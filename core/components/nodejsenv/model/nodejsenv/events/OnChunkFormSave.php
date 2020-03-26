<?php

namespace NodeJsEnv\Events;

class OnChunkFormSave extends Event
{

    public function __construct($modx, &$scriptProperties)
    {
        parent::__construct($modx, $scriptProperties);
        $this->register = $this->modx->registry->getRegister('mgr', 'registry.modFileRegister');
        if ($this->register&&$this->register->connect()) {
            $this->register->clear('/nodejsenv/');
            $this->register->subscribe('/nodejsenv/');
            $this->register->setCurrentTopic('/nodejsenv/');
        }
        
    }

    public function run()
    {
        //mode,chunk,id
        if($this->scriptProperties['mode']!='upd')return true;
        if($this->scriptProperties['chunk']->static!=1)return true;
        
        $source_id=$this->scriptProperties['chunk']->source?:$this->modx->getOption('default_media_source',null,1);
        $this->modx->loadClass('sources.modMediaSource');
        $source = \modMediaSource::getDefaultSource($this->modx,$source_id);
        $source_properties = $source->getProperties();
        
        $path=$source_properties['basePath']['value'].$this->scriptProperties['chunk']->static_file;
        $paths=pathinfo($path);
        
        if(strpos($paths['dirname'],$this->cmp->config['projectsPath'])!==0)return true;
        $projectName=current(explode('/',str_replace($this->cmp->config['projectsPath'],'',$paths['dirname'])));
        $projectPath=$this->cmp->config['projectsPath'].$projectName.'/';
        include($projectPath.'config.inc.php');
        
        if(strpos($paths['filename'],'_')===0){
            $generating_file=$paths['dirname'].'/'.substr($paths['basename'],1);
            $placeholders=[];
            $placeholders['config']=$config;
            $tpl=file_get_contents($path);
            $this->modx->getParser();
            if($this->modx->parser instanceof \pdoParser)$output = $this->modx->parser->pdoTools->getChunk('@INLINE '.$tpl, $placeholders);
            else $output = $this->scriptProperties['chunk']->process($placeholders,$tpl);
            $maxIterations = (integer) $this->modx->getOption('parser_max_iterations', null, 10);
            $this->modx->getParser()->processElementTags('', $output, false, false, '[[', ']]', array(), $maxIterations);
            $this->modx->getParser()->processElementTags('', $output, true, true, '[[', ']]', array(), $maxIterations);
            
            file_put_contents($generating_file,$output);
        }
        
        $is_git=false;
        if(is_dir($projectPath.'.git/'))$is_git=true;
        
        $runshell=false;
        $shellparams=[$projectName,'0','0','0','0'];
        if($_REQUEST['nodejsenv_build']){
            $runshell=true;
            $shellparams[1]='1';
        }
        if($is_git&&$_REQUEST['nodejsenv_commit']&&$_REQUEST['nodejsenv_commit_message']){
            $runshell=true;
            $shellparams[2]='1';
            $shellparams[3]=escapeshellarg($_REQUEST['nodejsenv_commit_message']);
            if($_REQUEST['nodejsenv_push']){
                $shellparams[4]='1';
            }
        }
        
        if($runshell){
            $command=$this->cmp->config['corePath'].'assets/project/update.script.sh '.implode(' ',$shellparams);
            $result=shell_exec($command);
            $this->setRegistryLog(\modX::LOG_LEVEL_INFO,$result);
        }
        return true;
    }
    public function setRegistryLog($level,$message){
        if (!$this->register||!$this->register->connect())return false;
        $timestamp = strftime('%Y-%m-%d %H:%M:%S');
        $messageKey = (string) time();
        $messageKey .= '-' . sprintf("%06d", 0);
        $message = array(
            'timestamp' => $timestamp,
            'level' => $this->_getLogLevel($level),
            'msg' => nl2br($message),
            'def' => '','file' => '','line' => ''
        );
        $options = array();
        if ($level === \xPDO::LOG_LEVEL_FATAL) {
            $options['kill'] = true;
        }
        //$this->modx->log(1,print_r($message,1));
        $this->register->send('', array($messageKey => $message), $options);
    }
    public function _getLogLevel($level) {
        switch ($level) {
            case \xPDO::LOG_LEVEL_DEBUG :
                $levelText= 'DEBUG';
                break;
            case \xPDO::LOG_LEVEL_INFO :
                $levelText= 'INFO';
                break;
            case \xPDO::LOG_LEVEL_WARN :
                $levelText= 'WARN';
                break;
            case \xPDO::LOG_LEVEL_ERROR :
                $levelText= 'ERROR';
                break;
            default :
                $levelText= 'FATAL';
        }
        return $levelText;
    }
}
