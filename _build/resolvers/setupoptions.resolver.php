<?php
/**
 * Resolves setup-options settings
 *
 * @package quip
 * @subpackage build
 */
$success= false;
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
        //Тут нужно создать проект, если указаны опции
        $success= true;
        break;
    case xPDOTransport::ACTION_UPGRADE:
        $success= true;
        break;
    case xPDOTransport::ACTION_UNINSTALL:
        $success= true;
        break;
}
return $success;