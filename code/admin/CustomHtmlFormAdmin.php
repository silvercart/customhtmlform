<?php
/**
 * Copyright 2013 pixeltricks GmbH
 *
 * This file is part of CustomHtmlForm.
 *
 * CustomHtmlForm is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * CustomHtmlForm is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with CustomHtmlForm.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package CustomHtmlForm
 * @subpackage Admin
 */

/**
 * ModelAdmin for CustomHtmlForm configuration.
 *
 * @package CustomHtmlForm
 * @subpackage Admin
 * @author Sebastian Diel <sdiel@pixeltricks.de>, Sascha Koehler <skoehler@pixeltricks.de>
 * @since 18.02.2013
 * @copyright 2013 pixeltricks GmbH
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class CustomHtmlFormAdmin extends ModelAdmin {

    /**
     * The URL segment
     *
     * @var string
     */
    public static $url_segment = 'customhtmlform';

    /**
     * The menu title
     *
     * @var string
     */
    public static $menu_title = 'Forms';

    /**
     * Managed models
     *
     * @var array
     */
    public static $managed_models = array(
        'CustomHtmlFormConfiguration' => array(
            'collection_controller' => 'CustomHtmlFormAdmin_CollectionController'
        )
    );

    /**
     * Constructor
     *
     * @return CustomHtmlFormAdmin
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2012-12-10
     */
    public function __construct() {
        self::$menu_title = _t('CustomHtmlFormConfiguration.PLURALNAME');

        parent::__construct();
    }

    /**
     * Provides hook for decorators, so that they can overwrite css
     * and other definitions.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2012-12-10
     */
    public function init() {
        parent::init();
        $this->extend('updateInit');
    }
}

/**
 * ModelAdmin for CustomHtmlForm configuration.
 *
 * @package CustomHtmlForm
 * @subpackage Admin
 * @author Sebastian Diel <sdiel@pixeltricks.de>, Sascha Koehler <skoehler@pixeltricks.de>
 * @since 18.02.2013
 * @copyright 2013 pixeltricks GmbH
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class CustomHtmlFormAdmin_CollectionController extends ModelAdmin_CollectionController {

    /**
     * Hide the import form
     *
     * @var boolean
     */
    public $showImportForm = false;
}