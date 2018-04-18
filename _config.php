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

use CustomHtmlForm\Model\PageControllerExtension;
use SilverStripe\Control\Director;

if (class_exists('RequirementsEngine')) {
    if (PageControllerExtension::do_use_own_jquery()) {
        RequirementsEngine::registerJsFile('customhtmlform/script/jquery.js');
    }
    if (PageControllerExtension::load_jquery_dependencies()) {
        RequirementsEngine::registerJsFile('customhtmlform/script/jquery.scrollTo.min.js');
        RequirementsEngine::registerJsFile('customhtmlform/script/jquery.pixeltricks.forms.checkFormData.js');
        RequirementsEngine::registerJsFile('customhtmlform/script/jquery.pixeltricks.forms.events.js');
        RequirementsEngine::registerJsFile('customhtmlform/script/jquery.pixeltricks.forms.validator.js');
    }
    RequirementsEngine::registerJsFile(Director::baseURL() . "/silverstripe-admin/client/src/i18n.js");
}