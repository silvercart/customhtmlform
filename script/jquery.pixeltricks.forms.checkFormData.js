// Namensraum initialisieren und ggfs. vorhandenen verwenden
var pixeltricks         = pixeltricks       ? pixeltricks       : [];
    pixeltricks.forms   = pixeltricks.forms ? pixeltricks.forms : [];

/**
 * Methoden zur Feldpruefung.
 */
(function($) {pixeltricks.forms.checkFormData = function()
{
    /**
     * Workaround fuer Selbstreferenzierung in Closures.
     */
    var that = this;

    /**
     * Enthaelt den Feldwert.
     */
    this.fieldValue = '';

    /**
     * Enthaelt den Feldtyp.
     */
    this.fieldType = '';

    /**
     * Prueft, ob die Eingabe Sonderzeichen enthaelt und dieses Resultat dem
     * erwarteten Resultat entspricht.
     *
     * @param boolean expectedResult
     * @return array
     */
    this.hasSpecialSigns = function(expectedResult) {
        var errorMessage    = '';
        var success         = false;
        var valueMatch      = false;

        var matches = this.fieldValue.match(/^[A-Za-z0-9@\.]+$/);

        if (matches && (matches[0] == this.fieldValue)) {
            valueMatch = true;
        }

        if (valueMatch == expectedResult) {
            success = true;
        } else {
            success = false;

            if (valueMatch) {
                if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
                    errorMessage = 'Dieses Feld muss Sonderzeichen enthalten (andere Zeichen als Buchstaben, Zahlen und die Zeichen "@" und ".").';
                } else {
                    errorMessage = ss.i18n._t('Form.HASNOSPECIALSIGNS', 'Dieses Feld muss Sonderzeichen enthalten (andere Zeichen als Buchstaben, Zahlen und die Zeichen "@" und ".").');
                }
            } else {
                if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
                    errorMessage = 'Dieses Feld darf nur Buchstaben, Zahlen und die Zeichen "@" und "." enthalten.';
                } else {
                    errorMessage = ss.i18n._t('Form.HASSPECIALSIGNS', 'Dieses Feld darf nur Buchstaben, Zahlen und die Zeichen "@" und "." enthalten.');
                }
            }
        }

        return {
            success:        success,
            errorMessage:   errorMessage
        };
    };

    /**
     * Checks, whether the given string matches basicly an email address.
     * The rule is: one or more chars, then '@', then two ore more chars, then
     * '.', then two or more chars. This matching was simplified because the 
     * stricter version did not match some special cases.
     *
     * @param boolean expectedResult
     * @return array
     */
    this.isEmailAddress = function(expectedResult) {
        var errorMessage    = '';
        var success         = false;
        var valueMatch      = false;

        if (this.fieldValue == '') {
            success = true;
        } else {
            var valueMatch = this.isRFC822Email(this.fieldValue);

            if (valueMatch == expectedResult) {
                success = true;
            } else {
                success = false;

                if (valueMatch) {
                    if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
                        errorMessage = "Please don't enter an email address.";
                    } else {
                        errorMessage = ss.i18n._t('Form.MUSTNOTBEEMAILADDRESS', "Please don't enter an email address.");
                    }
                } else {
                    if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
                        errorMessage = 'Please enter a valid email address.';
                    } else {
                        errorMessage = ss.i18n._t('Form.MUSTBEEMAILADDRESS', 'Please enter a valid email address.');
                    }
                }
            }
        }

        return {
            success:        success,
            errorMessage:   errorMessage
        };
    };

    /**
     * RegExp for checking if an email address conforms to RFC822 standard.
     *
     * JavaScript function to check an email address conforms to RFC822 (http://www.ietf.org/rfc/rfc0822.txt)
     *
     * Version: 0.2
     * Author: Ross Kendall
     * Created: 2006-12-16
     * Updated: 2007-03-22
     *
     * Based on the PHP code by Cal Henderson
     * http://iamcal.com/publish/articles/php/parsing_email/
     *
     * Portions copyright (C) 2006  Ross Kendall - http://rosskendall.com
     * Portions copyright (C) 1993-2005 Cal Henderson - http://iamcal.com
     *
     * Licenced under Creative Commons _or_ GPL according to the terms below...
     *
     * --
     *
     * Licensed under a Creative Commons Attribution-ShareAlike 2.5 License
     *
     * You are free:
     *
     * to Share -- to copy, distribute, display, and perform the work
     * to Remix -- to make derivative works
     *
     * Under the following conditions:
     *
     * Attribution. You must attribute the work in the manner specified by the author or licensor.
     * Share Alike. If you alter, transform, or build upon this work, you may distribute the resulting work only under a license identical to this one.
     *
     * For any reuse or distribution, you must make clear to others the license terms of this work.
     * Any of these conditions can be waived if you get permission from the copyright holder.
     *
     * http://creativecommons.org/licenses/by-sa/2.5/
     *
     * --
     *
     * This program is free software; you can redistribute it and/or
     * modify it under the terms of the GNU General Public License
     * as published by the Free Software Foundation; either version 2
     * of the License, or (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with this program; if not, write to the Free Software
     * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
     * http://www.gnu.org/copyleft/gpl.html
     *
     * @param {string} email
     *
     * @return {Boolean}
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2012-11-19
     */
    this.isRFC822Email = function(email) {
        return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test( email );
    };

    /**
     * Prueft, ob die Eingabe in ein Captchafield korrekt war.
     *
     * @return array
     */
    this.PtCaptchaInput = function(parameters) {

        var errorMessage    = '';
        var success         = true;

        return {
            success:        success,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft, ob ein Feld leer ist und dieses Resultat dem erwarteten Resultat
     * entspricht.
     *
     * @param boolean expectedResult
     * @return array
     */
    this.isFilledIn = function(expectedResult)
    {
        var errorMessage    = '';
        var isFilledIn      = true;
        var success         = false;

        if (this.fieldType == 'CheckboxField')
        {
            isFilledIn = this.fieldValue;
            
            if (isFilledIn == 'checked' ||
                isFilledIn) {
                
                isFilledIn = true;
            } else {
                isFilledIn = false;
            }
        }
        else if (this.fieldType == 'OptionsetField' ||
                 this.fieldType == 'SilvercartCheckoutOptionsetField' ||
                 this.fieldType == 'SilvercartShippingOptionsetField' ||
                 this.fieldType == 'SilvercartAddressOptionsetField')
        {
            if (this.fieldValue == undefined ||
                this.fieldValue.length == 0) {
                isFilledIn = false;
            } else {
                isFilledIn = true;
            }
        }
        else
        {
            var checkValue = this.getValueWithoutWhitespace(this.fieldValue);

            if (checkValue === '')
            {
                isFilledIn = false;
            }
        }

        if (isFilledIn === expectedResult)
        {
            success = true;
        }

        if (!success)
        {
            if (isFilledIn)
            {
                if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
                    errorMessage = 'Dieses Feld muss leer sein.';
                } else {
                    errorMessage = ss.i18n._t('Form.FIELD_MUST_BE_EMPTY', 'Dieses Feld muss leer sein.');
                }
            }
            else
            {
                if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
                    errorMessage = 'Dieses Feld darf nicht leer sein.';
                } else {
                    errorMessage = ss.i18n._t('Form.FIELD_MAY_NOT_BE_EMPTY', 'Dieses Feld darf nicht leer sein.');
                }
            }
        }

        return {
            success:        success,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft, ob der Wert eines Feldes leer ist; ob ein Fehler zurueckgegeben
     * wird, haengt davon ab, ob das als Abhaengigkeit gegebene Feld
     * ausgefuellt ist.
     *
     * @param array parameters
     */
    this.isFilledInDependantOn = function(parameters)
    {
        var errorMessage        = '';
        var isFilledInCorrectly = true;
        var checkValue          = this.getValueWithoutWhitespace(this.fieldValue);

        if (typeof parameters == 'object')
        {
            if (!parameters[0].field ||
                !parameters[0].hasValue)
            {
                // Fehlerbehandlung noch offen: serverseitig pruefen lassen
            }

            // Abfrage fuer Checkboxen
            if ($('input[@name=' + [parameters[0].field] + ']:checked').val() == parameters[0].hasValue)
            {
                if (checkValue.length == 0)
                {
                    isFilledInCorrectly = false;
                }
            }
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'Dieses Feld darf nicht leer sein.';
        } else {
            errorMessage = ss.i18n._t('Form.FIELD_MAY_NOT_BE_EMPTY', 'Dieses Feld darf nicht leer sein.');
        }

        return {
            success:        isFilledInCorrectly,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft, ob die Laenge des Wertes der angegebenen Mindestlaenge
     * entspricht. Whitespaces am Anfang und Ende des Wertes werden fuer den
     * Vergleich entfernt.
     *
     * @param int minLength
     * @return array
     */
    this.hasMinLength = function(minLength)
    {
        var errorMessage    = '';
        var hasMinLength    = true;
        var checkValue      = this.getValueWithoutWhitespace(this.fieldValue);

        if (checkValue.length > 0 &&
            checkValue.length < minLength)
        {
            hasMinLength = false;
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'Bitte geben Sie mindestens ' + minLength + ' Zeichen ein.';
        } else {
            errorMessage = ss.i18n.sprintf(ss.i18n._t('Form.MIN_CHARS', 'Bitte geben Sie mindestens %s Zeichen ein.'), minLength);
        }

        return {
            success:        hasMinLength,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft, ob die Laenge des Wertes der angegebenen Laenge
     * entspricht. Whitespaces am Anfang und Ende des Wertes werden fuer den
     * Vergleich entfernt.
     *
     * @param int length
     * @return array
     */
    this.hasLength = function(length)
    {
        var errorMessage    = '';
        var hasLength       = true;
        var checkValue      = jQuery.trim(this.fieldValue);

        if (checkValue.length > 0 &&
            checkValue.length != length)
        {
            hasLength = false;
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'Dieses Feld erfordert ' + length + ' Zeichen.';
        } else {
            errorMessage = ss.i18n.sprintf(ss.i18n._t('Form.FIELD_REQUIRES_NR_OF_CHARS', 'Dieses Feld erfordert %s Zeichen.'), length);
        }

        return {
            success:        hasLength,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft ob der Wert eines Feldes dem Wert eines anderen Feldes
     * entspricht.
     *
     * @param array (
     *     'value'      => string: Wert den das Feld haben muss
     *     'fieldName'  => string: Name des anderen Feldes
     * )
     * @return array
     */
    this.mustEqual = function(parameters)
    {
        var errorMessage    = '';
        var isEqual         = true;

        if (this.fieldValue != parameters.value)
        {
            isEqual = false;
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'Bitte geben Sie den gleichen Wert ein wie im Feld "' + parameters.fieldName + '".';
        } else {
            errorMessage = ss.i18n.sprintf(ss.i18n._t('Form.REQUIRES_SAME_VALUE_AS_IN_FIELD', 'Bitte geben Sie den gleichen Wert ein wie im Feld "%s".'), parameters.fieldName);
        }

        return {
            success     :   isEqual,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft ob der Wert eines Feldes dem Wert eines anderen Feldes
     * nicht entspricht.
     *
     * @param array (
     *     'value'      => string: Wert den das Feld nicht haben darf
     *     'fieldName'  => string: Name des anderen Feldes
     * )
     * @return array
     */
    this.mustNotEqual = function(parameters)
    {
        var errorMessage    = '';
        var isNotEqual      = true;

        if (this.fieldValue == parameters.value)
        {
            isNotEqual = false;
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'Dieses Feld darf nicht den gleichen Wert wie das Feld "' + parameters.fieldName + '" haben.';
        } else {
            errorMessage = ss.i18n.sprintf(ss.i18n._t('Form.REQUIRES_OTHER_VALUE_AS_IN_FIELD', 'Dieses Feld darf nicht den gleichen Wert wie das Feld "%s" haben.'), parameters.fieldName);
        }

        return {
            success     :   isNotEqual,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft, ob ein Feld ausschliesslich aus Zahlen besteht.
     *
     * @param boolean
     * @return array
     */
    this.isNumbersOnly = function(expectedResult)
    {
        var errorMessage            = '';
        var consistsOfNumbersOnly   = true;
        var success                 = false;
        var checkValue              = that.fieldValue.replace(/[0-9]*/g, '');

        if (checkValue.length > 0)
        {
            consistsOfNumbersOnly = false;
        }

        if (consistsOfNumbersOnly == expectedResult)
        {
            success = true;
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'Dieses Feld darf nur Zahlen enthalten.';
        } else {
            errorMessage = ss.i18n._t('Form.NUMBERS_ONLY', 'Dieses Feld darf nur Zahlen enthalten.');
        }

        return {
            success:        success,
            errorMessage:   errorMessage
        };
    }

    /**
     * Does a field contain only characters for quantity specification?
     *
     * @param {int} numberOfDecimalPlaces The number of decimal places that are allowed
     *
     * @return {object}
     */
    this.isDecimalNumber = function(numberOfDecimalPlaces)
    {
        return {
            success:        true,
            errorMessage:   ''
        };
        var errorMessage      = '';
        var isQuantityField   = true;
        var success           = true;
        var checkValue        = that.fieldValue.replace(/[0-9,\.]*/g, '');
        var cleanValue        = that.fieldValue.replace(/,/g, '.');

        if (checkValue.length > 0) {
            isQuantityField = false;
        }

        if (isQuantityField === false) {
            if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
                errorMessage = 'Dieses Feld darf nur Zahlen sowie "." und "," enthalten.';
            } else {
                errorMessage = ss.i18n._t('Form.QUANTITY_ONLY', 'Dieses Feld darf nur Zahlen sowie "." und "," enthalten.');
            }

            success = false;
        } else {
            // Check for number of decimal places
            var separatorPos         = cleanValue.indexOf('.');
            var decimalPlacesInValue = that.fieldValue.length - (separatorPos + 1);

            if (decimalPlacesInValue > numberOfDecimalPlaces) {
                if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
                    errorMessage = 'Dieses Feld darf maximal ' + numberOfDecimalPlaces + ' Dezimalstellen enthalten.';
                } else {
                    errorMessage = ss.i18n.sprintf(
                        ss.i18n._t('Form.MAX_DECIMAL_PLACES_ALLOWED', 'Dieses Feld darf maximal %s Dezimalstellen enthalten.'),
                        numberOfDecimalPlaces
                    );
                }

                success = false;
            }
        }

        return {
            success:        success,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft, ob der Wert eines Feldes einer Waehrungsangabe entspricht.
     *
     * @param mixed expectedResult
     * @return array
     */
    this.isCurrency = function(expectedResult)
    {
        var errorMessage    = '';
        var success         = expectedResult;

        if (that.fieldValue.length > 0)
        {
            var nrOfMatches = that.fieldValue.search(
                /^[\d]{1,}[,]?[\d]{0,2}$/
            );

            if (nrOfMatches === -1)
            {
                success = false;
            }
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'In dieses Feld muss eine Währungsangabe (z.B. "1499,95") eingetragen werden.';
        } else {
            errorMessage = ss.i18n._t('Form.CURRENCY_ONLY', 'In dieses Feld muss eine Währungsangabe (z.B. "1499,95") eingetragen werden.');
        }

        return {
            success:        success,
            errorMessage:   errorMessage
        };
    }

    /**
     * Prueft, ob der Wert eines Feldes einer Datumsangabe entspricht.
     *
     * @param mixed expectedResult
     * @return array
     */
    this.isDate = function(expectedResult)
    {
        var errorMessage    = '';
        var success         = expectedResult;

        if (that.fieldValue.length > 0)
        {
            var nrOfMatches = that.fieldValue.search(
                /^[\d]{2}[\.]{1}[\d]{2}[\.]{1}[\d]{4}$/
            );

            if (nrOfMatches === -1)
            {
                success = false;
            }
        }

        if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
            errorMessage = 'In dieses Feld muss ein Datum (z.B. "31.01.2010") eingetragen werden.';
        } else {
            errorMessage = ss.i18n._t('Form.DATE_ONLY', 'In dieses Feld muss ein Datum (z.B. "31.01.2010") eingetragen werden.');
        }

        return {
            success:        success,
            errorMessage:   errorMessage
        };
    }

    /**
     * Entfernt alle Whitespaces aus dem uebergebenen Wert und gibt das
     * Ergebnis zurueck.
     *
     * @param string value
     * @return string
     */
    this.getValueWithoutWhitespace = function(value)
    {
        if (value)
        {
            return value.replace(/[\s]*/g, '');
        }
        else
        {
            return '';
        }
    }

    /**
     * Setzt den Wert des Feldes.
     *
     * @param Mixed fieldValue
     */
    this.setFieldValue = function(fieldValue)
    {
        this.fieldValue = fieldValue;
    }

    /**
     * Setzt den Typ des Felds.
     *
     * @param string fieldType
     */
    this.setFieldType = function(fieldType)
    {
        this.fieldType = fieldType;
    }
}})(jQuery);
