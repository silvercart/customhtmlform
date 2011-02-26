<?php
/*
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
 */

/**
 * Offers methods for form input validation
 *
 * @package pixeltricks_module
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @copyright 2010 pxieltricks GmbH
 * @since 25.10.2010
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class CheckFormData {

    /**
     * value of expression to be checked
     *
     * @var mixed
     */
    protected $value;

    /**
     * creates a new expression
     *
     * @param mixed $value value of expression to be checked
     */
    public function __construct($value) {
        $this -> value = $value;
    }

    /**
     * Checks if input containes special chars and if the result corresponds to
     * the expected result
     * 
     * @param boolean $expectedResult expected result
     *
     * @return array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function hasSpecialSigns($expectedResult) {

        $success        = false;
        $errorMessage   = '';
        $match          = false;

        preg_match(
            '/^[A-Za-z0-9@\.]+$/',
            $this->value,
            $matches
        );

        if ($matches && ($matches[0] == $this->value)) {
            $match = true;
        }

        if ($match == $expectedResult) {
            $success = true;
        } else {
            $success = false;

            if ($match) {
                $errorMessage = _t('Form.HASNOSPECIALSIGNS', 'Dieses Feld muss Sonderzeichen enthalten (andere Zeichen als Buchstaben, Zahlen und die Zeichen "@" und ".").');
            } else {
                $errorMessage = _t('Form.HASSPECIALSIGNS', 'Dieses Feld darf nur Buchstaben, Zahlen und die Zeichen "@" und "." enthalten.');
            }
        }

        return array(
            'success'       => $success,
            'errorMessage'  => $errorMessage
        );
    }

    /**
     * Is the input formally an email address?
     * Prueft, ob die Eingabe formal einer Email Adresse entspricht und
     * dieses Resultat dem erwarteten Resultat entspricht.
     *
     * @param boolean $expectedResult Das erwartete Resultat.
     *
     * @return array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function isEmailAddress($expectedResult) {

        $success        = false;
        $errorMessage   = '';
        $match          = false;

        preg_match(
            '/[a-zA-Z0-9\-_\.,]{1,}@[a-zA-Z0-9\-_,]{1,}\.[a-zA-Z,]{1,}/',
            $this->value,
            $matches
        );

        if ($matches && ($matches[0] == $this->value)) {
            $match = true;
        }

        if ($match == $expectedResult) {
            $success = true;
        } else {
            $success = false;

            if ($match) {
                $errorMessage = _t('Form.MUSTNOTBEEMAILADDRESS', 'Bitte geben Sie keine Email Adresse an.');
            } else {
                $errorMessage = _t('Form.MUSTBEEMAILADDRESS', 'Bitte geben Sie eine gültige Email Adresse an.');
            }
        }

        return array(
            'success'       => $success,
            'errorMessage'  => $errorMessage
        );
    }

    /**
     * checks captcha field input
     *
     * @param array $parameters form's and field's name
     *      array(
     *          'formName'  => string,
     *          'fieldName' => string
     *      )
     *
     * @return array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function PtCaptchaInput($parameters) {
        $session_key    = $parameters['fieldName'];
        $success        = false;
        $errorMessage   = '';
        $checkValue     = $this->getValueWithoutWhitespace($this->value);
        $temp_dir       = TEMP_FOLDER;

        if (file_exists($temp_dir.'/'.'cap_'.$session_key.'.txt')) {
            $fh     = fopen($temp_dir.'/'.'cap_'.$session_key.'.txt', "r");
            $hash   = fgets($fh);
            $hash2  = md5(strtolower($checkValue));

            if ($hash2 == $hash) {
                $success = true;
            } else {
                $success        = false;
                $errorMessage   = _t('Form.CAPTCHAFIELDNOMATCH', 'Diese Eingabe war leider falsch. Bitte versuchen Sie es erneut.');
            }
        } else {
            $success        = false;
            $errorMessage   = _t('Form.CAPTCHAFIELDNOMATCH', 'Es gab ein technisches Problem. Bitte versuchen Sie es erneut.');
        }

        return array(
            'success'       => $success,
            'errorMessage'  => $errorMessage
        );
    }

    /**
     * Checks if a field is empty and if this result is expected
     *
     * @param boolean $expectedResult the expected result
     *
     * @return array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function isFilledIn($expectedResult) {
        $isFilledIn     = true;
        $success        = false;
        $errorMessage   = '';
        $checkValue     = $this -> getValueWithoutWhitespace($this -> value);

        if ($checkValue === '') {
            $isFilledIn = false;
        }

        if ($isFilledIn === $expectedResult) {
            $success = true;
        }

        if (!$success) {
            if ($isFilledIn) {
                $errorMessage = _t('Form.FIELD_MUST_BE_EMPTY', 'Dieses Feld muss leer sein.');
            } else {
                $errorMessage = _t('Form.FIELD_MAY_NOT_BE_EMPTY', 'Dieses Feld darf nicht leer sein.');
            }
        }

        return array(
            'success'       => $success,
            'errorMessage'  => $errorMessage
        );
    }

    /**
     * Is the field empty? If a dependent field is not filled in an error will
     * be returned
     *
     * @param array $parameters fields to be checked
     *      array(
     *          array(
     *              'field'     => string,
     *              'hasValue'  => mixed
     *          ),
     *          array(
     *              'field'     => mixed
     *          )
     *      )
     *
     * @return array(
     *      'success'       => bool,
     *      'errorMessage'  => string
     * )
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function isFilledInDependantOn($parameters) {
        $isFilledInCorrectly    = true;
        $checkValue             = $this -> getValueWithoutWhitespace($this -> value);

        if (is_array($parameters)) {

            if (!isset($parameters[0]['field']) ||
                !isset($parameters[0]['hasValue'])) {

                throw new Exception(
                    'Feld ist falsch konfiguriert fuer Pruefung "CheckFormData->isFilledInDependantOn".'
                );
            }

            if ($parameters[1][$parameters[0]['field']] == $parameters[0]['hasValue']) {
                if (empty($checkValue)) {
                    $isFilledInCorrectly = false;
                }
            }
        } else {
            throw new Exception(
                'Feld ist falsch konfiguriert fuer Pruefung "CheckFormData->isFilledInDependantOn".'
            );
        }

        return array(
            'success'       => $isFilledInCorrectly,
            'errorMessage'  => _t('Form.FIELD_MUST_BE_FILLED_IN', 'Dieses Feld muss ausgefuellt sein.')
        );
    }

    /**
     * Does the input strings have the minimum length? Whitespaces do not count
     *
     * @param int $minLength the expression#s minimum length
     *
     * @return array(
     *      'success'       => bool,
     *      'errorMessage'  => string
     * )
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function hasMinLength($minLength) {
        $hasMinLength   = true;
        $checkValue     = trim($this -> value);

        if (strlen($checkValue) > 0 &&
            strlen($checkValue) < $minLength) {

            $hasMinLength = false;
        }

        return array(
            'success'       => $hasMinLength,
            'errorMessage'  => sprintf(_t('Form.MIN_CHARS', 'Bitte geben Sie mindestens %s Zeichen ein.'), $minLength)
        );
    }

    /**
     * Does the input string match the length defined? Whitespaces do not count
     *
     * @param int $length tthe expressions exact length
     *
     * @return array(
     *      'success'       => bool,
     *      'errorMessage'  => string
     * )
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function hasLength($length) {
        $hasLength  = true;
        $checkValue = trim($this -> value);

        if (strLen($checkValue) > 0 &&
            strlen($checkValue) !== $length) {

            $hasLength = false;
        }

        return array(
            'success'       => $hasLength,
            'errorMessage'  => sprintf(_t('Form.FIED_REQUIRES_NR_OF_CHARS', 'Dieses Feld erfordert %s Zeichen.'), $length)
        );
    }

    /**
     * Do the values of two fields match?
     *
     * @param array $parameters Value and field name to be compared
     *      array (
     *          'value'      => string: the value the field must have
     *          'fieldName'  => string: Name of the other field
     *      )
     *
     * @return array(
     *      'success'       => bool,
     *      'errorMessage'  => string
     * )
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function mustEqual($parameters) {
        $isEqual = true;

        if ($this -> value !== $parameters['value']) {
            $isEqual = false;
        }

        if (isset($parameters['fieldTitle'])) {
            $refererField = $parameters['fieldTitle'];
        } else {
            $refererField = $parameters['fieldName'];
        }

        return array(
            'success'       => $isEqual,
            'errorMessage'  => sprintf(_t('Form.REQUIRES_SAME_VALUE_AS_IN_FIELD', 'Bitte geben Sie den gleichen Wert ein wie im Feld "%s".'), $refererField)
        );
    }

    /**
     * checks if two field values do NOT match (inversion of mustEqual())
     *
     * @param array $parameters Value and field name to be compared
     *      array (
     *          'value'      => string: value the field must NOT have
     *          'fieldName'  => string: Name of the other field
     *      )
     *
     * @return array(
     *      'success'       => bool,
     *      'errorMessage'  => string
     * )
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function mustNotEqual($parameters) {
        $isNotEqual = true;

        if ($this -> value == $parameters['value']) {
            $isNotEqual = false;
        }

        if (isset($parameters['fieldTitle'])) {
            $refererField = $parameters['fieldTitle'];
        } else {
            $refererField = $parameters['fieldName'];
        }

        return array(
            'success'       => $isNotEqual,
            'errorMessage'  => sprintf(_t('Form.REQUIRES_OTHER_VALUE_AS_IN_FIELD', 'Dieses Feld darf nicht den gleichen Wert wie das Feld "%s" haben.'), $refererField)
        );
    }

    /**
     * Does a field contain number only
     *
     * @param boolean $expectedResult the expected result can be true or false
     *
     * @return array(
     *      'success'       => bool,
     *      'errorMessage'  => string
     * )
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function isNumbersOnly($expectedResult) {
        $consistsOfNumbersOnly  = true;
        $success                = false;

        $checkValue = preg_replace(
            '/[0-9]*/',
            '',
            $this -> value
        );

        if (strlen($checkValue) > 0) {
            $consistsOfNumbersOnly = false;
        }

        if ($consistsOfNumbersOnly === $expectedResult) {
            $success = true;
        }

        return array(
            'success'       => $success,
            'errorMessage'  => _t('Form.NUMBERS_ONLY', 'Dieses Feld darf nur Zahlen enthalten.')
        );
    }

    /**
     * Checks if the field input is a currency
     *
     * @param mixed $expectedResult the expected result
     *
     * @return array(
     *      'success'       => bool,
     *      'errorMessage'  => string
     * )
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function isCurrency($expectedResult) {
        $success = $expectedResult;

        if (!empty($this->value)) {
            $nrOfMatches = preg_match('/^[\d]*[,]?[^\D]*$/', $this->value, $matches);

            if ($nrOfMatches === 0) {
                $success = false;
            }
        }

        return array(
            'success'       => $success,
            'errorMessage'  => _t('Form.CURRENCY_ONLY', 'In dieses Feld muss eine Währungsangabe (z.B. "1499,95") eingetragen werden.')
        );
    }

    /**
     * Checks if the field input is a date
     *
     * @param mixed $expectedResult programmers expectation to be met
     *
     * @return array(
     *      'success'       => bool,
     *      'errorMessage'  => string
     * )
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function isDate($expectedResult) {
        $success = $expectedResult;

        if (!empty($this->value)) {
            $nrOfMatches = preg_match('/[\d]{2}[\.]{1}[\d]{2}[.]{1}[\d]{4}/', $this->value);

            if ($nrOfMatches === 0) {
                $success = false;
            }
        }

        return array(
            'success'       => $success,
            'errorMessage'  => _t('Form.DATE_ONLY', 'In dieses Feld muss ein Datum im Format "tt.mm.jjjj" eingetragen werden.')
        );
    }

    /**
     * removes a values whitespaces and returns the value cleaned
     *
     * @param string $value value to be cleaned of whitespaces
     *
     * @return string the cheaned value
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    private function getValueWithoutWhitespace($value) {
        return preg_replace('/[\s]*/', '', $value);
    }
}
