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
class PtCaptchaImageField extends SpamProtectorField {

    protected $cachedField = null;
    protected $formIdentifier;
    protected $temp_dir;
    protected $width;
    protected $height;
    protected $jpg_quality;
    protected $font;
    protected $nr_of_chars;
    protected $modulePath = '';

    /**
     * Setzt die Defaultwerte fuer das Feld.
     *
     * @param string $name
     * @param string $title
     * @param mixed $value
     * @param Form $form
     * @param string $rightTitle
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 10.12.2012
     */
    function __construct($name, $title = null, $value = null, $form = null, $rightTitle = null) {
        parent::__construct($name, $title, $value, $form, $rightTitle);

        $this->modulePath       = Director::baseFolder().'/customhtmlform/form_fields/pt_captcha';
        $this->temp_dir         = TEMP_FOLDER;
        $this->width            = CustomHtmlFormConfiguration::SpamCheck_width();
        $this->height           = CustomHtmlFormConfiguration::SpamCheck_height();
        $this->jpg_quality      = CustomHtmlFormConfiguration::SpamCheck_jpgQuality();
        $this->nr_of_chars      = CustomHtmlFormConfiguration::SpamCheck_numberOfCharsInCaptcha();
        $this->font             = $this->modulePath.'/fonts/Aller_Rg.ttf';
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
            $imagePath          = Director::makeRelative($this->temp_dir).'/'.'cap_'.$picture.'.jpg';
            $this->cachedField  = '
                <img src="'.$imagePath.'" width="'.$this->width.'" height="'.$this->height.'" alt="" />
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
     * Validate by submitting to external service
     *
     * @param Validator $validator
     *
     * @return boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 10.12.2012
     */
    public function validate($validator) {
        $checkValue     = $_REQUEST[$this->name.'Field'];
        $temp_dir       = TEMP_FOLDER;

        $fh     = fopen($temp_dir.'/'.'cap_'.$this->formIdentifier.'.txt', "r");
        $hash   = fgets($fh);
        $hash2  = md5(strtolower($checkValue));

        if ($hash2 == $hash) {
            return true;
        } else {
            $validator->validationError(
                $this->name,
                _t(
                    'Form.CAPTCHAFIELDNOMATCH'
                ),
                "validation",
                false
            );

            return false;
        }
    }

    /**
     * Generates Image file for captcha
     *
     * @param string $location
     * @param string $char_seq
     *
     * @return boolean true
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 10.12.2012
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
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 10.12.2012
     */
    function getPic($num_chars=8) {
        // define characters of which the captcha can consist
        $alphabet = array(
            'A','B','C','D','E','F','G','H','I','J','K','L','M',
            'N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
            '1','2','3','4','5','6','7','8','9','0' );

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
        if ($this->generateImage($this->temp_dir.'/'.'cap_'.$captchaIdentifier.'.jpg', $captcha_str)) {

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