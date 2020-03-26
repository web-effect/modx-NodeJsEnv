<?php
/**
 * MODX CMP package build script
 *
 */
/*ini_set('display_startup_errors', 1);
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
ini_set('log_errors_max_len', 0);*/

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0); /* makes sure our script doesnt timeout */

require_once __DIR__.'/build.config.php';

require_once dirname(dirname(dirname(__DIR__))).'/builder.class.php';
$modx= new PackageBuilder();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');




class packageAutoBuilder{
    public $modx=null;
    public $config=[];
    public $builder=null;
    public $data=[];
    public $objects=[];
    public $vehicles=[];
    
    public function __construct(&$modx,$config){
        $this->modx=$modx;
        $this->config=$config;
        
        $this->modx->loadClass('transport.modPackageBuilder','',false, true);
    }
    
    public function build(){
        $this->builder = new modPackageBuilder($this->modx);
        $this->builder->createPackage($this->config['component']['namespace'],$this->config['component']['version'],$this->config['component']['release']);
        $this->builder->registerNamespace(
            $this->config['component']['namespace'],false,true,
            '{core_path}components/'.$this->config['component']['namespace'].'/',
            '{assets_path}components/'.$this->config['component']['namespace'].'/'
        );
        
        $this->data=$this->collectObjectsData();
        $keys=$this->createObjects($this->data);
        $this->addVehicles($this->data,$keys);
        $this->setAttributes($this->config['component']['attributes']);
        
        $this->builder->pack();
    }
    
    public function setAttributes($attributes){
        $this->builder->setPackageAttributes($attributes);
    }
    
    public function addVehicles($data,$keys){
        foreach($keys as $i=>$key){
            $this->vehicles[] = $this->builder->createVehicle($this->objects[$key]['object'],$this->objects[$key]['attrs']);
            $vehicle=&$this->vehicles[count($this->vehicles)-1];
            list($class,$name)=explode('@',$key);
            $resolvers=array_values($data[$class][$name]['resolvers']?:[]);
            if($i==0)$resolvers=array_merge(array_values($this->config['component']['resolvers']?:[]),$resolvers);
            foreach($resolvers as $resolver){
                $vehicle->resolve($resolver['type'],$resolver['options']);
            }
            $this->builder->putVehicle($vehicle);
        }
    }

    public function collectObjectsData(){
        //Собираем карту объектов из data
        $data=[];
        $config=$this->config;
        $modx=$this->modx;
        $iterator = new IteratorIterator(new DirectoryIterator($this->config['data']));
        $iterator->rewind();
        while($iterator->valid()){
        	if($iterator->isDir()){$iterator->next();continue;}
        	$vars = array_keys(get_defined_vars());
        	include_once($iterator->getPathname());
        	$vars = array_diff(array_keys(get_defined_vars()),$vars);
            foreach($vars as $varkey){
                unset(${$varkey});
            }
        	$iterator->next();
        }
        return $data;
    }
    public function createObjects($data){
        //Создаём объекты
        $keys=[];
        foreach($data as $class=>$objects){
            foreach($objects as $oname=>$properties){
                $k=$this->addObject($class,$oname,$data);
                if($k&&!$properties['relations'])$keys[]=$k;
            }
        }
        /*echo '======================================================';
        foreach($vehicles_objects as $key){
            var_dump($this->objects[$key]['object']->toArray('',false,false,true));
            var_dump($this->objects[$key]['attrs']);
        }*/
        return $keys;
    }
    
    public function addObject($class,$oname,&$data){
        $k=$class.'@'.$oname;
        if(isset($this->objects[$k]))return $k;
        
        $properties=$data[$class][$oname];
        if(empty($properties['options']['search_by'])){
            $this->modx->log(MODX_LOG_LEVEL_ERROR,'You must declare "search_by" option for '.$k);
            return false;
        }
        
        $storage=&$this->objects[$k];
        $storage['object']=$this->modx->newObject($class);
        
        $pk=$this->modx->getPK($class);
        if(!is_array($pk))$pk=[$pk];
        if(in_array('id',$pk))$properties['fields']['id']=0;
        
        $storage['object']->fromArray($properties['fields'],'',true,true);
        $storage['attrs']=array(
            xPDOTransport::UNIQUE_KEY => $properties['options']['search_by'],
            xPDOTransport::UPDATE_OBJECT => $properties['options']['update']??true,
            xPDOTransport::PRESERVE_KEYS => $properties['options']['preserve']??false,
        );
        if($properties['relations']){
            foreach($properties['relations'] as $rclass=>$relations){
                $this->modx->loadClass($rclass);
                $rmap=$this->modx->map[$rclass];
                foreach($relations as $rname=>$alias){
                    $rk=$this->addObject($rclass,$rname,$data);
                    if(!$rk)continue;
                    $rstorage=&$this->objects[$rk];
                    $cardinality=$rmap['composites'][$alias]['cardinality']?:$rmap['aggregates'][$alias]['cardinality'];
                    if($cardinality){
                        $rstorage['object']->{'add'.strtoupper($cardinality)}($storage['object'],$alias);
                        $rstorage['attrs'][xPDOTransport::RELATED_OBJECTS]=true;
                        $rstorage['attrs'][xPDOTransport::RELATED_OBJECT_ATTRIBUTES][$alias]=$storage['attrs'];
                        $storage['attrs']=&$rstorage['attrs'][xPDOTransport::RELATED_OBJECT_ATTRIBUTES][$alias];
                    }
                }
            }
        }
        
        return $k;
    }
}

$builder=new packageAutoBuilder($modx,$config);
$builder->build();

$mtime= microtime();
$mtime= explode(" ", $mtime);
$mtime= $mtime[1] + $mtime[0];
$tend= $mtime;
$totalTime= ($tend - $tstart);
$totalTime= sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO,"\nPackage Built.\nExecution time: {$totalTime}\n");

session_write_close();
exit();