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
 * The input field handler for ptCaptcha.
 *
 * @package CustomHtmlForm
 * @subpackage FormFields
 * @copyright pixeltricks GmbH
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @since 07.12.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class PtCaptchaInputField extends TextField {

    /**
     * check hash of password against hash of searched characters
     *
     * @param string $char_seq The code to check
     *
     * @return boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 17.12.2012
     */
    protected function verify($char_seq) {
        $fh     = fopen( $this->temp_dir.'/'.'cap_'.$this->session_key.'.txt', "r" );
        $hash   = fgets( $fh );
        $hash2  = md5(strtolower($char_seq));

        if ($hash2 == $hash) {
            return true;
        } else {
            return false;
        }
    }
}