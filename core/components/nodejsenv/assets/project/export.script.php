<?php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
ini_set('html_errors', 1);
ini_set('log_errors', 1);
ini_set('ignore_repeated_errors', 0);
ini_set('ignore_repeated_source', 0);
ini_set('report_memleaks', 1);
ini_set('track_errors', 1);
ini_set('docref_root', 0);
ini_set('docref_ext', 0);
ini_set('error_reporting', -1);
ini_set('log_errors_max_len', 0);

$project_name=$argv[1];
$path=dirname(dirname(dirname(dirname(dirname(__DIR__)))));
$project_dir=$path.'/core/components/nodejsenv/projects/'.$project_name;
if(!is_dir($project_dir)){
	echo 'Проект не найден!';
	die();
}
$project_conf=$project_dir.'/config.inc.php';
if(!file_exists($project_conf)){
	echo 'Файл конфигурации не найден!';
	die();
}
include_once($project_conf);
if(!isset($config['export'])){
	echo 'Проект не сконфигурирован!';
	die();
}


$_SERVER['DOCUMENT_ROOT']=$path."/public_html";
require $path."/public_html/config.core.php";
if(!defined('MODX_CORE_PATH')) require_once '../../../config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

if(!$modx){
	echo 'Не удалось подключить ядро MODX!';
	die();
}

//Создать папку проекта
$projectCat=$modx->getObject('modCategory',['category'=>$project_name]);
if(!$projectCat){
	$projectCat=$modx->newObject('modCategory',['category'=>$project_name]);
	$projectCat->save();
}

$source_id=intval($config['export']['source'])?:$modx->getOption('default_media_source',null,1);
$modx->loadClass('sources.modMediaSource');
$source = modMediaSource::getDefaultSource($modx,$source_id);
$source_properties = $source->getProperties();




$rootFiles=[];
$iterator = new RegexIterator(
	new IteratorIterator(new DirectoryIterator($project_dir)),
	'/^.+\.js$|^.+\.json$/i', RegexIterator::GET_MATCH
);
$iterator->rewind();
while($iterator->valid()){
	$file=$iterator->getPathname();
	$filename=$iterator->getFilename();
	if(strpos($filename,'_')===0){
		$filename=substr($filename,1);
	}
	$rootFiles[$filename]=str_replace($source_properties['basePath']['value'],'',$file);
	$iterator->next();
}

foreach($rootFiles as $name=>$rootFile){
	$chunk=$modx->getObject('modChunk',['name'=>$name]);
	if(!$chunk){
		$chunk=$modx->newObject('modChunk',[
			'source'=>$source_id,
			'name'=>$name,
			'description'=>'',
			'category'=>$projectCat->id,
			'snippet'=>'',
			'static'=>1,
			'static_file'=>$rootFile,
		]);
		$chunk->save();
	}
}




$cssMainCat=$modx->getObject('modCategory',['category'=>'css','parent'=>$projectCat->id]);
if(!$cssMainCat){
	$cssMainCat=$modx->newObject('modCategory',['category'=>'css','parent'=>$projectCat->id]);
	$cssMainCat->save();
}

$cssFiles = [];
$cssCats = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($project_dir.$config['export']['css'],FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
$iterator->rewind();
while($iterator->valid()){
	if($iterator->isDir()){$iterator->next();continue;}
	$file=$iterator->getPathname();
	$f_dirs=array_values(array_diff(explode('/',str_replace($project_dir.$config['export']['css'],'',dirname($file))),array("")));
	$f_dir=&$cssFiles;
	$suffix='';
	foreach($f_dirs as $i=>$f_dirname){
		if(!isset($f_dir[$f_dirname]))$f_dir[$f_dirname]=array();
		$prevKey=implode('/',array_slice($f_dirs,$i-1));
		if($i-1<0)$prevKey=false;
		$curKey=implode('/',array_slice($f_dirs,$i));
		$cssCat=$modx->getObject('modCategory',['category'=>$f_dirname,'parent'=>$cssCats[$prevKey]?:$cssMainCat->id]);
		if(!$cssCat){
			$cssCat=$modx->newObject('modCategory',['category'=>$f_dirname,'parent'=>$cssCats[$prevKey]?:$cssMainCat->id]);
			$cssCat->save();
		}
		$cssCats[$curKey]=$cssCat->id;
		$suffix.=substr($f_dirname,0,1);
		$f_dir=&$f_dir[$f_dirname];
	}
	$filename=pathinfo($file,PATHINFO_FILENAME).($suffix?'.':'').$suffix.'.'.$iterator->getExtension();
	$f_dir[$filename]=str_replace($source_properties['basePath']['value'],'',$file);
	$iterator->next();
}

$iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($cssFiles));
foreach($iterator as $key=>$value){
	if(is_array($value))continue;
	$keys = array();
	for ($i = $iterator->getDepth(); $i>0; $i--) {
		$keys[] = $iterator->getSubIterator($i-1)->key();
	}
	$cssCat=$cssCats[implode('/',$keys)]?:$cssMainCat->id;
	$chunk=$modx->getObject('modChunk',['name'=>$key]);
	if(!$chunk){
		$chunk=$modx->newObject('modChunk',[
			'source'=>$source_id,
			'name'=>$key,
			'description'=>'',
			'category'=>(int)$cssCat,
			'snippet'=>'',
			'static'=>1,
			'static_file'=>$value,
		]);
		$chunk->save();
	}
}




$jsMainCat=$modx->getObject('modCategory',['category'=>'js','parent'=>$projectCat->id]);
if(!$jsMainCat){
	$jsMainCat=$modx->newObject('modCategory',['category'=>'js','parent'=>$projectCat->id]);
	$jsMainCat->save();
}

$jsFiles = [];
$jsCats = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($project_dir.$config['export']['js'],FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
$iterator->rewind();
while($iterator->valid()){
	if($iterator->isDir()){$iterator->next();continue;}
	$file=$iterator->getPathname();
	$f_dirs=array_values(array_diff(explode('/',str_replace($project_dir.$config['export']['js'],'',dirname($file))),array("")));
	$f_dir=&$jsFiles;
	$suffix='';
	foreach($f_dirs as $i=>$f_dirname){
		if(!isset($f_dir[$f_dirname]))$f_dir[$f_dirname]=array();
		$prevKey=implode('/',array_slice($f_dirs,$i-1));
		if($i-1<0)$prevKey=false;
		$curKey=implode('/',array_slice($f_dirs,$i));
		$jsCat=$modx->getObject('modCategory',['category'=>$f_dirname,'parent'=>$jsCats[$prevKey]?:$jsMainCat->id]);
		if(!$jsCat){
			$jsCat=$modx->newObject('modCategory',['category'=>$f_dirname,'parent'=>$jsCats[$prevKey]?:$jsMainCat->id]);
			$jsCat->save();
		}
		$jsCats[$curKey]=$jsCat->id;
		$suffix.=substr($f_dirname,0,1);
		$f_dir=&$f_dir[$f_dirname];
	}
	$filename=pathinfo($file,PATHINFO_FILENAME).($suffix?'.':'').$suffix.'.'.$iterator->getExtension();
	$f_dir[$filename]=str_replace($source_properties['basePath']['value'],'',$file);
	$iterator->next();
}

$iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($jsFiles));
foreach($iterator as $key=>$value){
	if(is_array($value))continue;
	$keys = array();
	for ($i = $iterator->getDepth(); $i>0; $i--) {
		$keys[] = $iterator->getSubIterator($i-1)->key();
	}
	$jsCat=$jsCats[implode('/',$keys)]?:$jsMainCat->id;
	$chunk=$modx->getObject('modChunk',['name'=>$key]);
	if(!$chunk){
		$chunk=$modx->newObject('modChunk',[
			'source'=>$source_id,
			'name'=>$key,
			'description'=>'',
			'category'=>(int)$jsCat,
			'snippet'=>'',
			'static'=>1,
			'static_file'=>$value,
		]);
		$chunk->save();
	}
}




$tplMainCat=$modx->getObject('modCategory',['category'=>'templates','parent'=>$projectCat->id]);
if(!$tplMainCat){
	$tplMainCat=$modx->newObject('modCategory',['category'=>'templates','parent'=>$projectCat->id]);
	$tplMainCat->save();
}

$tplFiles = [];
$tplCats = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($project_dir.$config['export']['templates'],FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
$iterator->rewind();
while($iterator->valid()){
	if($iterator->isDir()){$iterator->next();continue;}
	$file=$iterator->getPathname();
	$f_dirs=array_values(array_diff(explode('/',str_replace($project_dir.$config['export']['templates'],'',dirname($file))),array("")));
	$f_dir=&$tplFiles;
	$suffix='';
	foreach($f_dirs as $i=>$f_dirname){
		if(!isset($f_dir[$f_dirname]))$f_dir[$f_dirname]=array();
		$prevKey=implode('/',array_slice($f_dirs,$i-1));
		if($i-1<0)$prevKey=false;
		$curKey=implode('/',array_slice($f_dirs,$i));
		$tplCat=$modx->getObject('modCategory',['category'=>$f_dirname,'parent'=>$tplCats[$prevKey]?:$tplMainCat->id]);
		if(!$tplCat){
			$tplCat=$modx->newObject('modCategory',['category'=>$f_dirname,'parent'=>$tplCats[$prevKey]?:$tplMainCat->id]);
			$tplCat->save();
		}
		$tplCats[$curKey]=$tplCat->id;
		$suffix.=substr($f_dirname,0,1);
		$f_dir=&$f_dir[$f_dirname];
	}
	$filename=pathinfo($file,PATHINFO_FILENAME).($suffix?'.':'').$suffix.'.'.$iterator->getExtension();
	$f_dir[$filename]=str_replace($source_properties['basePath']['value'],'',$file);
	$iterator->next();
}

$iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($tplFiles));
foreach($iterator as $key=>$value){
	if(is_array($value))continue;
	$keys = array();
	for ($i = $iterator->getDepth(); $i>0; $i--) {
		$keys[] = $iterator->getSubIterator($i-1)->key();
	}
	$tplCat=$tplCats[implode('/',$keys)]?:$tplMainCat->id;
	$chunk=$modx->getObject('modChunk',['name'=>$key]);
	if(!$chunk){
		$chunk=$modx->newObject('modChunk',[
			'source'=>$source_id,
			'name'=>$key,
			'description'=>'',
			'category'=>(int)$tplCat,
			'snippet'=>'',
			'static'=>1,
			'static_file'=>$value,
		]);
		$chunk->save();
	}
}



$compiled_source=1;
$compiled_dir='/'.$config['dest']['root'].$config['dest']['pages'];
$iterator = new IteratorIterator(new DirectoryIterator($project_dir.$config['export']['pages']));
$iterator->rewind();
while($iterator->valid()){
	if($iterator->isDir()){$iterator->next();continue;}
	$file=pathinfo($iterator->getPathname(),PATHINFO_FILENAME).'.'.$config['dest']['page_ext'];
	$compiled_file=$compiled_dir.'/'.$file;
	$tpl=$modx->getObject('modTemplate',['templatename'=>$file]);
	if(!$tpl){
		$tpl=$modx->newObject('modTemplate',[
			'source'=>$compiled_source,
			'templatename'=>$file,
			'description'=>'',
			'category'=>(int)$projectCat->id,
			'content'=>'',
			'static'=>1,
			'static_file'=>$compiled_file,
		]);
		$tpl->setProperties(array('nodejsenv.project'=>$project_name),true);
		$tpl->save();
	}
	
	$iterator->next();
}