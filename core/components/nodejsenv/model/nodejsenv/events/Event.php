<?php

namespace NodeJsEnv\Events;

abstract class Event
{

    /** @var \modX $modx */
    protected $modx;

    /** @var \NodeJsEnv $cmp */
    protected $cmp;

    /** @var array $scriptProperties */
    protected $scriptProperties;

    public function __construct($modx, &$scriptProperties)
    {
        $this->scriptProperties =& $scriptProperties;
        $this->modx = $modx;
        $this->cmp = $this->modx->nodejsenv;
    }

    abstract public function run();
}