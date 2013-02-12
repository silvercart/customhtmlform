<?php
/**
 * Copyright 2013 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * SilverCart is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SilverCart is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with SilverCart.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package CustomHtmlForm
 * @subpackage FormFields
 */

/**
 * A ptCaptcha field (uses the jax captcha class)
 *
 * @package CustomHtmlForm
 * @subpackage FormFields
 * @copyright pixeltricks GmbH
 * @author Sascha Koehler <skoehler@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
 * @since 12.02.2013
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class PtCaptchaImageField extends TextField {
    
    /**
     * The captcha string
     *
     * @var string
     */
    protected $captchaStr = null;
    
    /**
     * Font of the captcha
     *
     * @var int
     */
    protected $font;
    
    /**
     * Identifier
     *
     * @var string 
     */
    protected $formIdentifier;
    
    /**
     * height of the captcha
     *
     * @var int
     */
    protected $height;
    
    /**
     * path to the image
     *
     * @var string
     */
    protected $imagePath = null;
    
    /**
     * Quality of the captcha
     *
     * @var int
     */
    protected $jpgQuality;
    
    /**
     * number of chars
     *
     * @var int
     */
    protected $nrOfChars;
    
    /**
     * Holds the pics value
     *
     * @var string
     */
    protected $pic = null;
    
    /**
     * Temporary dir
     *
     * @var string
     */
    protected $temp_dir;
    
    /**
     * Width of the captcha
     *
     * @var int
     */
    protected $width;

    /**
     * Initializes the field.
     *
     * @param string $name      Name of the field
     * @param string $title     Title of the field
     * @param mixed  $value     Value of the field
     * @param int    $maxLength Max input length
     * @param Form   $form      Form to relate field with
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.02.2013
     */
    public function __construct($name, $title = null, $value = '', $maxLength = null, $form = null) {
        parent::__construct($name, $title, $value, $maxLength, $form);

        $this->setTempDir(          ASSETS_PATH . 'pt-captcha');
        $this->setWidth(            CustomHtmlFormConfiguration::SpamCheck_width());
        $this->setHeight(           CustomHtmlFormConfiguration::SpamCheck_height());
        $this->setJpgQuality(       CustomHtmlFormConfiguration::SpamCheck_jpgQuality());
        $this->setNrOfChars(        CustomHtmlFormConfiguration::SpamCheck_numberOfCharsInCaptcha());
        $this->setFont(             Director::baseFolder() . '/customhtmlform/fonts/Aller_Rg.ttf');
        $this->setFormIdentifier(   $form->getName() . $this->getName());
    }

    /**
     * Validate by submitting to external service
     *
     * @param Validator $validator Validator
     *
     * @return boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.02.2013
     */
    public function validate($validator) {
        $valid      = false;
        $checkValue = $_REQUEST[$this->getName() . 'Field'];
        $fh         = fopen($this->getTempDir() . '/' . 'cap_' . $this->getFormIdentifier() . '.txt', "r");
        $hash       = fgets($fh);
        $hash2      = md5(strtolower($checkValue));

        if ($hash2 == $hash) {
            $valid = true;
        } else {
            $validator->validationError(
                $this->getName(),
                _t('Form.CAPTCHAFIELDNOMATCH'),
                "validation",
                false
            );
        }
        return $valid;
    }

    /**
     * Generates Image file for captcha
     *
     * @param string $location Location
     * @param string $char_seq Sequence of characters
     *
     * @return boolean true
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.02.2013
     */
    protected function generateImage($location, $char_seq) {
        $num_chars  = strlen($char_seq);
        $img        = imagecreatetruecolor($this->getWidth(), $this->getHeight());
        
        imagealphablending($img, 1);
        imagecolortransparent( $img );

        // generate background of randomly built ellipses
        for ($i=1; $i<=200; $i++) {
            $r = round( rand( 0, 100 ) );
            $g = round( rand( 0, 100 ) );
            $b = round( rand( 0, 100 ) );
            $color = imagecolorallocate( $img, $r, $g, $b );
            imagefilledellipse(
                    $img,
                    round(rand(0, $this->getWidth())),
                    round(rand(0, $this->getHeight())),
                    round(rand(0, $this->getWidth() / 16)),
                    round(rand(0, $this->getHeight() / 4)),
                    $color
            );
        }

        $max_font_size  = round($this->getWidth() / $num_chars);;
        $start_x        = round(0.5 * $max_font_size);
        $max_x_ofs      = round($max_font_size * 0.9);

        // set each letter with random angle, size and color
        for ($i=0; $i <= $num_chars; $i++) {
            $r      = round( rand( 127, 255 ) );
            $g      = round( rand( 127, 255 ) );
            $b      = round( rand( 127, 255 ) );
            $y_pos  = ($this->getHeight() / 2) + round( rand( 5, 20 ) );

            $fontsize   = round( rand( 18, $max_font_size) );
            $color      = imagecolorallocate( $img, $r, $g, $b);
            $presign    = round( rand( 0, 1 ) );
            $angle      = round( rand( 0, 25 ) );

            if ($presign == true) {
                $angle = -1 * $angle;
            }

            $image_font = $this->getFont();
            ImageTTFText(
                    $img,
                    $fontsize,
                    $angle,
                    $start_x + $i * $max_x_ofs,
                    $y_pos,
                    $color,
                    $image_font,
                    substr($char_seq, $i, 1)
            );
        }

        // create image file
        imagejpeg( $img, $location, $this->getJpgQuality() );
        flush();
        imagedestroy( $img );

        return true;
    }

    /**
     * Returns name of the new generated captcha image file
     *
     * @param int $charCount The number of chars to generate
     *
     * @return boolean|string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.02.2013
     */
    public function getPic($charCount = 8) {
        if (is_null($this->pic)) {
            $this->pic          = false;
            $captchaStr         = $this->getCaptchaString($charCount);
            $captchaIdentifier  = md5(strtolower($captchaStr));

            // generate a picture file that displays the random string
            if ($this->generateImage($this->getTempDir() . '/' . 'cap_' . $captchaIdentifier . '.jpg', $captchaStr)) {
                if (!is_array($_SESSION)) {
                    $_SESSION = array();
                }
                if (!array_key_exists('CustomHtmlForm', $_SESSION)) {
                    $_SESSION['CustomHtmlForm'] = array();
                }
                if (!array_key_exists('SpamCheck', $_SESSION['CustomHtmlForm'])) {
                    $_SESSION['CustomHtmlForm']['SpamCheck'] = array();
                }

                $_SESSION['CustomHtmlForm']['SpamCheck'][$this->getFormIdentifier()] = $captchaIdentifier;

                $this->pic = $captchaIdentifier;
            }
        }
        return $this->pic;
    }
    
    /**
     * Returns the captcha string
     * 
     * @param int $charCount The number of chars to generate
     * 
     * @return string
     */
    public function getCaptchaString($charCount = 8) {
        if (is_null($this->captchaStr)) {
            $alphabet = array(
                'A','B','C','D','E','F','G','H','J','K','M',
                'N','P','Q','R','S','T','U','V','W','X','Y','Z',
                '2','3','4','5','6','7','8','9'
            );
            $max        = sizeof($alphabet);
            $this->captchaStr = '';
            for ($i = 1; $i <= $charCount; $i++) {
                $this->captchaStr .= $alphabet[rand(1, $max) - 1];
            }
        }
        return $this->captchaStr;
    }

    /**
     * Returns the path to the captcha image
     * 
     * @return string
     */
    public function getImagePath() {
        if (is_null($this->imagePath)) {
            $picture            = $this->getPic($this->getNrOfChars());
            $this->imagePath    = Director::makeRelative($this->getTempDir()).'/'.'cap_'.$picture.'.jpg';
        }
        return $this->imagePath;
    }

    /**
     * Returns the font to use
     * 
     * @return string
     */
    public function getFont() {
        return $this->font;
    }

    /**
     * Sets the font to use
     * 
     * @param string $font Font to use
     * 
     * @return void
     */
    public function setFont($font) {
        $this->font = $font;
    }

    /**
     * Returns the form identifier
     * 
     * @return string
     */
    public function getFormIdentifier() {
        return $this->formIdentifier;
    }

    /**
     * Sets the form identifier
     * 
     * @param string $formIdentifier Form identifier
     * 
     * @return void
     */
    public function setFormIdentifier($formIdentifier) {
        $this->formIdentifier = $formIdentifier;
    }

    /**
     * Returns the height of the captcha image
     * 
     * @return int
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * Sets the height of the captcha image
     * 
     * @param int $height Height of the captcha image
     * 
     * @return void
     */
    public function setHeight($height) {
        $this->height = $height;
    }

    /**
     * Returns the JPEG quality
     * 
     * @return int
     */
    public function getJpgQuality() {
        return $this->jpgQuality;
    }

    /**
     * Sets the JPEG quality
     * 
     * @param int $jpgQuality JPEG quality
     * 
     * @return void
     */
    public function setJpgQuality($jpgQuality) {
        $this->jpgQuality = $jpgQuality;
    }

    /**
     * Returns the number of chars to use
     * 
     * @return int
     */
    public function getNrOfChars() {
        return $this->nrOfChars;
    }

    /**
     * Sets the number of chars to use
     * 
     * @param int $nrOfChars Number of chars to use
     * 
     * @return void
     */
    public function setNrOfChars($nrOfChars) {
        $this->nrOfChars = $nrOfChars;
    }

    /**
     * Returns the temporary dir
     * 
     * @return string
     */
    public function getTempDir() {
        return $this->tempDir;
    }

    /**
     * Sets the temporary dir
     * 
     * @param string $tempDir Temporary dir
     * 
     * @return void
     */
    public function setTempDir($tempDir) {
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $this->tempDir = $tempDir;
    }

    /**
     * Returns the width of the captcha image
     * 
     * @return int
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * Sets the width of the captcha image
     * 
     * @param int $width Width
     * 
     * @return void
     */
    public function setWidth($width) {
        $this->width = $width;
    }

}