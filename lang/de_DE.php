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
 * @subpackage i18n
 * @ignore
 */

i18n::include_locale_file('customhtmlform', 'en_US');

global $lang;

if (array_key_exists('de_DE', $lang) && is_array($lang['de_DE'])) {
    $lang['de_DE'] = array_merge($lang['en_US'], $lang['de_DE']);
} else {
    $lang['de_DE'] = $lang['en_US'];
}

$lang['de_DE']['CustomHtmlForm']['REQUIRED_FIELD_MARKER'] = '<span class="required-field">[ Pflichtfeld ]</span>';

$lang['de_DE']['Form']['FIELD_MUST_BE_EMPTY'] = 'Dieses Feld muss leer sein.';
$lang['de_DE']['Form']['FIELD_MAY_NOT_BE_EMPTY'] = 'Dieses Feld darf nicht leer sein.';
$lang['de_DE']['Form']['FIELD_MUST_BE_FILLED_IN'] = 'Dieses Feld muss ausgefuellt sein.';
$lang['de_DE']['Form']['MIN_CHARS'] = 'Bitte gib mindestens %s Zeichen ein.';
$lang['de_DE']['Form']['FIED_REQUIRES_NR_OF_CHARS'] = 'Dieses Feld erfordert %s Zeichen.';
$lang['de_DE']['Form']['REQUIRES_SAME_VALUE_AS_IN_FIELD'] = 'Bitte geben Sie den gleichen Wert ein wie im Feld "%s".';
$lang['de_DE']['Form']['REQUIRES_OTHER_VALUE_AS_IN_FIELD'] = 'Dieses Feld darf nicht den gleichen Wert wie das Feld "%s" haben.';
$lang['de_DE']['Form']['QUANTITY_ONLY'] = 'Dieses Feld darf nur Zahlen sowie "." und "," enthalten.';
$lang['de_DE']['Form']['NUMBERS_ONLY'] = 'Dieses Feld darf nur Zahlen enthalten.';
$lang['de_DE']['Form']['MAX_DECIMAL_PLACES_ALLOWED_'] = 'Dieses Feld darf maximal %s Dezimalstellen enthalten.';
$lang['de_DE']['Form']['CURRENCY_ONLY'] = 'In dieses Feld muss eine Währungsangabe (z.B. "1499,95") eingetragen werden.';
$lang['de_DE']['Form']['DATE_ONLY'] = 'In dieses Feld muss ein Datum im Format "tt.mm.jjjj" eingetragen werden.';
$lang['de_DE']['Form']['CAPTCHAFIELDNOMATCH'] = 'Diese Eingabe war leider falsch. Versuchen Sie es bitte erneut!';
$lang['de_DE']['Form']['HASNOSPECIALSIGNS'] = 'Dieses Feld muss Sonderzeichen enthalten (andere Zeichen als Buchstaben, Zahlen und die Zeichen "@" und ".").';
$lang['de_DE']['Form']['HASSPECIALSIGNS'] = 'Dieses Feld darf nur Buchstaben, Zahlen und die Zeichen "@" und "." enthalten.';
$lang['de_DE']['Form']['MANDATORYFIELD'] = 'Dieses Feld muss ausgefüllt werden.';
$lang['de_DE']['Form']['MUSTNOTBEEMAILADDRESS'] = 'Bitte geben Sie hier keine Email Adresse an.';
$lang['de_DE']['Form']['MUSTBEEMAILADDRESS'] = 'Bitte geben Sie hier eine gültige Email Adresse an.';

$lang['de_DE']['CustomHtmlFormStepPage']['BASE_NAME'] = 'Basisname für Formular Objekt- und Templatedateien: ';
$lang['de_DE']['CustomHtmlFormStepPage']['SHOW_CANCEL'] = 'Abbrechen Link zeigen';
$lang['de_DE']['CustomHtmlFormStepPage']['CANCEL_TARGET'] = 'Auf welche Seite soll der Abbrechen-Link fuehren: ';

$lang['de_DE']['CustomHtmlFormConfiguration']['SpamCheck_numberOfCharsInCaptcha']   = 'Anzahl der Zeichen im Captcha';
$lang['de_DE']['CustomHtmlFormConfiguration']['SpamCheck_width']                    = 'Breite in Pixel';
$lang['de_DE']['CustomHtmlFormConfiguration']['SpamCheck_height']                   = 'Höhe in Pixel';
$lang['de_DE']['CustomHtmlFormConfiguration']['SpamCheck_jpgQuality']               = 'JPG Qualität des Captcha-Bilds (0 [schlechteste] bis 100 [beste])';
$lang['de_DE']['CustomHtmlFormConfiguration']['PLURALNAME']                         = 'Formular Konfiguration';
$lang['de_DE']['CustomHtmlFormConfiguration']['SINGULARNAME']                       = 'Formular Konfiguration';

$lang['de_DE']['CustomHtmlFormConfigurationAdmin']['TAB_SPAMCHECK'] = 'Captcha';

$lang['de_DE']['CustomHtmlFormErrorMessages']['CHECK_FIELDS'] = 'Bitte pr&uuml;fen Sie Ihre Eingaben in folgenden Feldern:';

$lang['de_DE']['CustomHtmlFormField']['PtCaptchaImageField_Title'] = 'Bitte diesen Captcha-Code im folgenden Feld eingeben:';
$lang['de_DE']['CustomHtmlFormField']['PtCaptchaInputField_Title'] = 'Captcha-Code:';

$lang['de_DE']['CustomHtmlFormAdmin']['PLURALNAME']                             = 'Formulare';
$lang['de_DE']['CustomHtmlFormAdmin']['SINGULARNAME']                           = 'Formulare';
$lang['de_DE']['CustomHtmlFormAdmin']['MENUTITLE']                              = 'Formulare';