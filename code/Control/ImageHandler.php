<?php

namespace CustomHtmlForm\Control;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;

/**
 * Delivers images from the silverstripe-cache directory.
 *
 * In your image src tag enter the following pattern:
 * <img src="customhtmlformimage/get/{YourImageName}/{Extension}" />
 *
 * Allowed extensions are defined in self::$allowed_mime_types.
 *
 * @package CustomHtmlForm
 * @subpackage Control
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 12.10.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class ImageHandler extends Controller {

    /**
    * Allowed actions
    *
    * @var array
    */
    private static $allowed_actions = array(
        'get'
    );

    /**
    * Allowed mime types
    *
    * @var array
    */
    private static $allowed_mime_types = array(
        'png',
        'jpg',
        'jpeg',
        'gif'
    );
    
    /**
     * Returns the image for the given name
     *
     * @param HTTPRequest $request The request object
     *
     * @return string
     */
    public function get($request) {
        $error      = 0;
        $errorText  = '';
        $params     = $request->allParams();

        if (empty($params['ID'])) {
            $error = 1;
        }
        if ($error == 0 &&
            in_array($params['OtherID'], self::$allowed_mime_types)) {

            $filePath = ASSETS_PATH . '/pt-captcha/' . $params['ID'] . '.' . $params['OtherID'];

            if ($error == 0 &&
                file_exists($filePath) &&
                is_file($filePath)) {

                header('content-type: image/'.$params['OtherID']);
                echo file_get_contents($filePath);
            } else {
                $error = 3;
            }
        } else {
            $error = 2;
        }

        switch ($error) {
            case 1:
                $errorText = 'Please provide an image name';
                break;
            case 2:
                $errorText = 'Extension not allowed';
                break;
            case 3:
                $errorText = 'File not found';
                break;
            default:
                $errorText = 'Unknown error';
        }
        return $errorText;
    }
}