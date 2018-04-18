<?php

namespace CustomHtmlForm\Model;

use SilverStripe\Forms\FieldList;
use SilverCart\Forms\FormFields\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * SiteConfig Extension for CustomHtmlForms.
 *
 * @package CustomHtmlForm
 * @subpackage Model
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 11.10.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class SiteConfigExtension extends DataExtension {

    /**
     * Attributes.
     *
     * @var array
     */
    private static $db = array(
        'SpamCheck_numberOfCharsInCaptcha' => 'Int',
        'SpamCheck_width'                  => 'Int',
        'SpamCheck_height'                 => 'Int',
        'SpamCheck_jpgQuality'             => 'Int',
    );

    /**
     * Defaults for DB Attributes.
     *
     * @var array
     */
    private static $defaults = array(
        'SpamCheck_numberOfCharsInCaptcha' => 8,
        'SpamCheck_width'                  => 160,
        'SpamCheck_height'                 => 50,
        'SpamCheck_jpgQuality'             => 90,
    );

    /**
     * SiteConfig object
     *
     * @var SiteConfig 
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

    /**
     * Updates the fields labels
     *
     * @param array &$labels Labels to update
     * 
     * @return void
     * 
     * @author Patrick Schneider <pschneider@pixeltricks.de>
     * @since 06.06.2013
     */
    public function updateFieldLabels(&$labels) {
        $labels = array_merge(
                $labels,
                array(
                'SpamCheck_numberOfCharsInCaptcha'  => _t(SiteConfigExtension::class . '.SpamCheck_numberOfCharsInCaptcha', 'Number of characters in the captcha'),
                'SpamCheck_width'                   => _t(SiteConfigExtension::class . '.SpamCheck_width', 'Width in pixels'),
                'SpamCheck_height'                  => _t(SiteConfigExtension::class . '.SpamCheck_height', 'Height in pixels'),
                'SpamCheck_jpgQuality'              => _t(SiteConfigExtension::class . '.SpamCheck_jpgQuality', 'JPG quality setting for the rendering of the captcha image (0 [worst] to 100 [best])'),
                'FormConfigurationTab'              => _t(SiteConfigExtension::class . '.FormConfigurationTab', 'Form configuration'),
            )
        );
    }
    
    /**
     * Adds a translation section
     *
     * @param FieldList $fields The FieldList
     * 
     * @return void
     *
     * @author Patrick Schneider <pschneider@pixeltricks.de>
     * @since 06.06.2013
     */
    public function updateCMSFields(FieldList $fields) {
        $fields->findOrMakeTab('Root.FormConfiguration')->setTitle($this->owner->fieldLabel('FormConfigurationTab'));
        
        $numberOfCharsInCaptchaField = new TextField('SpamCheck_numberOfCharsInCaptcha', $this->owner->fieldLabel('SpamCheck_numberOfCharsInCaptcha'));
        $spamCheckWidthField         = new TextField('SpamCheck_width',                  $this->owner->fieldLabel('SpamCheck_width'));
        $spamCheckHeightField        = new TextField('SpamCheck_height',                 $this->owner->fieldLabel('SpamCheck_height'));
        $spamCheckJpgQualityField    = new TextField('SpamCheck_jpgQuality',             $this->owner->fieldLabel('SpamCheck_jpgQuality'));

        $fields->addFieldToTab('Root.FormConfiguration', $numberOfCharsInCaptchaField);
        $fields->addFieldToTab('Root.FormConfiguration', $spamCheckWidthField);
        $fields->addFieldToTab('Root.FormConfiguration', $spamCheckHeightField);
        $fields->addFieldToTab('Root.FormConfiguration', $spamCheckJpgQualityField);
    }

    /**
     * Returns the CustomHtmlFormConfig or triggers an error if not existent.
     *
     * @return SiteConfig
     */
    public static function getConfig() {
        if (is_null(self::$config)) {
            self::$config = SiteConfig::current_site_config();
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

}