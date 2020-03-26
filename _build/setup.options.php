<?php
/**
 * Build the setup options form.
 *
 * @subpackage build
 */
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
        $output = '
        <div class="panel-desc">
            <p>Создать проект после установки?</p>
        </div>
        <label for="sample-option">Название проекта:</label>
        <input type="text" name="sampleOption" id="sample-option" width="300" value="" />
        <label for="sample-option">Ссылка для клонирования с github:</label>
        <input type="text" name="sampleOption" id="sample-option" width="300" value="" />
        ';
        break;
    case xPDOTransport::ACTION_UPGRADE:
    case xPDOTransport::ACTION_UNINSTALL: break;
}


return $output;