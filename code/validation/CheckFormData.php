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
 */

/**
 * Offers methods for form input validation
 *
 * @package CustomHtmlForm
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @copyright 2010 pixeltricks GmbH
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
     * @since 25.10.2010
     */
    public function hasSpecialSigns($expectedResult) {
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
                $errorMessage = _t('Form.HASNOSPECIALSIGNS', 'This field must contain special signs (other signs than letters, numbers and the signs "@" and ".").');
            } else {
                $errorMessage = _t('Form.HASSPECIALSIGNS', 'This field must not contain special signs (letters, numbers and the signs "@" and ".").');
            }
        }

        return array(
            'success'       => $success,
            'errorMessage'  => $errorMessage
        );
    }

    /**
     * Checks, whether the given string matches basicly an email address.
     * The rule is: one or more chars, then '@', then two ore more chars, then
     * '.', then two or more chars. This matching was simplified because the 
     * stricter version did not match some special cases.
     *
     * @param boolean $expectedResult Das erwartete Resultat.
     *
     * @return array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.06.2011
     */
    public function isEmailAddress($expectedResult) {
        $errorMessage   = '';

        if (empty($this->value)) {
            $success = true;
        } else {
            $match = $this->is_valid_email_address($this->value);

            if ($match == $expectedResult) {
                $success = true;
            } else {
                $success = false;

                if ($match) {
                    $errorMessage = _t('Form.MUSTNOTBEEMAILADDRESS', 'Please don\'t enter an email address.');
                } else {
                    $errorMessage = _t('Form.MUSTBEEMAILADDRESS', 'Please enter a valid email address.');
                }
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
     * @since 25.10.2010
     */
    public function PtCaptchaInput($parameters) {
        $customHtmlForm = Session::get('CustomHtmlForm');
        $spamCheck      = Session::get('CustomHtmlForm.SpamCheck');
        if (is_null($customHtmlForm)) {
            Session::set('CustomHtmlForm', array());
        }
        if (is_null($spamCheck)) {
            Session::set('CustomHtmlForm.SpamCheck', array());
        }
        Session::save();

        $codeToMatch = Session::get('CustomHtmlForm.SpamCheck.' . $parameters['fieldName']);

        $success        = false;
        $errorMessage   = '';
        $checkCode      = md5(strtolower($this->getValueWithoutWhitespace($this->value)));

        if ($checkCode === $codeToMatch) {
            $success = true;
        } else {
            $success        = false;
            $errorMessage   = _t('Form.CAPTCHAFIELDNOMATCH', 'Your entry was not correct. Please try again!');
        }

        return array(
            'success'       => $success,
            'errorMessage'  => $errorMessage
        );
    }

    /**
     * checks reCAPTCHA field input
     * 
     * @param array $parameters form and field name
     *
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.12.2016
     */
    public function GoogleRecaptchaField($parameters) {
        $success            = false;
        $gRecaptchaResponse = $_REQUEST['g-recaptcha-response'];
        $remoteIp           = $_SERVER['REMOTE_ADDR'];
        
        $recaptcha = new \ReCaptcha\ReCaptcha(GoogleRecaptchaField::get_recaptcha_secret());
        $resp = $recaptcha->verify($gRecaptchaResponse, $remoteIp);
        if ($resp->isSuccess()) {
            $success = true;
        } else {
            $errorMessage = _t('Form.CAPTCHAFIELDNOMATCH', 'Your entry was not correct. Please try again!');
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
     * @param string  $checkValue     Optional static value to check.
     *
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 14.07.2014
     */
    public function isFilledIn($expectedResult, $checkValue = null) {
        $isFilledIn     = true;
        $success        = false;
        $errorMessage   = '';
        if (is_null($checkValue)) {
            $checkValue = $this->getValueWithoutWhitespace($this->value);
        }

        if ($checkValue === '') {
            $isFilledIn = false;
        }

        if ($isFilledIn === $expectedResult) {
            $success = true;
        }

        if (!$success) {
            if ($isFilledIn) {
                $errorMessage = _t('Form.FIELD_MUST_BE_EMPTY', 'This field must be empty.');
            } else {
                $errorMessage = _t('Form.FIELD_MAY_NOT_BE_EMPTY', 'This field may not be empty.');
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
     * @throws Exception
     *
     * @return array(
     *      'success'       => bool,
     *      'errorMessage'  => string
     * )
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 25.10.2010
     */
    public function isFilledInDependantOn($parameters) {
        $isFilledInCorrectly    = true;
        $checkValue             = $this -> getValueWithoutWhitespace($this -> value);

        if (is_array($parameters)) {

            if (array_key_exists('requirement', $parameters[0])) {
                return $this->isValidDependantOn($parameters, 'isFilledIn');
            }
            
            if (!isset($parameters[0]['field']) ||
                !isset($parameters[0]['hasValue'])) {

                throw new Exception(
                    'Field is misconfigured for "CheckFormData->isFilledInDependantOn".'
                );
            }

            if ($parameters[1][$parameters[0]['field']] == $parameters[0]['hasValue']) {
                if (empty($checkValue)) {
                    $isFilledInCorrectly = false;
                }
            }
        } else {
            throw new Exception(
                'Field is misconfigured for "CheckFormData->isFilledInDependantOn".'
            );
        }

        return array(
            'success'       => $isFilledInCorrectly,
            'errorMessage'  => _t('Form.FIELD_MUST_BE_FILLED_IN', 'Please fill in this field.')
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
            'errorMessage'  => sprintf(_t('Form.MIN_CHARS', 'Enter at least %s characters.'), $minLength)
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
     * @since 25.10.2010
     */
    public function hasLength($length) {
        $hasLength        = true;
        $checkValue       = trim($this -> value);
        $checkValueLength = (int) strLen($checkValue);

        if ($checkValueLength > 0 &&
            $checkValueLength !== (int) $length) {

            $hasLength = false;
        }

        return array(
            'success'       => $hasLength,
            'errorMessage'  => sprintf(_t('Form.FIED_REQUIRES_NR_OF_CHARS', 'This field requires exactly %s characters.'), $length)
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
            'errorMessage'  => sprintf(_t('Form.REQUIRES_SAME_VALUE_AS_IN_FIELD', 'Please enter the same value as in field "%s".'), $refererField)
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
            'errorMessage'  => sprintf(_t('Form.REQUIRES_OTHER_VALUE_AS_IN_FIELD', 'This field may not have the same value as field "%s".'), $refererField)
        );
    }

    /**
     * Checks the equality of two fields dependant of another field.
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
     * @throws Exception
     *
     * @return array(
     *      'success'       => bool,
     *      'errorMessage'  => string
     * )
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.07.2014
     */
    public function mustEqualDependantOn($parameters) {
        return $this->isValidDependantOn($parameters, 'mustEqual');
    }

    /**
     * Checks the equality of two fields dependant of another field.
     *
     * @param array $parameters fields to be checked
     *
     * @throws Exception
     *
     * @return array(
     *      'success'       => bool,
     *      'errorMessage'  => string
     * )
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.07.2014
     */
    public function mustNotEqualDependantOn($parameters) {
        return $this->isValidDependantOn($parameters, 'mustNotEqual');
    }

    /**
     * Checks the validity of the field dependant of another field and a generic
     * validation method.
     *
     * @param array  $parameters fields to be checked
     * @param string $method     Method to call
     *
     * @throws Exception
     *
     * @return array(
     *      'success'       => bool,
     *      'errorMessage'  => string
     * )
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.07.2014
     */
    public function isValidDependantOn($parameters, $method) {
        $isValidDependantOn = array(
            'success'       => true,
            'errorMessage'  => '',
        );

        if (is_array($parameters)) {

            if (!isset($parameters[0]['field']) ||
                !isset($parameters[0]['requirement'])) {

                throw new Exception(
                    'Field is misconfigured for "CheckFormData->mustNotEqualDependantOn".'
                );
            }

            $requirement    = $parameters[0]['requirement'];
            $dependantValue = $parameters[1][$parameters[0]['field']];
            switch ($requirement) {
                case 'isFilledIn':
                default:
                    $result = $this->isFilledIn(true,$dependantValue);
                    break;
            }
            if ($result['success']) {
                $isValidDependantOn = $this->$method($parameters[2]);
            }
        } else {
            throw new Exception(
                'Field is misconfigured for "CheckFormData->mustNotEqualDependantOn".'
            );
        }

        return $isValidDependantOn;
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
            'errorMessage'  => _t('Form.NUMBERS_ONLY', 'This field may consist of numbers only.')
        );
    }

    /**
     * Does a field contain only characters for quantity specification?
     *
     * @param int $numberOfDecimalPlaces The number of decimal places that are allowed
     *
     * @return array(
     *      'success'       => bool,
     *      'errorMessage'  => string
     * )
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 22.11.2012
     */
    public function isDecimalNumber($numberOfDecimalPlaces) {
        $isQuantityField = true;
        $success         = true;
        $errorMessage    = '';

        $checkValue = preg_replace(
            '/[0-9,\.]*/',
            '',
            $this->value
        );
        $cleanValue = str_replace(',', '.', $this->value);

        if (strlen($checkValue) > 0) {
            $isQuantityField = false;
        }

        if ($isQuantityField === false) {
            $errorMessage = _t('Form.QUANTITY_ONLY', 'This field may consist of numbers and "." or "," only.');
            $success      = false;
        } else {
            // Check for number of decimal places
            $separatorPos         = strpos($cleanValue, '.');
            $decimalPlacesInValue = strlen($this->value) - ($separatorPos + 1);

            if ($decimalPlacesInValue > $numberOfDecimalPlaces) {
                $errorMessage = sprintf(
                    _t('Form.MAX_DECIMAL_PLACES_ALLOWED', 'Dieses Feld darf maximal %s Dezimalstellen enthalten.'),
                    $numberOfDecimalPlaces
                );
                $success = false;
            }
        }

        return array(
            'success'       => $success,
            'errorMessage'  => $errorMessage
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
            'errorMessage'  => _t('Form.CURRENCY_ONLY', 'Please enter a valid currency amount (e.g. 1499,00).')
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
            'errorMessage'  => _t('Form.DATE_ONLY', 'Please enter a valid german date (e.g. "dd.mm.yyyy").')
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
     * @since 25.10.2010
     */
    private function getValueWithoutWhitespace($value) {
        return preg_replace('/[\s]*/', '', $value);
    }

    /**
     * Taken from "https://github.com/iamcal/rfc822".
     *
     * Checks if an email conforms to the rfc822 standard.
     *
     * @param string $email   The email address to check
     * @param array  $options Additional options:
     *      - 'allow_comments'
     *      - 'public_internet'
     *
     * @return boolean
     *
     * @author Cal Henderson <cal@iamcal.com>
     * @since 19.11.2012
     */
    private function is_valid_email_address($email, $options=array()) {
        #
        # you can pass a few different named options as a second argument,
        # but the defaults are usually a good choice.
        #
        $defaults = array(
            'allow_comments'	=> true,
            'public_internet'	=> true, # turn this off for 'strict' mode
        );

        $opts = array();
        foreach ($defaults as $k => $v) {
            $opts[$k] = isset($options[$k]) ? $options[$k] : $v;
        }
        $options = $opts;

        ####################################################################################
        #
        # NO-WS-CTL       =       %d1-8 /         ; US-ASCII control characters
        #                         %d11 /          ;  that do not include the
        #                         %d12 /          ;  carriage return, line feed,
        #                         %d14-31 /       ;  and white space characters
        #                         %d127
        # ALPHA          =  %x41-5A / %x61-7A   ; A-Z / a-z
        # DIGIT          =  %x30-39

        $no_ws_ctl	= "[\\x01-\\x08\\x0b\\x0c\\x0e-\\x1f\\x7f]";
        $alpha		= "[\\x41-\\x5a\\x61-\\x7a]";
        $digit		= "[\\x30-\\x39]";
        $cr		    = "\\x0d";
        $lf		    = "\\x0a";
        $crlf		= "(?:$cr$lf)";

        ####################################################################################
        #
        # obs-char        =       %d0-9 / %d11 /          ; %d0-127 except CR and
        #                         %d12 / %d14-127         ;  LF
        # obs-text        =       *LF *CR *(obs-char *LF *CR)
        # text            =       %d1-9 /         ; Characters excluding CR and LF
        #                         %d11 /
        #                         %d12 /
        #                         %d14-127 /
        #                         obs-text
        # obs-qp          =       "\" (%d0-127)
        # quoted-pair     =       ("\" text) / obs-qp
        $obs_char	= "[\\x00-\\x09\\x0b\\x0c\\x0e-\\x7f]";
        $obs_text	= "(?:$lf*$cr*(?:$obs_char$lf*$cr*)*)";
        $text		= "(?:[\\x01-\\x09\\x0b\\x0c\\x0e-\\x7f]|$obs_text)";

        #
        # there's an issue with the definition of 'text', since 'obs_text' can
        # be blank and that allows qp's with no character after the slash. we're
        # treating that as bad, so this just checks we have at least one
        # (non-CRLF) character
        #
        $text		    = "(?:$lf*$cr*$obs_char$lf*$cr*)";
        $obs_qp		    = "(?:\\x5c[\\x00-\\x7f])";
        $quoted_pair	= "(?:\\x5c$text|$obs_qp)";

        ####################################################################################
        #
        # obs-FWS         =       1*WSP *(CRLF 1*WSP)
        # FWS             =       ([*WSP CRLF] 1*WSP) /   ; Folding white space
        #                         obs-FWS
        # ctext           =       NO-WS-CTL /     ; Non white space controls
        #                         %d33-39 /       ; The rest of the US-ASCII
        #                         %d42-91 /       ;  characters not including "(",
        #                         %d93-126        ;  ")", or "\"
        # ccontent        =       ctext / quoted-pair / comment
        # comment         =       "(" *([FWS] ccontent) [FWS] ")"
        # CFWS            =       *([FWS] comment) (([FWS] comment) / FWS)

        #
        # note: we translate ccontent only partially to avoid an infinite loop
        # instead, we'll recursively strip *nested* comments before processing
        # the input. that will leave 'plain old comments' to be matched during
        # the main parse.
        #
        $wsp		= "[\\x20\\x09]";
        $obs_fws	= "(?:$wsp+(?:$crlf$wsp+)*)";
        $fws		= "(?:(?:(?:$wsp*$crlf)?$wsp+)|$obs_fws)";
        $ctext		= "(?:$no_ws_ctl|[\\x21-\\x27\\x2A-\\x5b\\x5d-\\x7e])";
        $ccontent	= "(?:$ctext|$quoted_pair)";
        $comment	= "(?:\\x28(?:$fws?$ccontent)*$fws?\\x29)";
        $cfws		= "(?:(?:$fws?$comment)*(?:$fws?$comment|$fws))";

        #
        # these are the rules for removing *nested* comments. we'll just detect
        # outer comment and replace it with an empty comment, and recurse until
        # we stop.
        #
        $outer_ccontent_dull	= "(?:$fws?$ctext|$quoted_pair)";
        $outer_ccontent_nest	= "(?:$fws?$comment)";
        $outer_comment		    = "(?:\\x28$outer_ccontent_dull*(?:$outer_ccontent_nest$outer_ccontent_dull*)+$fws?\\x29)";

        ####################################################################################
        #
        # atext           =       ALPHA / DIGIT / ; Any character except controls,
        #                         "!" / "#" /     ;  SP, and specials.
        #                         "$" / "%" /     ;  Used for atoms
        #                         "&" / "'" /
        #                         "*" / "+" /
        #                         "-" / "/" /
        #                         "=" / "?" /
        #                         "^" / "_" /
        #                         "`" / "{" /
        #                         "|" / "}" /
        #                         "~"
        # atom            =       [CFWS] 1*atext [CFWS]
        $atext		= "(?:$alpha|$digit|[\\x21\\x23-\\x27\\x2a\\x2b\\x2d\\x2f\\x3d\\x3f\\x5e\\x5f\\x60\\x7b-\\x7e])";
        $atom		= "(?:$cfws?(?:$atext)+$cfws?)";

        ####################################################################################
        #
        # qtext           =       NO-WS-CTL /     ; Non white space controls
        #                         %d33 /          ; The rest of the US-ASCII
        #                         %d35-91 /       ;  characters not including "\"
        #                         %d93-126        ;  or the quote character
        # qcontent        =       qtext / quoted-pair
        # quoted-string   =       [CFWS]
        #                         DQUOTE *([FWS] qcontent) [FWS] DQUOTE
        #                         [CFWS]
        # word            =       atom / quoted-string
        $qtext		    = "(?:$no_ws_ctl|[\\x21\\x23-\\x5b\\x5d-\\x7e])";
        $qcontent	    = "(?:$qtext|$quoted_pair)";
        $quoted_string	= "(?:$cfws?\\x22(?:$fws?$qcontent)*$fws?\\x22$cfws?)";

        #
        # changed the '*' to a '+' to require that quoted strings are not empty
        #
        $quoted_string	= "(?:$cfws?\\x22(?:$fws?$qcontent)+$fws?\\x22$cfws?)";
        $word		    = "(?:$atom|$quoted_string)";

        ####################################################################################
        #
        # obs-local-part  =       word *("." word)
        # obs-domain      =       atom *("." atom)
        $obs_local_part	= "(?:$word(?:\\x2e$word)*)";
        $obs_domain	    = "(?:$atom(?:\\x2e$atom)*)";

        ####################################################################################
        #
        # dot-atom-text   =       1*atext *("." 1*atext)
        # dot-atom        =       [CFWS] dot-atom-text [CFWS]
        $dot_atom_text	= "(?:$atext+(?:\\x2e$atext+)*)";
        $dot_atom	    = "(?:$cfws?$dot_atom_text$cfws?)";

        ####################################################################################
        #
        # domain-literal  =       [CFWS] "[" *([FWS] dcontent) [FWS] "]" [CFWS]
        # dcontent        =       dtext / quoted-pair
        # dtext           =       NO-WS-CTL /     ; Non white space controls
        #
        #                         %d33-90 /       ; The rest of the US-ASCII
        #                         %d94-126        ;  characters not including "[",
        #                                         ;  "]", or "\"
        $dtext		= "(?:$no_ws_ctl|[\\x21-\\x5a\\x5e-\\x7e])";
        $dcontent	= "(?:$dtext|$quoted_pair)";
        $domain_literal	= "(?:$cfws?\\x5b(?:$fws?$dcontent)*$fws?\\x5d$cfws?)";

        ####################################################################################
        #
        # local-part      =       dot-atom / quoted-string / obs-local-part
        # domain          =       dot-atom / domain-literal / obs-domain
        # addr-spec       =       local-part "@" domain
        $local_part	= "(($dot_atom)|($quoted_string)|($obs_local_part))";
        $domain		= "(($dot_atom)|($domain_literal)|($obs_domain))";
        $addr_spec	= "$local_part\\x40$domain";

        #
        # this was previously 256 based on RFC3696, but dominic's errata was accepted.
        #
        if (strlen($email) > 254) {
            return false;
        }

        #
        # we need to strip nested comments first - we replace them with a simple comment
        #

        if ($options['allow_comments']) {
            $email = $this->email_strip_comments($outer_comment, $email, "(x)");
        }

        #
        # now match what's left
        #

        if (!preg_match("!^$addr_spec$!", $email, $m)) {
            return false;
        }

        $bits = array(
            'local'			 => isset($m[1]) ? $m[1] : '',
            'local-atom'	 => isset($m[2]) ? $m[2] : '',
            'local-quoted'	 => isset($m[3]) ? $m[3] : '',
            'local-obs'		 => isset($m[4]) ? $m[4] : '',
            'domain'		 => isset($m[5]) ? $m[5] : '',
            'domain-atom'	 => isset($m[6]) ? $m[6] : '',
            'domain-literal' => isset($m[7]) ? $m[7] : '',
            'domain-obs'	 => isset($m[8]) ? $m[8] : '',
        );


        #
        # we need to now strip comments from $bits[local] and $bits[domain],
        # since we know they're in the right place and we want them out of the
        # way for checking IPs, label sizes, etc
        #
        if ($options['allow_comments']) {
            $bits['local']	= $this->email_strip_comments($comment, $bits['local']);
            $bits['domain']	= $this->email_strip_comments($comment, $bits['domain']);
        }

        #
        # length limits on segments
        #
        if (strlen($bits['local']) > 64) {
            return false;
        }
        if (strlen($bits['domain']) > 255) {
            return false;
        }

        #
        # restrictions on domain-literals from RFC2821 section 4.1.3
        #
        # RFC4291 changed the meaning of :: in IPv6 addresses - i can mean one or
        # more zero groups (updated from 2 or more).
        #
        if (strlen($bits['domain-literal'])) {
            $Snum			= "(\d{1,3})";
            $IPv4_address_literal	= "$Snum\.$Snum\.$Snum\.$Snum";

            $IPv6_hex		= "(?:[0-9a-fA-F]{1,4})";

            $IPv6_full		= "IPv6\:$IPv6_hex(?:\:$IPv6_hex){7}";

            $IPv6_comp_part		= "(?:$IPv6_hex(?:\:$IPv6_hex){0,7})?";
            $IPv6_comp		= "IPv6\:($IPv6_comp_part\:\:$IPv6_comp_part)";

            $IPv6v4_full		= "IPv6\:$IPv6_hex(?:\:$IPv6_hex){5}\:$IPv4_address_literal";

            $IPv6v4_comp_part	= "$IPv6_hex(?:\:$IPv6_hex){0,5}";
            $IPv6v4_comp		= "IPv6\:((?:$IPv6v4_comp_part)?\:\:(?:$IPv6v4_comp_part\:)?)$IPv4_address_literal";

            #
            # IPv4 is simple
            #
            if (preg_match("!^\[$IPv4_address_literal\]$!", $bits['domain'], $m)) {

                if (intval($m[1]) > 255) {
                    return false;
                }
                if (intval($m[2]) > 255) {
                    return false;
                }
                if (intval($m[3]) > 255) {
                    return false;
                }
                if (intval($m[4]) > 255) {
                    return false;
                }

            } else {

                #
                # this should be IPv6 - a bunch of tests are needed here :)
                #

                while (1) {

                    if (preg_match("!^\[$IPv6_full\]$!", $bits['domain'])) {
                        break;
                    }

                    if (preg_match("!^\[$IPv6_comp\]$!", $bits['domain'], $m)) {
                        list($a, $b) = explode('::', $m[1]);
                        $folded = (strlen($a) && strlen($b)) ? "$a:$b" : "$a$b";
                        $groups = explode(':', $folded);
                        if (count($groups) > 7) {
                            return false;
                        }
                        break;
                    }

                    if (preg_match("!^\[$IPv6v4_full\]$!", $bits['domain'], $m)) {

                        if (intval($m[1]) > 255) {
                            return false;
                        }
                        if (intval($m[2]) > 255) {
                            return false;
                        }
                        if (intval($m[3]) > 255) {
                            return false;
                        }
                        if (intval($m[4]) > 255) {
                            return false;
                        }
                        break;
                    }

                    if (preg_match("!^\[$IPv6v4_comp\]$!", $bits['domain'], $m)) {
                        list($a, $b) = explode('::', $m[1]);
                        $b = substr($b, 0, -1); # remove the trailing colon before the IPv4 address
                        $folded = (strlen($a) && strlen($b)) ? "$a:$b" : "$a$b";
                        $groups = explode(':', $folded);
                        if (count($groups) > 5) {
                            return false;
                        }
                        break;
                    }

                    return false;
                }
            }
        } else {
            #
            # the domain is either dot-atom or obs-domain - either way, it's
            # made up of simple labels and we split on dots
            #

            $labels = explode('.', $bits['domain']);

            #
            # this is allowed by both dot-atom and obs-domain, but is un-routeable on the
            # public internet, so we'll fail it (e.g. user@localhost)
            #

            if ($options['public_internet']) {
                if (count($labels) == 1) {
                    return false;
                }
            }

            #
            # checks on each label
            #

            foreach ($labels as $label) {

                if (strlen($label) > 63) {
                    return false;
                }
                if (substr($label, 0, 1) == '-') {
                    return false;
                }
                if (substr($label, -1) == '-') {
                    return false;
                }
            }

            #
            # last label can't be all numeric
            #

            if ($options['public_internet']) {
                if (preg_match('!^[0-9]+$!', array_pop($labels))) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Taken from "https://github.com/iamcal/rfc822".
     *
     * Removes comments from an email.
     *
     * @param string $comment The comment
     * @param string $email   The email
     * @param string $replace The replace string
     *
     * @return string
     *
     * @author Cal Henderson <cal@iamcal.com>
     * @since 19.11.2012
     */
    private function email_strip_comments($comment, $email, $replace='') {
        while (1) {
            $new = preg_replace("!$comment!", $replace, $email);
            if (strlen($new) == strlen($email)) {
                return $email;
            }
            $email = $new;
        }
    }
}
