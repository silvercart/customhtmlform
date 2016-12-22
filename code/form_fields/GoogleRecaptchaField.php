<?php
/**
 * Copyright 2016 pixeltricks GmbH
 *
 * This file is part of CustomHtmlForm.
 *
 * @package CustomHtmlForm
 * @subpackage FormFields
 */

/**
 * A Google reCAPTCHA field
 *
 * @package CustomHtmlForm
 * @subpackage FormFields
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 21.12.2016
 * @copyright 2016 pixeltricks GmbH
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class GoogleRecaptchaField extends FormField {
    
    /**
     * Private reCAPTCHA secret.
     *
     * @var string
     */
    private static $recaptcha_secret = '';
    
    /**
     * reCAPTCHA site key.
     *
     * @var string
     */
    private static $recaptcha_site_key = '';
    
    /**
     * Returns the private reCAPTCHA secret.
     * 
     * @return string
     */
    public static function get_recaptcha_secret() {
        return self::$recaptcha_secret;
    }

    /**
     * Sets the private reCAPTCHA secret.
     * 
     * @param string $recaptcha_secret Private reCAPTCHA secret
     * 
     * @return void
     */
    public static function set_recaptcha_secret($recaptcha_secret) {
        self::$recaptcha_secret = $recaptcha_secret;
    }
    
    /**
     * Returns the private reCAPTCHA secret.
     * 
     * @return string
     */
    public function getRecaptchaSecret() {
        return self::get_recaptcha_secret();
    }
    
    /**
     * Returns the reCAPTCHA site_key.
     * 
     * @return string
     */
    public static function get_recaptcha_site_key() {
        return self::$recaptcha_site_key;
    }

    /**
     * Sets the reCAPTCHA site_key.
     * 
     * @param string $recaptcha_site_key Private reCAPTCHA site_key
     * 
     * @return void
     */
    public static function set_recaptcha_site_key($recaptcha_site_key) {
        self::$recaptcha_site_key = $recaptcha_site_key;
    }
    
    /**
     * Returns the reCAPTCHA site_key.
     * 
     * @return string
     */
    public function getRecaptchaSiteKey() {
        return self::get_recaptcha_site_key();
    }

    /**
     * Validate by submitting to external service
     *
     * @param Validator $validator Validator
     *
     * @return boolean
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 21.12.2016
     */
    public function validate($validator) {
        $valid              = false;
        $gRecaptchaResponse = $_REQUEST['g-recaptcha-response'];
        $remoteIp           = $_SERVER['REMOTE_ADDR'];
        
        $recaptcha = new \ReCaptcha\ReCaptcha(self::get_recaptcha_secret());
        $resp = $recaptcha->verify($gRecaptchaResponse, $remoteIp);
        if ($resp->isSuccess()) {
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
    
}