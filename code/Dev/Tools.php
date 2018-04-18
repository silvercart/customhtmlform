<?php

namespace CustomHtmlForm\Dev;

use CustomHtmlForm\Forms\PtCaptchaImageField;
use CustomHtmlForm\Forms\PtCaptchaInputField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\GroupedDropdownField;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\SelectionGroup;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Forms\TreeDropdownField_Readonly;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\Session;

/**
 * Provides helper methods for CustomHtmlForms.
 *
 * @package CustomHtmlForm
 * @subpackage Dev
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 11.10.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class Tools {

    /**
     * The base url segment
     *
     * @var string
     */
    public static $baseURLSegment = null;

    /**
     * Returns whether the given type is a dropdown field.
     *
     * @param string $type The type to check
     *
     * @return boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 21.11.2012
     */
    public static function isDropdownField($type) {
        $isField = false;

        if ($type == DropdownField::class ||
            $type == GroupedDropdownField::class ||
            $type == TreeDropdownField::class ||
            $type == TreeDropdownField_Readonly::class ||
            $type == OptionsetField::class ||
            in_array(OptionsetField::class, class_parents($type)) ||
            in_array(DropdownField::class, class_parents($type))) {

            $isField = true;
        }

        return $isField;
    }

    /**
     * Returns whether the given type is a listbox field.
     *
     * @param string $type The type to check
     *
     * @return boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 21.11.2012
     */
    public static function isListboxField($type) {
        $isField = false;

        if ($type == ListboxField::class ||
            in_array(ListboxField::class, class_parents($type))) {

            $isField = true;
        }

        return $isField;
    }

    /**
     * Returns whether the given type is an optionset field.
     *
     * @param string $type The type to check
     *
     * @return boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 21.11.2012
     */
    public static function isOptionsetField($type) {
        $isField = false;

        if ($type == OptionsetField::class ||
            in_array(OptionsetField::class, class_parents($type))) {

            $isField = true;
       }

        return $isField;
    }

    /**
     * Returns whether the given type is a selection group field.
     *
     * @param string $type The type to check
     *
     * @return boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 21.11.2012
     */
    public static function isSelectiongroupField($type) {
        $isField = false;

        if ($type == SelectionGroup::class ||
            in_array(SelectionGroup::class, class_parents($type))) {

            $isField = true;
        }

        return $isField;
    }

    /**
     * Returns whether the given type is a text field.
     *
     * @param string $type The type to check
     *
     * @return boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 21.11.2012
     */
    public static function isTextField($type) {
        $isField = false;

        if ($type != PtCaptchaImageField::class &&
            ($type == TextField::class ||
            $type == SilverCart\Forms\FormFields\TextField::class ||
            $type == EmailField::class ||
            $type == PtCaptchaInputField::class ||
            in_array(TextField::class, class_parents($type)))) {

            $isField = true;
        }

        return $isField;
    }

    /**
     * Returns whether the given type is a textarea field.
     *
     * @param string $type The type to check
     *
     * @return boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2013-01-03
     */
    public static function isTextareaField($type) {
        $isField = false;

        if ($type == TextareaField::class ||
            in_array(TextareaField::class, class_parents($type))) {

            $isField = true;
        }

        return $isField;
    }

    /**
     * Returns the base URL segment that's used for inclusion of css and
     * javascript files via Requirements.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 16.02.2012
     */
    public static function getBaseURLSegment() {
        if (is_null(self::$baseURLSegment)) {
            $baseUrl = Director::baseURL();
            
            if ($baseUrl === '/') {
                $baseUrl = '';
            }

            if (!empty($baseUrl) &&
                substr($baseUrl, -1) != '/') {

                $baseUrl .= '/';
            }
            self::$baseURLSegment = $baseUrl;
        }
        return self::$baseURLSegment;
    }
    
    /**
     * Returns the current Session.
     * 
     * @return Session
     */
    public static function Session() {
        return Controller::curr()->getRequest()->getSession();
    }
    
    /**
     * Returns the current Session.
     * 
     * @return Session
     */
    public static function saveSession() {
        return self::Session()->save(Controller::curr()->getRequest());
    }
}