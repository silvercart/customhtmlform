<?php
/**
 * Stellt Methoden zur Pruefung von Formularwerten zur Verfuegung.
 *
 * @package fashionbids
 * @author Sascha Koehler
 * @copyright Pixeltricks GmbH
 */
class CheckFormData
{
    protected $value;

    public function __construct($value)
    {
        $this -> value = $value;
    }

    /**
     * Prueft, ob die Eingabe Sonderzeichen enthaelt und dieses Resultat dem
     * erwarteten Resultat entspricht.
     * 
     * @param boolean $expectedResults
     * @return array
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
     * Prueft, ob die Eingabe formal einer Email Adresse entspricht und
     * dieses Resultat dem erwarteten Resultat entspricht.
     *
     * @param boolean $expectedResult
     * @return array
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
     * Prueft, ob die Eingabe in ein Captchafield korrekt war.
     *
     * @param string $formName
     * @param string $fieldName
     * @return array
     */
    public function PtCaptchaInput($parameters) {
        $session_key    = $parameters['formName'].'_'.$parameters['fieldName'];
        $success        = false;
        $errorMessage   = '';
        $checkValue     = $this -> getValueWithoutWhitespace($this -> value);
        $temp_dir       = TEMP_FOLDER;

        $fh     = fopen($temp_dir.'/'.'cap_'.$session_key.'.txt', "r");
        $hash   = fgets($fh);
        $hash2  = md5(strtolower($checkValue));

        if ($hash2 == $hash) {
            $success = true;
        } else {
            $success        = false;
            $errorMessage   = _t('Form.CAPTCHAFIELDNOMATCH', 'Diese Eingabe war leider falsch. Versuch es erneut!');
        }

        return array(
            'success'       => $success,
            'errorMessage'  => $errorMessage
        );
    }

    /**
     * Prueft, ob ein Feld leer ist und dieses Resultat dem erwarteten Resultat
     * entspricht.
     *
     * @param boolean $expectedResult
     * @return array
     */
    public function isFilledIn($expectedResult)
    {
        $isFilledIn     = true;
        $success        = false;
        $errorMessage   = '';
        $checkValue     = $this -> getValueWithoutWhitespace($this -> value);

        if (empty($checkValue))
        {
            $isFilledIn = false;
        }

        if ($isFilledIn === $expectedResult)
        {
            $success = true;
        }

        if (!$success)
        {
            if ($isFilledIn)
            {
                $errorMessage = _t('Form.FIELD_MUST_BE_EMPTY', 'Dieses Feld muss leer sein.');
            }
            else
            {
                $errorMessage = _t('Form.FIELD_MAY_NOT_BE_EMPTY', 'Dieses Feld darf nicht leer sein.');
            }
        }

        return array(
            'success'       => $success,
            'errorMessage'  => $errorMessage
        );
    }

    /**
     * Prueft, ob der Wert eines Feldes leer ist; ob ein Fehler zurueckgegeben
     * wird, haengt davon ab, ob das als Abhaengigkeit gegebene Feld
     * ausgefuellt ist.
     *
     * @param array $parameters
     */
    public function isFilledInDependantOn($parameters)
    {
        $isFilledInCorrectly    = true;
        $checkValue             = $this -> getValueWithoutWhitespace($this -> value);

        if (is_array($parameters))
        {
            if (!isset($parameters[0]['field']) ||
                !isset($parameters[0]['hasValue']))
            {
                throw new Exception(
                    'Feld ist falsch konfiguriert fuer Pruefung "CheckFormData->isFilledInDependantOn".'
                );
            }

            if ($parameters[1][$parameters[0]['field']] == $parameters[0]['hasValue'])
            {
                if (empty($checkValue))
                {
                    $isFilledInCorrectly = false;
                }
            }
        }
        else
        {
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
     * Prueft, ob die Laenge des Wertes der angegebenen Mindestlaenge
     * entspricht. Whitespaces am Anfang und Ende des Wertes werden fuer den
     * Vergleich entfernt.
     *
     * @param int $minLength
     * @return array
     */
    public function hasMinLength($minLength)
    {
        $hasMinLength   = true;
        $checkValue     = trim($this -> value);

        if (strlen($checkValue) > 0 &&
            strlen($checkValue) < $minLength)
        {
            $hasMinLength = false;
        }

        return array(
            'success'       => $hasMinLength,
            'errorMessage'  => sprintf(_t('Form.MIN_CHARS', 'Bitte geben Sie mindestens %s Zeichen ein.'), $minLength)
        );
    }

    /**
     * Prueft, ob die Laenge des Wertes der angegebenen Laenge
     * entspricht. Whitespaces am Anfang und Ende des Wertes werden fuer den
     * Vergleich entfernt.
     *
     * @param int $length
     * @return array
     */
    public function hasLength($length)
    {
        $hasLength  = true;
        $checkValue = trim($this -> value);

        if (strLen($checkValue) > 0 &&
            strlen($checkValue) !== $length)
        {
            $hasLength = false;
        }

        return array(
            'success'       => $hasLength,
            'errorMessage'  => sprintf(_t('Form.FIED_REQUIRES_NR_OF_CHARS', 'Dieses Feld erfordert %s Zeichen.'), $length)
        );
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
    public function mustEqual($parameters)
    {
        $isEqual = true;

        if ($this -> value !== $parameters['value'])
        {
            $isEqual = false;
        }

        return array(
            'success'       => $isEqual,
            'errorMessage'  => sprintf(_t('Form.REQUIRES_SAME_VALUE_AS_IN_FIELD', 'Bitte geben Sie den gleichen Wert ein wie im Feld "%s".'), $parameters['fieldName'])
        );
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
    public function mustNotEqual($parameters)
    {
        $isNotEqual = true;

        if ($this -> value == $parameters['value'])
        {
            $isNotEqual = false;
        }

        return array(
            'success'       => $isNotEqual,
            'errorMessage'  => sprintf(_t('Form.REQUIRES_OTHER_VALUE_AS_IN_FIELD', 'Dieses Feld darf nicht den gleichen Wert wie das Feld "%s" haben.'), $parameters['fieldName'])
        );
    }

    /**
     * Prueft, ob ein Feld ausschliesslich aus Zahlen besteht.
     *
     * @param boolean $expectedResult
     * @return array
     */
    public function isNumbersOnly($expectedResult)
    {
        $consistsOfNumbersOnly  = true;
        $success                = false;

        $checkValue = preg_replace(
            '/[0-9]*/',
            '',
            $this -> value
        );

        if (strlen($checkValue) > 0)
        {
            $consistsOfNumbersOnly = false;
        }

        if ($consistsOfNumbersOnly === $expectedResult)
        {
            $success = true;
        }

        return array(
            'success'       => $success,
            'errorMessage'  => _t('Form.NUMBERS_ONLY', 'Dieses Feld darf nur Zahlen enthalten.')
        );
    }

    /**
     * Prueft, ob der Wert eines Feldes einer Waehrungsangabe entspricht.
     *
     * @param mixed $expectedResult
     * @return array
     */
    public function isCurrency($expectedResult)
    {
        $success = $expectedResult;

        if (!empty($this->value))
        {
            $nrOfMatches = preg_match('/^[\d]*[,]?[^\D]*$/', $this->value, $matches);

            if ($nrOfMatches === 0)
            {
                $success = false;
            }
        }

        return array(
            'success'       => $success,
            'errorMessage'  => _t('Form.CURRENCY_ONLY', 'In dieses Feld muss eine Währungsangabe (z.B. "1499,95") eingetragen werden.')
        );
    }

    /**
     * Prueft, ob der Wert eines Feldes einer Datumsangabe entspricht.
     *
     * @param mixed $expectedResult
     * @return array
     */
    public function isDate($expectedResult)
    {
        $success = $expectedResult;

        if (!empty($this->value))
        {
            $nrOfMatches = preg_match('/[\d]{2}[\.]{1}[\d]{2}[.]{1}[\d]{4}/', $this->value);

            if ($nrOfMatches === 0)
            {
                $success = false;
            }
        }

        return array(
            'success'       => $success,
            'errorMessage'  => _t('Form.DATE_ONLY', 'In dieses Feld muss ein Datum im Format "tt.mm.jjjj" eingetragen werden.')
        );
    }

    /**
     * Entfernt alle Whitespaces aus dem uebergebenen Wert und gibt das
     * Ergebnis zurueck.
     *
     * @param string $value
     * @return string
     */
    private function getValueWithoutWhitespace($value)
    {
        return preg_replace('/[\s]*/', '', $value);
    }
}