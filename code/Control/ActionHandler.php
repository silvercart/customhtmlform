<?php

namespace CustomHtmlForm\Control;

use SilverStripe\Control\Controller;
use SilverStripe\Dev\Deprecation;

/**
 * Central handler for form actions.
 * You can add foreign actions with an extension.
 *
 * @package CustomHtmlForm
 * @subpackage Control
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 12.10.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class ActionHandler extends Controller {
    
    /**
     * Adds an action handler
     * 
     * @param string $classname Class name of action handler to add
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.10.2017
     */
    public static function addHandler($classname) {
        Deprecation::notice(
            '4.0',
            'CustomHtmlForm\Control\ActionHandler::addHandler() is deprecated. Use CustomHtmlForm\Control\ActionHandler::add_handler() instead.'
        );
        self::add_handler($classname);
    }
    
    /**
     * Adds an action handler
     * 
     * @param string $classname Class name of action handler to add
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.10.2017
     */
    public static function add_handler($classname) {
        ActionHandler::add_extension($classname);
    }
    
}