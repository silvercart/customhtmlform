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
* @subpackage Controller
*/

/**
* Delivers images from the silverstripe-cache directory.
*
* In your image src tag enter the following pattern:
* <img src="customhtmlformimage/get/{YourImageName}/{Extension}" />
*
* Allowed extensions are defined in self::$allowed_mime_types.
*
* @package CustomHtmlForm
* @subpackage Controller
* @copyright pixeltricks GmbH
* @author Sascha Koehler <skoehler@pixeltricks.de>
* @since 2013-03-07
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/
class CustomHtmlFormImageHandler extends Controller {

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
     * @param SS_HTTPREQUEST $request The request object
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2013-03-07
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
