<?php

namespace NodeJsEnv\Events;

class OnChunkFormPrerender extends Event
{

    public function __construct($modx, &$scriptProperties)
    {
        parent::__construct($modx, $scriptProperties);

        
    }

    public function run()
    {
        //mode,chunk,id
        if($this->scriptProperties['mode']!='upd')return true;
        if($this->scriptProperties['chunk']->static!=1)return true;
        
        $path=$this->scriptProperties['chunk']->getSourceFile();
        $paths=pathinfo($path);
        
        if(strpos($paths['dirname'],$this->cmp->config['projectsPath'])!==0)return true;
        $projectName=current(explode('/',str_replace($this->cmp->config['projectsPath'],'',$paths['dirname'])));
        $projectPath=$this->cmp->config['projectsPath'].$projectName.'/';
        include($projectPath.'config.inc.php');
        
        $config['name']=$projectName;
        $config['is_git']=is_dir($projectPath.'.git/');
        $this->cmp->loadAssets('mgr');
        $this->modx->controller->addJavascript($this->cmp->config['assetsUrl'].'mgr/js/widgets/chunk.js');
        $this->modx->controller->addHtml('<script>NodeJSEnv.project='.json_encode($config).';</script>');
        
        return true;
    }

}
