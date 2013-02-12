<?php
/**
 * Copyright 2012 pixeltricks GmbH
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
 */

/**
 * Configuration for CustomHtmlForms.
 *
 * @package CustomHtmlForm
 * @subpackage Config
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @copyright 2012 pxieltricks GmbH
 * @since 2012-12-10
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class CustomHtmlFormConfiguration extends DataObject {

    /**
     * Attributes.
     *
     * @var array
     *
     * @since 2012-12-10
     */
    public static $db = array(
        'SpamCheck_numberOfCharsInCaptcha' => 'Int',
        'SpamCheck_width'                  => 'Int',
        'SpamCheck_height'                 => 'Int',
        'SpamCheck_jpgQuality'             => 'Int',
    );

    /**
     * CustomHtmlFormConfiguration object
     *
     * @var CustomHtmlFormConfiguration 
     */
    public static $config                           = null;
    
    /**
     * Number of chars in captcha
     *
     * @var int 
     */
    public static $SpamCheck_numberOfCharsInCaptcha = null;
    
    /**
     * Captcha width
     *
     * @var int
     */
    public static $SpamCheck_width                  = null;
    
    /**
     * Captcha height
     *
     * @var int
     */
    public static $SpamCheck_height                 = null;
    
    /**
     * Captcha quality
     *
     * @var int
     */
    public static $SpamCheck_jpgQuality             = null;

    /**
     * Returns the translated singular name of the object. If no translation exists
     * the class name will be returned.
     *
     * @return string The objects singular name
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2012-12-10
     */
    public function singular_name() {
        return SilvercartTools::singular_name_for($this);
    }


    /**
     * Returns the translated plural name of the object. If no translation exists
     * the class name will be returned.
     *
     * @return string the objects plural name
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2012-12-10
     */
    public function plural_name() {
        return SilvercartTools::plural_name_for($this);
    }

    /**
     * Returns the number of chars that should be displayed in the SpamCheck captcha.
     *
     * @return int
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2012-12-10
     */
    public static function SpamCheck_numberOfCharsInCaptcha() {
        if (is_null(self::$SpamCheck_numberOfCharsInCaptcha)) {
            self::$SpamCheck_numberOfCharsInCaptcha = self::getConfig()->SpamCheck_numberOfCharsInCaptcha;
            if (empty(self::$SpamCheck_numberOfCharsInCaptcha)) {
                self::$SpamCheck_numberOfCharsInCaptcha = 8;
            }
        }
        return self::$SpamCheck_numberOfCharsInCaptcha;
    }

    /**
     * Returns the width for the captcha image.
     *
     * @return int
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2012-12-10
     */
    public static function SpamCheck_width() {
        if (is_null(self::$SpamCheck_width)) {
            self::$SpamCheck_width = self::getConfig()->SpamCheck_width;
            if (empty(self::$SpamCheck_width)) {
                self::$SpamCheck_width = 160;
            }
        }
        return self::$SpamCheck_width;
    }

    /**
     * Returns the height for the captcha image.
     *
     * @return int
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2012-12-10
     */
    public static function SpamCheck_height() {
        if (is_null(self::$SpamCheck_height)) {
            self::$SpamCheck_height = self::getConfig()->SpamCheck_height;
            if (empty(self::$SpamCheck_height)) {
                self::$SpamCheck_height = 50;
            }
        }
        return self::$SpamCheck_height;
    }

    /**
     * Returns the JPG quality setting for the rendering of the captcha image.
     *
     * @return int
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2012-12-10
     */
    public static function SpamCheck_jpgQuality() {
        if (is_null(self::$SpamCheck_jpgQuality)) {
            self::$SpamCheck_jpgQuality = self::getConfig()->SpamCheck_jpgQuality;
            if (empty(self::$SpamCheck_jpgQuality)) {
                self::$SpamCheck_jpgQuality = 90;
            }
        }
        return self::$SpamCheck_jpgQuality;
    }

    // -----------------------------------------------------------------------

    /**
     * Returns the field labels.
     *
     * @param boolean $includerelations a boolean value to indicate if the labels returned include relation fields
     *
     * @return array|string Array of all element labels if no argument given, otherwise the label of the field
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2012-12-10
     */
    public function fieldLabels($includerelations = true) {
        return array_merge(
            parent::fieldLabels($includerelations),
            array(
                'SpamCheck_numberOfCharsInCaptcha'  => _t('CustomHtmlFormConfiguration.SpamCheck_numberOfCharsInCaptcha'),
                'SpamCheck_width'                   => _t('CustomHtmlFormConfiguration.SpamCheck_width'),
                'SpamCheck_height'                  => _t('CustomHtmlFormConfiguration.SpamCheck_height'),
                'SpamCheck_jpgQuality'              => _t('CustomHtmlFormConfiguration.SpamCheck_jpgQuality'),
            )
        );
    }

    /**
     * Create CMS fields
     *
     * @return FieldList
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2012-12-10
     */
    public function getCMSFields() {
        $parentFields = parent::getCMSFields();
        $CMSFields    = new FieldList(
            $rootTab  = new TabSet(
                'Root',
                $generalTab = new TabSet(
                    'General',
                    _t('SilvercartConfig.GENERAL'),
                    $tabGeneralSpamCheck = new Tab('SpamCheck', _t('CustomHtmlFormConfigurationAdmin.TAB_SPAMCHECK'))
                )
            )
        );

        $CMSFields->addFieldToTab(
            'Root.General.SpamCheck',
            $parentFields->dataFieldByName('SpamCheck_numberOfCharsInCaptcha')
        );
        $CMSFields->addFieldToTab(
            'Root.General.SpamCheck',
            $parentFields->dataFieldByName('SpamCheck_width')
        );
        $CMSFields->addFieldToTab(
            'Root.General.SpamCheck',
            $parentFields->dataFieldByName('SpamCheck_height')
        );
        $CMSFields->addFieldToTab(
            'Root.General.SpamCheck',
            $parentFields->dataFieldByName('SpamCheck_jpgQuality')
        );

        return $CMSFields;
    }

    // -----------------------------------------------------------------------

    /**
     * Returns the CustomHtmlFormConfig or triggers an error if not existent.
     *
     * @return SilvercartConfig
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2012-12-10
     */
    public static function getConfig() {
        if (is_null(self::$config)) {
            self::$config = DataObject::get_one('CustomHtmlFormConfiguration');

            if (!self::$config) {
                if (SilvercartTools::isIsolatedEnvironment()) {
                    return false;
                }
                $errorMessage = _t('CustomHtmlFormConfiguration.ERROR_NO_CONFIG');
                self::triggerError($errorMessage);
            }
        }
        return self::$config;
    }

    /**
     * Displays an error and stops further program execution.
     *
     * @param string $errorMessage the error message to display
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2012-12-10
     */
    public static function triggerError($errorMessage) {
        trigger_error($errorMessage);
        exit();
    }

    /**
     * Creates a config if none exists.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2012-12-10
     */
    public function requireDefaultRecords() {
        if (!CustomHtmlFormConfiguration::get()->exists()) {
            $config = new CustomHtmlFormConfiguration();
            $config->SpamCheck_numberOfCharsInCaptcha   = 8;
            $config->SpamCheck_width                    = 160;
            $config->SpamCheck_height                   = 50;
            $config->SpamCheck_jpgQuality               = 90;
            $config->write();
        }
    }

    /**
     * Checks whether there is an existing SilvercartConfig or not before writing.
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.02.2011
     */
    public function onBeforeWrite() {
        parent::onBeforeWrite();
        if (CustomHtmlFormConfiguration::get()->exists()) {
            if (DataObject::get_one('CustomHtmlFormConfiguration')->ID !== $this->ID) {
                $this->record = array();
            }
        }
    }
}