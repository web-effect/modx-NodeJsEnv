<?php
$corePath = $modx->getOption('nodejsenv.core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/nodejsenv/');
$nodejsenv = $modx->getService(
    'nodejsenv',
    'NodeJsEnv',
    $corePath . 'model/nodejsenv/',
    array(
        'core_path' => $corePath
    )
);

if (!($nodejsenv instanceof NodeJsEnv)) return '';

$className = "\\NodeJsEnv\\Events\\{$modx->event->name}";
if (class_exists($className)) {
    /** @var \Collections\Events\Event $handler */
    $handler = new $className($modx, $scriptProperties);
    $handler->run();
}

return;