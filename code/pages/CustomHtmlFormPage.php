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
 */

/**
 * Provides additional methods for Page.php used by the CustomHtmlForms module
 *
 * @package CustomHtmlForm
 * @author Sascha Koehler <skoehler@pixeltricks.de>,
 *         Sebastian Diel <sdiel@pixeltricks.de>
 * @since 04.07.2013
 * @copyright 2013 pixeltricks GmbH
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class CustomHtmlFormPage_Controller extends DataExtension {
    
    /**
     * Set this to false if you do not want to include the meta-content-language-tag
     * 
     * @var bool 
     */
    public static $include_meta_content_language = true;

    /**
     * defines allowed methods
     *
     * defines the event to be jumped after form submission
     *
     * Hier wird das zentrale Event fuer die CustomHtmlForm definiert, das
     * nach dem Absenden eines Formulars angesprungen wird.
     *
     * @var array
     */
    public static $allowed_actions = array(
        'customHtmlFormSubmit',
        'uploadifyUpload',
        'uploadifyRefresh',
        'uploadifyRemoveFile'
    );

    /**
     * Contains all JS blocks to be added to the onload-event
     *
     * @var array
     */
    protected $JavascriptOnloadSnippets = array();

    /**
     * Contains all JS blocks NOT to be added to the onload-event
     *
     * @var array
     */
    protected $JavascriptSnippets = array();

    /**
     * contains a list of registerd custom html forms
     *
     * @var array
     */
    protected $registeredCustomHtmlForms = array();
    
    /**
     * Set this to false to not include CustomHtmlForms jQuery.
     *
     * @var bool
     */
    public static $do_use_own_jquery = true;

    /**
     * Determines whether to load the jQuery dependencies or not.
     *
     * @var bool
     */
    private static $load_jquery_dependencies = true;

    /**
     * Sets whether to load the jQuery sources included in this module.
     * 
     * @param bool $do_use_own_jquery Load jQuery dependencies?
     * 
     * @return void
     */
    public static function set_do_use_own_jquery($do_use_own_jquery) {
        self::$do_use_own_jquery = $do_use_own_jquery;
    }
    
    /**
     * Returns whether to load the jQuery sources included in this module.
     * 
     * @return bool
     */
    public static function get_do_use_own_jquery() {
        return self::$do_use_own_jquery;
    }
    
    /**
     * Returns whether to load the jQuery sources included in this module.
     * Alias for self::get_load_jquery_dependencies()
     * 
     * @return bool
     */
    public static function do_use_own_jquery() {
        return self::$do_use_own_jquery;
    }

    /**
     * Sets whether to load the jQuery dependencies or not.
     * 
     * @param bool $load_jquery_dependencies Load jQuery dependencies?
     * 
     * @return void
     */
    public static function set_load_jquery_dependencies($load_jquery_dependencies) {
        self::$load_jquery_dependencies = $load_jquery_dependencies;
    }
    
    /**
     * Returns whether to load the jQuery dependencies or not.
     * 
     * @return bool
     */
    public static function get_load_jquery_dependencies() {
        return self::$load_jquery_dependencies;
    }
    
    /**
     * Returns whether to load the jQuery dependencies or not.
     * Alias for self::get_load_jquery_dependencies()
     * 
     * @return bool
     */
    public static function load_jquery_dependencies() {
        return self::$load_jquery_dependencies;
    }

    /**
     * adds a snippet to the list of JS onload events
     * Fuegt ein Snippet in die Liste der Javascript Onload-Events ein.
     *
     * @param string $snippet text block with JS statements
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 25.10.2010
     */
    public function addJavascriptOnloadSnippet($snippet) {
        $this->JavascriptOnloadSnippets[] = $snippet;
    }

    /**
     * adds a snippet to the JS list to be added in the documents header
     *
     * @param string $snippet Textblock mit Javascript-Anweisungen
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 25.10.2010
     */
    public function addJavascriptSnippet($snippet) {
        $this->JavascriptSnippets[] = $snippet;
    }

    /**
     * Clears all javascript snippets.
     * 
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 12.02.2012
     */
    public function clearJavascriptSnippets() {
        $this->JavascriptSnippets = array();
    }

    /**
     * Clears all javascript onload snippets.
     * 
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 12.02.2012
     */
    public function clearJavascriptOnloadSnippets() {
        $this->JavascriptOnloadSnippets = array();
    }

    /**
     * registers a form object
     *
     * @param string         $formIdentifier unique form name which can be called via template
     * @param CustomHtmlForm $formObj        The form object with field definitions and preocessing methods
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 25.10.2010
     */
    public function registerCustomHtmlForm($formIdentifier, CustomHtmlForm $formObj) {
        $this->registeredCustomHtmlForms[$formIdentifier] = $formObj;
    }

    /**
     * unregisters a form object
     *
     * @param string $formIdentifier unique form name which can be called via template
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 12.02.2012
     */
    public function unRegisterCustomHtmlForm($formIdentifier) {
        if (array_key_exists($formIdentifier, $this->registeredCustomHtmlForms)) {
            unset($this->registeredCustomHtmlForms[$formIdentifier]);
        }
    }

    /**
     * Returns the CustomHtmlForm object with the given identifier; if it's not
     * found a boolean false is returned.
     *
     * @param string $formIdentifier The identifier of the form
     *
     * @return mixed CustomHtmlForm|bool false
     */
    public function getRegisteredCustomHtmlForm($formIdentifier) {
        $formObj = false;

        if (isset($this->registeredCustomHtmlForms[$formIdentifier])) {
            $formObj = $this->registeredCustomHtmlForms[$formIdentifier];
        }

        return $formObj;
    }
    
    /**
     * Returns all registered CustomHtmlForms.
     *
     * @return array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 15.10.2011
     */
    public function getRegisteredCustomHtmlForms() {
        return $this->registeredCustomHtmlForms;
    }
    
    /**
     * Sets the registered CustomHtmlForms.
     *
     * @param array $forms An array containing CustomHtmlForms
     * 
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 15.10.2011
     */
    public function setRegisteredCustomHtmlForms($forms) {
        $this->registeredCustomHtmlForms = $forms;
    }

    /**
     * returns HTML markup for the requested form
     *
     * @param string $formIdentifier   unique form name which can be called via template
     * @param Object $renderWithObject object array; in those objects context the forms shall be created
     *
     * @return CustomHtmlForm
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 07.05.2015
     * 
     */
    public function InsertCustomHtmlForm($formIdentifier, $renderWithObject = null) {
        if (!isset($this->registeredCustomHtmlForms[$formIdentifier])) {
            if (Director::isDev()) {
                throw new Exception(
                    sprintf(
                        'The requested CustomHtmlForm "%s" is not registered.',
                        $formIdentifier
                    )
                );
            }
            return;
        }
        $outputForm = $this->registeredCustomHtmlForms[$formIdentifier]->getCachedFormOutput();
        if (empty($outputForm)) {
            // Inject controller
            $customFields = array(
                'Controller' => $this->owner
            );

            if ($renderWithObject !== null) {
                if (is_array($renderWithObject)) {
                    foreach ($renderWithObject as $renderWithSingleObject) {
                        if ($renderWithSingleObject instanceof DataObject) {
                            if (isset($customFields)) {
                                $customFields = array_merge($customFields, $renderWithSingleObject->toMap());
                            } else {
                                $customFields = $renderWithSingleObject->toMap();
                            }
                            unset($customFields['ClassName']);
                            unset($customFields['RecordClassName']);
                        }
                    }
                } else {
                    if ($renderWithObject instanceof DataObject) {
                        $customFields = $renderWithObject->toMap();
                        unset($customFields['ClassName']);
                        unset($customFields['RecordClassName']);
                    }
                }
            }

            $outputForm = $this->registeredCustomHtmlForms[$formIdentifier]->customise($customFields)->renderWith(
                array(
                    $this->registeredCustomHtmlForms[$formIdentifier]->class,
                )
            );
            $this->registeredCustomHtmlForms[$formIdentifier]->setCachedFormOutput($outputForm);
        }

        return $outputForm;
    }

    /**
     * load some requirements first
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 23.12.2016
     */
    public function onBeforeInit() {
        if (!$this->owner instanceof Security ||
             $this->owner->urlParams['Action'] != 'ping') {
            Requirements::block('sapphire/thirdparty/jquery/jquery.js');
            if (!class_exists('RequirementsEngine')) {
                if (self::$do_use_own_jquery) {
                    Requirements::javascript('customhtmlform/script/jquery.js');
                }
            }
        }

        $this->owner->isFrontendPage = true;
    }

    /**
     * The onload and other javascript instructions are generated here.
     *
     * If you want a onload snippet to be loaded at the very end of the
     * definition you have to define it as array and provide the string
     * 'loadInTheEnd' as second parameter:
     *
     * $controller->addJavascriptOnloadSnippet(
     *     'var yourJavascriptSnippet;',
     *     'loadInTheEnd'
     * );
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 23.12.2016
     */
    public function onAfterInit() {
        if (!$this->owner instanceof Security ||
            $this->owner->urlParams['Action'] != 'ping') {
            if (self::$include_meta_content_language) {
                Requirements::insertHeadTags('<meta http-equiv="Content-language" content="' . i18n::get_locale() . '" />', 'CustomHtmlFormContentLanguageTag');
            }
            Requirements::add_i18n_javascript('customhtmlform/javascript/lang');

            if (!class_exists('RequirementsEngine')) {
                if (self::load_jquery_dependencies()) {
                    Requirements::javascript('customhtmlform/script/jquery.scrollTo.min.js');
                    Requirements::javascript('customhtmlform/script/jquery.pixeltricks.forms.checkFormData.js');
                    Requirements::javascript('customhtmlform/script/jquery.pixeltricks.forms.events.js');
                    Requirements::javascript('customhtmlform/script/jquery.pixeltricks.forms.validator.js');
                }
                Requirements::javascript(SAPPHIRE_DIR . "/javascript/i18n.js");
                Requirements::add_i18n_javascript('customhtmlform/javascript/lang');
            }
        }
        $onLoadSnippetStr           = '';
        $onLoadInTheEndSnippetStr   = '';
        $snippetStr                 = '';

        foreach ($this->JavascriptOnloadSnippets as $snippet) {
            if (is_array($snippet)) {
                if (isset($snippet[1]) &&
                    $snippet[1] == 'loadInTheEnd') {

                    $onLoadInTheEndSnippetStr .= $snippet[0];
                } else {
                    $onLoadSnippetStr .= $snippet[0];
                }
            } else {
                $onLoadSnippetStr .= $snippet;
            }
        }

        foreach ($this->JavascriptSnippets as $snippet) {
            $snippetStr .= $snippet;
        }

        if (!empty($snippetStr) ||
            !empty($onLoadSnippetStr)) {

            Requirements::customScript('
'.$snippetStr.'

(function($) {jQuery(document).ready(function() {
    '.$onLoadSnippetStr.'
    '.$onLoadInTheEndSnippetStr.'
})})(jQuery);');
        }
    }

    /**
     * processor method for all customhtmlform forms
     *
     * @param Form $form the submitting form object
     *
     * @return mixed depends on processing form method
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 25.10.2010
     */
    public function customHtmlFormSubmit($form) {
        $formName                    = $this->owner->request->postVar('CustomHtmlFormName');
        $registeredCustomHtmlFormObj = false;

        foreach ($this->registeredCustomHtmlForms as $registeredCustomHtmlForm) {
            if ($formName === $registeredCustomHtmlForm->name) {
                $registeredCustomHtmlFormObj = $registeredCustomHtmlForm;
                break;
            }

            foreach ($registeredCustomHtmlForm->registeredCustomHtmlForms as $customHtmlFormRegisteredCustomHtmlForm) {
                if ($formName === $customHtmlFormRegisteredCustomHtmlForm->name) {
                    $registeredCustomHtmlFormObj = $customHtmlFormRegisteredCustomHtmlForm;
                    break(2);
                }
            }
        }

        if ($registeredCustomHtmlFormObj instanceof CustomHtmlForm) {
            return $registeredCustomHtmlFormObj->submit($form, null);
        } else {
            if ($this->owner->request->requestVar('_REDIRECT_BACK_URL')) {
                $url = $this->owner->request->requestVar('_REDIRECT_BACK_URL');
            } elseif ($this->owner->request->getHeader('Referer')) {
                $url = $this->owner->request->getHeader('Referer');
            } else {
                $url = Director::baseURL();
            }
            
            if (substr($url, -20) == 'customHtmlFormSubmit') {
                $url = substr($url, 0, -20);
            }
            
            // absolute redirection URLs not located on this site may cause phishing
            if (Director::is_site_url($url)) {
                $this->owner->redirect($url);
            }
        }
    }

    /**
     * wrapper for action to uploadify field
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 03.11.2010
     */
    public function uploadifyUpload() {

        $fieldReference = $this->getFieldObject();

        if ($fieldReference != '') {
            $result = $fieldReference->upload();
            return $result;
        } else {
            return -1;
        }
    }

    /**
     * wrapper for action to uploadify field
     *
     * @param SS_HTTPRequest $request the request parameter
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 03.11.2010
     */
    public function uploadifyRefresh(SS_HTTPRequest $request) {
        $fieldReference = $this->getFieldObject();

        if ($fieldReference != '') {
            return $fieldReference->refresh($request);
        } else {
            return -1;
        }
    }

    /**
     * wrapper for action to uploadify field
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 03.11.2010
     */
    public function uploadifyRemoveFile() {
        $fieldReference = $this->getFieldObject();

        if ($fieldReference != '') {
            return $fieldReference->removefile();
        } else {
            return -1;
        }
    }

    /**
     * Method Description
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 03.11.2010
     */
    protected function getFieldObject() {
        $formIdentifier = 'CreateAuctionFormStep5';
        $fieldReference = '';

        foreach ($this->registeredCustomHtmlForms as $registeredFormIdentifier => $registeredCustomHtmlForm) {
            if ($formIdentifier == $registeredFormIdentifier) {
                break;
            }
        }

        if ($registeredCustomHtmlForm instanceof CustomHtmlForm) {
            foreach ($registeredCustomHtmlForm->SSformFields['fields'] as $field) {
                if ($field instanceof MultipleImageUploadField) {
                    $fieldReference = $field;
                }
            }
        }

        return $fieldReference;
    }
}
