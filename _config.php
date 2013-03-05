<?php
/**
 * Copyright 2010, 2011 pixeltricks GmbH
 *
 * This file is part of CustomHtmlForms.
 *
 * CustomHtmlForms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * CustomHtmlForms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with CustomHtmlForms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package CustomHtmlForm
 * @subpackage Config
 * @ignore
 */

Requirements::set_write_js_to_body(false); // write javascriptcode into the html head section

DataObject::add_extension('ContentController', 'CustomHtmlFormPage_Controller');
DataObject::add_extension('Security',          'CustomHtmlFormPage_Controller');

$cacheBaseDir   = getTempFolder() . DIRECTORY_SEPARATOR . 'cache';
$cacheDir       = $cacheBaseDir . DIRECTORY_SEPARATOR . 'CustomHtmlForm';
if (Director::isDev()) {
    $cachelifetime = 1;
} else {
    $cachelifetime = 86400;
}
if (!is_dir($cacheDir)) {
    if (!is_dir($cacheBaseDir)) {
        mkdir($cacheBaseDir);
    }
    mkdir($cacheDir);
}
SS_Cache::set_cache_lifetime('CustomHtmlForm', $cachelifetime);
SS_Cache::add_backend(
        'CustomHtmlForm',
        'File',
        array(
            'cache_dir'                 => $cacheDir,
            'hashed_directory_level'    => 3,
        )
);
SS_Cache::pick_backend('CustomHtmlForm', 'CustomHtmlForm');

if (class_exists('RequirementsEngine')) {
    RequirementsEngine::registerJsFile('customhtmlform/script/jquery.js');
    RequirementsEngine::registerJsFile('customhtmlform/script/jquery.scrollTo.min.js');
    RequirementsEngine::registerJsFile('customhtmlform/script/jquery.pixeltricks.forms.checkFormData.js');
    RequirementsEngine::registerJsFile('customhtmlform/script/jquery.pixeltricks.forms.events.js');
    RequirementsEngine::registerJsFile('customhtmlform/script/jquery.pixeltricks.forms.validator.js');
    RequirementsEngine::registerJsFile(SAPPHIRE_DIR . "/javascript/i18n.js");
}
if (class_exists('SilvercartPage')) {
    Object::set_static('CustomHtmlFormAdmin', 'menuCode',       'config');
    Object::set_static('CustomHtmlFormAdmin', 'menuSection',    'others');
    Object::set_static('CustomHtmlFormAdmin', 'menuSortIndex',  129);
}

Director::addRules(
        100,
        array(
            'customhtmlformaction/$Action/$ID/$OtherID' => 'CustomHtmlFormActionHandler',
        )
);
