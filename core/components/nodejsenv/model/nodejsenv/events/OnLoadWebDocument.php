<?php

namespace NodeJsEnv\Events;

class OnLoadWebDocument extends Event
{

    public function __construct($modx, &$scriptProperties)
    {
        parent::__construct($modx, $scriptProperties);

        
    }

    public function run()
    {
        if(!$this->modx->resource)return true;
        $tpl=$this->modx->resource->getOne('Template');
        if($tpl->static!=1)return true;
        $props=$tpl->getProperties();
        if($props['nodejsenv.project']){
            include($this->cmp->config['projectsPath'].$props['nodejsenv.project'].'/config.inc.php');
            $this->modx->placeholders['nodejsenv.config']=$config;
        }
        return true;
    }

}
