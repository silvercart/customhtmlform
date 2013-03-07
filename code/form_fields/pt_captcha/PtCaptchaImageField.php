<?php
/**
 * Copyright 2012 pixeltricks GmbH
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
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @since 07.12.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class PtCaptchaImageField extends TextField {

    /**
     * Request chached field markup
     *
     * @var string
     */
    protected $cachedField = null;
    
    /**
     * Identifier
     *
     * @var string 
     */
    protected $formIdentifier;
    
    /**
     * Width of the captcha
     *
     * @var int
     */
    protected $width;
    
    /**
     * height of the captcha
     *
     * @var int
     */
    protected $height;
    
    /**
     * Quality of the captcha
     *
     * @var int
     */
    protected $jpg_quality;
    
    /**
     * Font of the captcha
     *
     * @var int
     */
    protected $font;
    
    /**
     * number of chars
     *
     * @var int
     */
    protected $nr_of_chars;

    /**
     * Initializes the field.
     *
     * @param string $name       Name of the field
     * @param string $title      Title of the field
     * @param mixed  $value      Value of the field
     * @param Form   $form       Form to relate field with
     * @param string $rightTitle Right title (additional description) of the field
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 06.02.2013
     */
    public function __construct($name, $title = null, $value = null, $form = null, $rightTitle = null) {
        parent::__construct($name, $title, $value, $form, $rightTitle);

        $this->width            = CustomHtmlFormConfiguration::SpamCheck_width();
        $this->height           = CustomHtmlFormConfiguration::SpamCheck_height();
        $this->jpg_quality      = CustomHtmlFormConfiguration::SpamCheck_jpgQuality();
        $this->nr_of_chars      = CustomHtmlFormConfiguration::SpamCheck_numberOfCharsInCaptcha();
        $this->font             = Director::baseFolder() . '/customhtmlform/fonts/Aller_Rg.ttf';
        $this->formIdentifier   = $form->name.$this->Name();
    }

    /**
     * Creates the image and returns the image HTML tag as string.
     *
     * @return string HTML
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 10.12.2012
     */
    public function Field() {
        if ($this->cachedField === null) {
            $picture            = $this->getPic($this->nr_of_chars);
            $this->cachedField  = '
                <img src="'.Director::baseURL().'/customhtmlformimage/get/cap_'.$picture.'/jpg" width="'.$this->width.'" height="'.$this->height.'" alt="" />
            ';
        }

        return $this->cachedField;
    }

    /**
     * Return the field HTML code
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 10.12.2012
     */
    public function FieldHolder() {
        $Title = $this->XML_val('Title');
        $Message = $this->XML_val('Message');
        $MessageType = $this->XML_val('MessageType');
        $Type = $this->XML_val('Type');
        $extraClass = $this->XML_val('extraClass');
        $Name = $this->XML_val('Name');
        $Field = $this->XML_val('Field');

        $messageBlock = (!empty($Message)) ? "<span class=\"message $MessageType\">$Message</span>" : "";

        return <<<HTML

        <div>
            {$Title}:<br />
            <input type="text" name="{$Name}Field" />
        </div>

        <div class="captchaField" style="margin-top: 2px;">
            {$Field}{$messageBlock}
        </div>
HTML;
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
     * @since 06.02.2013
     */
    protected function generateImage($location, $char_seq) {
        $num_chars = strlen($char_seq);

        $img = imagecreatetruecolor( $this->width, $this->height );
        imagealphablending($img, 1);
        imagecolortransparent( $img );

        // generate background of randomly built ellipses
        for ($i=1; $i<=200; $i++) {
            $r = round( rand( 0, 100 ) );
            $g = round( rand( 0, 100 ) );
            $b = round( rand( 0, 100 ) );
            $color = imagecolorallocate( $img, $r, $g, $b );
            imagefilledellipse( $img,round(rand(0,$this->width)), round(rand(0,$this->height)), round(rand(0,$this->width/16)), round(rand(0,$this->height/4)), $color );
        }

        $start_x        = round($this->width / $num_chars);
        $max_font_size  = $start_x;
        $start_x        = round(0.5*$start_x);
        $max_x_ofs      = round($max_font_size*0.9);

        // set each letter with random angle, size and color
        for ($i=0;$i<=$num_chars;$i++) {
            $r      = round( rand( 127, 255 ) );
            $g      = round( rand( 127, 255 ) );
            $b      = round( rand( 127, 255 ) );
            $y_pos  = ($this->height/2)+round( rand( 5, 20 ) );

            $fontsize   = round( rand( 18, $max_font_size) );
            $color      = imagecolorallocate( $img, $r, $g, $b);
            $presign    = round( rand( 0, 1 ) );
            $angle      = round( rand( 0, 25 ) );

            if ($presign==true) {
                $angle = -1*$angle;
            }

            $image_font = $this->font;
            ImageTTFText( $img, $fontsize, $angle, $start_x+$i*$max_x_ofs, $y_pos, $color, $image_font, substr($char_seq,$i,1) );
        }

        // create image file
        imagejpeg( $img, $location, $this->jpg_quality );
        flush();
        imagedestroy( $img );

        return true;
    }

    /**
     * Returns name of the new generated captcha image file
     *
     * @param int $num_chars The number of chars to generate
     *
     * @return mixed boolean false|string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 06.02.2013
     */
    public function getPic($num_chars=8) {
        // define characters of which the captcha can consist
        $alphabet = array(
            'A','B','C','D','E','F','G','H','J','K','M',
            'N','P','Q','R','S','T','U','V','W','X','Y','Z',
            '2','3','4','5','6','7','8','9' );

        $max = sizeof($alphabet);

        // generate random string
        $captcha_str = '';

        for ($i=1;$i<=$num_chars;$i++) { // from 1..$num_chars
            // choose randomly a character from alphabet and append it to string
            $chosen = rand( 1, $max );
            $captcha_str .= $alphabet[$chosen-1];
        }

        $captchaIdentifier = md5(strtolower($captcha_str));

        // generate a picture file that displays the random string
        if ($this->generateImage(TEMP_FOLDER.'/'.'cap_'.$captchaIdentifier.'.jpg', $captcha_str)) {

            if (!is_array($_SESSION)) {
                $_SESSION = array();
            }
            if (!array_key_exists('CustomHtmlForm', $_SESSION)) {
                $_SESSION['CustomHtmlForm'] = array();
            }
            if (!array_key_exists('SpamCheck', $_SESSION['CustomHtmlForm'])) {
                $_SESSION['CustomHtmlForm']['SpamCheck'] = array();
            }

            $_SESSION['CustomHtmlForm']['SpamCheck'][$this->formIdentifier] = $captchaIdentifier;

            return $captchaIdentifier;
        } else {
            return false;
        }
    }
}
