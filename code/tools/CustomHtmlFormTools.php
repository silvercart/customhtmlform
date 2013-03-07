<?php
/**
 * Copyright 2013 pixeltricks GmbH
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
 * Provides helper methods for CustomHtmlForms
 *
 * @package CustomHtmlForm
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @copyright 2013 pixeltricks GmbH
 * @since 2013-02-14
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class CustomHtmlFormTools {

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

        if ($type == 'DropdownField' ||
            $type == 'GroupedDropdownField' ||
            $type == 'HTMLDropdownField' ||
            $type == 'CountryDropdownField' ||
            $type == 'LanguageDropdownField' ||
            $type == 'SimpleTreeDropdownField' ||
            $type == 'TreeDropdownField' ||
            $type == 'TreeDropdownField_Readonly' ||
            $type == 'StateProvinceDropdownField_Readonly' ||
            $type == 'Widget_TreeDropdownField_Readonly' ||
            $type == 'StateDropdownField' ||
            $type == 'SilvercartCheckoutOptionsetField' ||
            $type == 'SilvercartShippingOptionsetField' ||
            $type == 'OptionsetField' ||
            in_array('OptionsetField', class_parents($type)) ||
            in_array('DropdownField', class_parents($type))) {

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

        if ($type == 'ListboxField' ||
            in_array('ListboxField', class_parents($type))) {

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

        if ($type == 'OptionsetField' ||
            $type == 'SilvercartCheckoutOptionsetField' ||
            $type == 'SilvercartShippingOptionsetField' ||
            in_array('OptionsetField', class_parents($type))) {

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

        if ($type == 'SelectionGroup' ||
            in_array('SelectionGroup', class_parents($type))) {

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

        if ($type != 'PtCaptchaImageField' &&
            ($type == 'TextField' ||
            $type == 'SilvercartTextField' ||
            $type == 'EmailField' ||
            $type == 'PtCaptchaInputField' ||
            in_array('TextField', class_parents($type)))) {

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

        if ($type == 'TextareaField' ||
            in_array('TextareaField', class_parents($type))) {

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
            $baseUrl = Director::baseUrl();

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
}