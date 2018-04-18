<?php

namespace CustomHtmlForm\Forms;

use CustomHtmlForm\Dev\JavascriptTools;
use CustomHtmlForm\Dev\Tools;
use CustomHtmlForm\Forms\GoogleRecaptchaField;
use CustomHtmlForm\Forms\PtCaptchaImageField;
use CustomHtmlForm\Model\SiteConfigExtension;
use CustomHtmlForm\Model\StepPageController;
use CustomHtmlForm\Validation\CheckFormData;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\MoneyField;
use SilverStripe\Forms\PasswordField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Forms\TreeMultiselectField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\SecurityToken;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;
use SilverStripe\View\ViewableData;
use Translatable;

/**
 * Provide functionallity for forms with freely configurable HTML code.
 *
 * @package CustomHtmlForm
 * @subpackage Forms
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 11.10.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class CustomHtmlForm extends Form {

    /**
     * Indicator to check whether the special form fields are already injected
     *
     * @var bool
     */
    protected $injectedSpecialFormFields = false;

    /**
     * Indicator to check whether the form fields are already updated by extensions
     *
     * @var bool
     */
    protected $updatedFormFields = false;

    /**
     * Set to true to provide a spam check field
     *
     * @var array
     */
    public static $useSpamCheck = array();

    /**
     * Set to true to use Google Recaptcha as spam check
     *
     * @var boolean
     */
    public static $use_google_recaptcha = true;

    /**
     * Set to true to use a custom tabindex order for the forms fields
     *
     * @var array
     */
    protected $useCustomTabIndex = false;

    /**
     * Holds the highest tab index
     *
     * @var array
     */
    protected $highestTabIndex = 0;
    
    /**
     * Holds the forms using custom tabindexes
     *
     * @var int
     */
    public static $customTabIndexForms = array();

    /**
     * Set to true to exclude this form from caching.
     *
     * @var bool
     */
    protected $excludeFromCache = false;
    
    /**
     * Determines whether the CustomHtmlForm file cache is enabled or not
     *
     * @var bool
     */
    public static $cache_enabled = true;

    /**
     * saves controller of calling class
     *
     * @var Controller
     */
    protected $controller;

    /**
     * contains form definitions of form fields
     *
     * @var array
     */
    protected $formFields = array();

    /**
     * contains groups in which fields can be collected
     *
     * @var array
     */
    public $fieldGroups;

    /**
     * contains the form fields prepared for sapphire
     *
     * scheme:
     * $SSformFields = array(
     *     'fields' => array(FieldList),
     *     'actions' => array(FieldList)
     * );
     *
     * @var array
     */
    protected $SSformFields;

    /**
     * the objects class name
     *
     * @var string
     */
    public $class;

    /**
     * the objects name
     *
     * @var string
     */
    protected $name;

    /**
     * name of the objects which should be used for the JS validators
     *
     * @var string
     */
    protected $jsName;

    /**
     * contains the error message for a form field
     *
     * @var array
     */
    protected $errorMessages = array();

    /**
     * contains the messages for a form
     *
     * @var array
     */
    protected $messages = array();

    /**
     * contains the messages for a form to display in template
     *
     * @var ArrayList
     */
    protected $messagesForTemplate = null;

    /**
     * Contains an associative array with values that are passed to the form as
     * hidden fields. These values will not be validated, they only contain data
     * for control and evaluation.
     *
     * @var array
     */
    protected $customParameters = array();

    /**
     * the forms preferences; can be overwritten in the instance
     *
     * @var array
     */
    protected $basePreferences  = array(
        'createShoppingcartForms'           => true,
        'doJsValidation'                    => true,
        'doJsValidationScrolling'           => true,
        'fillInRequestValues'               => true,
        'isConditionalStep'                 => false,
        'loadShoppingcartModules'           => true,
        'markRequiredFields'                => false,
        'showJsValidationErrorMessages'     => true,
        'ShowCustomHtmlFormStepNavigation'  => true,
        'stepIsVisible'                     => true,
        'stepTitle'                         => '',
        'submitAction'                      => 'customHtmlFormSubmit',
        'submitButtonTitle'                 => 'Abschicken',
        'submitButtonToolTip'               => '',
        'submitButtonUseButtonTag'          => true,
        'submitButtonExtraClasses'          => array('btn'),
    );

    /**
     * Contains custom preferences that can be set in the form object.
     *
     * @var array
     */
    protected $preferences = array();

    /**
     * Contains fields that shall not be validated.
     *
     * @var array
     */
    protected $noValidationFields = array();

    /**
     * Instances of $this will have a unique ID
     *
     * Enthaelt fuer jede Formularklasse die Nummer der aktuellen
     * Instanziierung.
     *
     * @var array
     */
    public static $classInstanceCounter = array();

    /**
     * Contains the registered modules. This list is used by all methods that
     * fetch templates.
     * 
     * @var array
     */
    public static $registeredModules = array(
        'customhtmlform' => 1
    );

    /**
     * Contains the registered themes. This list is used by all methods that
     * fetch templates.
     *
     * @var array
     */
    public static $registeredThemes = array();

    /**
     * Contains a list of registerd custom html forms
     *
     * @var array
     */
    protected $registeredCustomHtmlForms = array();
    
    /**
     * Don't enable Security token for this type of form because we'll run
     * into caching problems when using it.
     * 
     * @var boolean
     */
    protected $securityTokenEnabled = true;
    
    /**
     * indicates whether there is an succeeded submission or not
     *
     * @var bool
     */
    protected $submitSuccess = false;
    
    /**
     * Contains all registered form field handlers.
     *
     * @var array
     */
    public static $registeredFormFieldHandlers = array();
    
    /**
     * CuzstomHtmlForm Cache object
     *
     * @var Zend_Cache_Core
     */
    protected static $cache = null;
    
    /**
     * Cache key for this form
     *
     * @var string
     */
    protected $cacheKey = null;
    
    /**
     * Cache key extension for this form
     *
     * @var string
     */
    protected $cacheKeyExtension = '';
    
    /**
     * Form fields and actions
     *
     * @var array
     */
    protected $form = null;

    /**
     * Indicates whether the form is called in barebone mode
     *
     * @var bool
     */
    protected $barebone = null;
    
    /**
     * Custom form action to use for this form.
     * If set, this will be used in context of 
     *
     * @var string
     */
    protected $customHtmlFormAction = null;
    
    /**
     * Custom CSS class to use for JS error messages.
     *
     * @var string
     */
    public static $custom_error_box_css_class = '';
    
    /**
     * CSS selector (child of #FieldID) to place the error messages box.
     *
     * @var string
     */
    public static $custom_error_box_sub_selector = '';
    
    /**
     * Allowed values:
     * append
     * prepend
     *
     * @var string
     */
    public static $custom_error_box_selection_method = '';

    /**
     * creates a form object with a free configurable markup
     *
     * @param ContentController $controller  the calling controller instance
     * @param array             $params      optional parameters
     * @param array             $preferences optional preferences
     * @param bool              $barebone    defines if a form should only be instanciated or be used too
     *
     * @return CustomHtmlForm
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 13.01.2015
     */
    public function __construct($controller, $params = null, $preferences = null, $barebone = false) {
        $this->class = get_class($this);
        $this->extend('onBeforeConstruct', $controller, $params, $preferences, $barebone);
        global $project;

        $this->barebone   = $barebone;
        $this->controller = $controller;

        if (is_array($params)) {
            $this->customParameters = $params;
        }

        // Hook for setting preferences via a method call
        $this->preferences();

        if (is_array($preferences)) {
            foreach ($preferences as $title => $setting) {
                if (!empty($title)) {
                    $this->basePreferences[$title] = $setting;
                }
            }
        }
        
        $name = $this->getSubmitAction();

        if (!$barebone) {
            $this->getFormFields();
        }
        
        if ($this->securityTokenEnabled) {
            SecurityToken::enable();
        } else {
            SecurityToken::disable();
        }

        parent::__construct(
            $this->getFormController($controller, $preferences),
            $name,
            new FieldList(),
            new FieldList()
        );
        
        if (!$barebone) {
            $this->getFormFields();
            $this->fillInFieldValues();
        }
        
        // Hook for setting preferences via a method call; we need to do this
        // a second time so that the standard Silverstripe mechanism can take
        // influence, too (i.e. _config.php files, init methods, etc).
        $this->preferences();

        if (is_array($preferences)) {
            foreach ($preferences as $title => $setting) {
                if (!empty($title)) {
                    $this->basePreferences[$title] = $setting;
                }
            }
        }

        // Counter for the form class, init or increment
        if (!isset(self::$classInstanceCounter[$this->class])) {
            self::$classInstanceCounter[$this->class] = 0;
        }

        if (!$barebone) {
            self::$classInstanceCounter[$this->class]++;
        }

        // new assignment required, because the controller will be overwritten in the form class
        $this->controller = $controller;

        // create group structure
        if (isset($this->formFields)) {
            $this->fieldGroups['formFields'] = $this->getFormFields();
        } else {
            $this->fieldGroups['formFields'] = array();
        }

        $this->name               = str_replace('/', '', $this->class.'_'.$name.'_'.(self::$classInstanceCounter[$this->class]));
        $this->jsName             = $this->name;
        $this->SSformFields       = $this->getForm();
        $this->SSformFields['fields']->setForm($this);
        $this->SSformFields['actions']->setForm($this);
        parent::setFields($this->SSformFields['fields']);
        parent::setActions($this->SSformFields['actions']);

        // define form action
        $this->setFormAction($this->buildFormAction());
        $this->setHTMLID($this->getName());

        /*
         * load and init JS validators
         * form integration via FormAttributes()
         */
        if (!$barebone) {
            $javascriptSnippets = $this->getJavascriptValidatorInitialisation();

            if (class_exists('SilverCart\\Model\\Order\\ShoppingCart')) {
                if (!$this->getLoadShoppingCartModules()) {
                    \SilverCart\Model\Order\ShoppingCart::setLoadShoppingCartModules(false);
                }
                if ($this->getCreateShoppingCartForms()) {
                    \SilverCart\Model\Order\ShoppingCart::setCreateShoppingCartForms(false);
                }
            }
            
            $this->controller->addJavascriptSnippet($javascriptSnippets['javascriptSnippets']);
            $this->controller->addJavascriptOnloadSnippet($javascriptSnippets['javascriptOnloadSnippets']);
            $this->controller->addJavascriptOnloadSnippet($this->getJavascriptFieldInitialisations());
        }

        // Register the default module directory from mysite/_config.php
        self::registerModule($project);
        $this->extend('onAfterConstruct', $controller, $params, $preferences, $barebone);
    }

    /**
     * Here you can set the preferences. This is an alternative to setting
     * them via the $preferences class variable.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 23.02.2011
     */
    public function preferences() {
        $this->extend('updatePreferences', $this->preferences);
    }
    
    /**
     * Used to overwrite a CustomHtmlForms process with an extension
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2012
     */
    public function extendedProcess() {
        $processResult = $this->extend('extendedProcess');
        if (empty ($processResult)) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }

    /**
     * Add a module for the template pull methods.
     *
     * You can give a priority ranging from 1 to 100. The standard priority
     * for the project given in "mysite/_config.php" is 50. The
     * customhtmlform priority is 1. To override both you would give a
     * priority of 51 or higher.
     *
     * @param string $moduleName The name of the module
     * @param int    $priority   The priority
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 28.01.2011
     */
    public static function registerModule($moduleName, $priority = 51) {
        self::$registeredModules[$moduleName] = $priority;
    }

    /**
     * Add a theme for the template pull methods.
     *
     * You can give a priority ranging from 1 to 100. The standard priority
     * for the project given in "mysite/_config.php" is 50. The
     * customhtmlform priority is 1. To override both you would give a
     * priority of 51 or higher.
     *
     * @param string $themeName The name of the module
     * @param int    $priority  The priority
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2013-04-23
     */
    public static function registerTheme($themeName, $priority = 51) {
        self::$registeredThemes[$themeName] = $priority;
    }

    /**
     * Returns an HTML tag for marking fields as required.
     *
     * @param boolean $isRequiredField Indicate wether this is a required field
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 29.03.2012
     */
    public function RequiredFieldMarker($isRequiredField) {
        $marker             = '';
        $markRequiredFields = $this->getPreference('markRequiredFields');

        if ($isRequiredField &&
            $markRequiredFields) {

            $marker = _t(CustomHtmlForm::class . '.REQUIRED_FIELD_MARKER', '<span class="required-field">[ Required ]</span>');
        }

        return $marker;
    }

    /**
     * Returns javascript code for fields that need special initialisation,
     * e.g. the datepicker field
     *
     * @return string
     */
    public function getJavascriptFieldInitialisations() {
        $this->getFormFields();
        $snippet    = '';

        foreach ($this->fieldGroups as $groupFields) {
            foreach ($groupFields as $fieldName => $fieldDefinition) {
                if ($fieldDefinition['type'] == DateField::class) {
                    $config = '';

                    if (array_key_exists('configuration', $fieldDefinition)) {
                        foreach ($fieldDefinition['configuration'] as $option => $value) {
                            $config .= sprintf(
                                "'%s': '%s',",
                                $option,
                                $value
                            );
                        }

                        if (!empty($config)) {
                            $config = substr($config, 0, -1);
                        }
                        
                        $snippet .= sprintf(
                            "$('input[name=\"%s\"]').datepicker({%s});",
                            $fieldName,
                            $config
                        );
                    }
                }
            }
        }

        return $snippet;
    }

    /**
     * Returns JS commands for JS validators init
     *
     * @return array
     */
    public function getJavascriptValidatorInitialisation() {
        $javascriptSnippets = '';
        $javascriptOnloadSnippets = '';
        if ($this->getDoJsValidation()) {
            $validatorFields    = JavascriptTools::generateJsValidatorFields($this->fieldGroups);
            $javascriptSnippets = '
                var ' . $this->jsName . ';';

            $javascriptOnloadSnippets = '
                ' . $this->jsName . ' = new pixeltricks.forms.validator();
                ' . $this->jsName . '.setFormFields({' . $validatorFields . '});
                ' . $this->jsName . '.setFormName(\'' . $this->jsName . '\');
                ' . $this->jsName . '.setPreference(\'doJsValidationScrolling\', ' . ($this->getDoJsValidationScrolling() ? 'true' : 'false') . ');
                ' . $this->jsName . '.setPreference(\'showJsValidationErrorMessages\', ' . ($this->getShowJsValidationErrorMessages() ? 'true' : 'false') . ');
                ' . $this->jsName . '.bindEvents();';

            if (!empty(self::$custom_error_box_css_class)) {
                $javascriptOnloadSnippets .= $this->jsName . ".setErrorBoxCssClass('" . self::$custom_error_box_css_class . "');";
            }
            if (!empty(self::$custom_error_box_sub_selector)) {
                $javascriptOnloadSnippets .= $this->jsName . ".setErrorBoxSubSelector('" . self::$custom_error_box_sub_selector . "');";
            }
            if (!empty(self::$custom_error_box_selection_method)) {
                $javascriptOnloadSnippets .= $this->jsName . ".setErrorBoxSelectionMethod('" . self::$custom_error_box_selection_method . "');";
            }
            $javascriptOnloadSnippets .= '$("#' . $this->jsName . '").bind("submit", function(e) { return ' . $this->jsName . '.checkForm(e); });';
        }

        return array(
            'javascriptSnippets'        => $javascriptSnippets,
            'javascriptOnloadSnippets'  => $javascriptOnloadSnippets
        );
    }
    
    /**
     * Set a form field.
     *
     * @param string $identifier      The identifier of the field
     * @param string $fieldDefinition The field definition
     *
     * @return void
     */
    public function setFormField($identifier, $fieldDefinition) {
        $this->formFields[$identifier] = $fieldDefinition;
    }

    /**
     * Set a custom parameter on the given form field.
     *
     * @param string $identifier The identifier of the field
     * @param string $value      The value of the field
     *
     * @return void
     */
    public function setFormFieldValue($identifier, $value) {
        $field = $this->SSformFields['fields']->fieldByName($identifier);
        if ($field) {
            $field->setValue($value);
        }

        $this->SSformFields = $this->getForm();
        $this->SSformFields['fields']->setForm($this);
        $this->SSformFields['actions']->setForm($this);
    }

    /**
     * this method can be implemented optionally in child classes
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 25.10.2010
     */
    protected function fillInFieldValues() {
        if ($this->getFillInRequestValues()) {
            $this->fillInRequestValues();
        }
    }

    /**
     * fills form fields with values from the request
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>,
     *         Sebastian Diel <sdiel@pixeltricks.de>
     * @since 04.07.2013
     */
    protected function fillInRequestValues() {
        $request = $this->controller->getRequest();

        $formFields = $this->getFormFields();

        $class = get_class($this);
        if (array_key_exists($class, self::$classInstanceCounter)) {
            $instanceCounter = self::$classInstanceCounter[$class] + 1;
        } else {
            $instanceCounter = 1;
        }
        $customHtmlFormName = str_replace('/', '', $class . '_' . $this->getSubmitAction() . '_' . $instanceCounter);
        
        if ($formFields &&
            $request['CustomHtmlFormName'] == $customHtmlFormName) {
            foreach ($formFields as $fieldName => $fieldDefinition) {
                if (isset($request[$fieldName])) {
                    if ($fieldDefinition['type'] == PasswordField::class) {
                        continue;
                    }
                    $this->formFields[$fieldName][$this->getFormFieldValueLabel($fieldName)] = $request[$fieldName];
                }
            }
        }
    }

    /**
     * Returns the parameter used to set the field value; might be "value" or "selectedValue"
     * Liefert den Parameter, der zum Setzen des Feldwertes benutzt wird.
     * Dieser kann je nach Feldtyp "value" oder "selectedValue" sein.
     *
     * @param string $fieldName name of the field
     *
     * @return string
     */
    protected function getFormFieldValueLabel($fieldName) {
        $valueLabel = 'value';

        $formFields = $this->getFormFields();
        
        if (isset($formFields[$fieldName])) {
            $fieldDefinition = $formFields[$fieldName];
            
            if (Tools::isDropdownField($fieldDefinition['type']) ||
                Tools::isListboxField($fieldDefinition['type'])) {
                $valueLabel = 'selectedValue';
            }
        }

        return $valueLabel;
    }

    /**
     * Processes the submitted form; If there are validation errors the form will
     * be returned with error messages.
     *
     * @param HTTPRequest $data submit data
     * @param Form        $form form object
     *
     * @return ViewableData
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 24.01.2014
     */
    public function submit($data, $form) {
        $this->extend('onBeforeSubmit', $data, $form);
        $formData = $this->getFormData($data);
        $this->extend('updateSubmittedFormData', $formData, $data);
        $this->checkFormData($formData);
        $result = null;

        ob_start();
        if (empty($this->errorMessages)) {
            $this->setSubmitSuccess(true);
            $submitSuccessResult = '';
            // No error occured
            $overwriteResult = $this->extend('overwriteSubmitSuccess', $data, $form, $formData);
            if (empty ($overwriteResult)) {
                $this->extend('onBeforeSubmitSuccess', $data, $form, $formData);
                $submitSuccessResult = $this->submitSuccess(
                    $data,
                    $form,
                    $formData
                );
                $this->extend('onAfterSubmitSuccess', $data, $form, $formData);
            } else {
                $submitSuccessResult = $overwriteResult[0];
            }
            $result = $submitSuccessResult;
        } else {
            // An error occured
            $overwriteResult = $this->extend('overwriteSubmitFailure', $data, $form);
            if (empty ($overwriteResult)) {
                $this->extend('onBeforeSubmitFailure', $data, $form);
                $submitFailureResult = $this->submitFailure(
                    $data,
                    $form
                );
                $this->extend('onAfterSubmitFailure', $data, $form);
            } else {
                $submitFailureResult = $overwriteResult[0];
            }
            $result = $submitFailureResult;
        }
        $output = ob_get_contents();
        ob_end_clean();

        if (!Controller::curr()->redirectedTo() &&
            empty($result) &&
            !empty($output)) {
            Controller::curr()->redirectBack();
        }

        return $result;
    }

    /**
     * In calse of validation errors the form will be returned with error
     * messages
     *
     * @param HTTPRequest $data submit data
     * @param Form        $form form object
     *
     * @return ViewableData
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 25.10.2010
     */
    public function submitFailure($data, $form) {
        $formFields = $this->getFormFields();

        // fill in the form
        foreach ($formFields as $fieldName => $fieldDefinition) {
            if (isset($data[$fieldName])) {
                $this->formFields[$fieldName][$this->getFormFieldValueLabel($fieldName)] = Convert::raw2xml($data[$fieldName]);
            }
        }

        $this->SSformFields = $this->getForm();

        if (empty($form)) {
            $form = $this->class;
        }
        
        if (class_exists('CommunityPage') &&
            method_exists('CommunityPage', 'addErrorMessage')) {
            foreach ($this->errorMessages as $fieldName => $errorMessage) {
                CommunityPage::addErrorMessage($errorMessage['message'], $errorMessage['fieldname']);
            }
        }

        // prepare validation errors for template
        $data = array(
            'errorMessages' => new ArrayList($this->errorMessages),
            'messages'      => new ArrayList($this->messages),
            $this->SSformFields['fields'],
            $this->SSformFields['actions'],
            'CustomHtmlFormErrorMessages' => $this->CustomHtmlFormErrorMessages()
        );

        parent::__construct(
            $this->controller,
            $this->name,
            $this->SSformFields['fields'],
            $this->SSformFields['actions']
        );

        // fill in form with validation results and render it
        $outputForm = $this->customise($data)->renderWith(
            array(
                $this->class
            )
        );

        // pass rendered form to the controller
        if ($this->controller instanceof StepPageController) {
            $output = $this->controller->customise(
                array(
                    $form => $outputForm
                )
            )->renderWith(array($this->controller->ClassName, 'Page'));
        } else {
            $output = $this->controller->customise(
                array(
                    $form => $outputForm
                )
            );
        }

        return $output;
    }

    /**
     * This method will be call if there are no validation error
     *
     * @param HTTPRequest $data     input data
     * @param Form        $form     form object
     * @param array       $formData secured form data
     *
     * @return mixed
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 25.10.2010
     */
    protected function submitSuccess(HTTPRequest $data, Form $form, $formData) {
        // In Instanz implementieren
    }

    /**
     * Passes the values from the HTTPRequest object to the defined form;
     * missing values will be set to false
     *
     * during the transmission the values will become SQL secure
     *
     * @param HTTPRequest $request the submitted data
     *
     * @return array
     */
    public function getFormData($request) {
        $formData = array();
        $postVars = $request->postVars();

        if ($this->securityTokenEnabled) {
            $formData['SecurityID'] = Convert::raw2sql($postVars['SecurityID']);
        }

        // read defined form fields
        // Definierte Formularfelder auslesen
        foreach ($this->fieldGroups as $groupName => $groupFields) {
            foreach ($groupFields as $fieldName => $fieldDefinition) {
                if (array_key_exists($fieldName, $postVars)) {
                    $formData[$fieldName] = Convert::raw2sql($postVars[$fieldName]);
                } else {
                    $formData[$fieldName] = false;
                }
            }
        }

        // read dynamically added form fields
        // Dynamisch hinzugefuegte Formularfelder auslesen
        if (isset($this->customParameters)) {
            foreach ($this->customParameters as $customParameterKey => $customParameterValue) {
                if (isset($request[$customParameterKey])) {
                    $formData[$customParameterKey] = Convert::raw2sql($request[$customParameterKey]);
                } else {
                    $formData[$customParameterKey] = false;
                }
            }
        }

        return $formData;
    }

    /**
     * checks all form fields and returns them as array
     *
     * @param HTTPRequest $data Die zu pruefenden Formulardaten.
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 14.07.2014
     */
    protected function checkFormData($data) {
        $errorMessages  = array();
        $error          = false;

        if ($this->securityTokenEnabled) {
            $securityID = $this->getSecurityID();
            
            if (empty($securityID) ||
                empty($data['SecurityID']) ||
                $data['SecurityID'] != $securityID) {

                // Possible CSRF attack
                $error                 = true;
                $errorMessages['CSRF'] = array(
                    'message'   => _t(CustomHtmlForm::class . '.ERROR_CSRF_MESSAGE', 'Possible CSRF attack!'),
                    'fieldname' => _t(CustomHtmlForm::class . '.ERROR_CSRF_FIELDS', 'Your session has expired. Please reload this page and fill in the form again.'),
                    'title'     => _t(CustomHtmlForm::class . '.ERROR_CSRF_FIELDS', 'Your session has expired. Please reload this page and fill in the form again.'),
                    'CSRF' => array(
                        'message' => _t(CustomHtmlForm::class . '.ERROR_CSRF_MESSAGE', 'Possible CSRF attack!'),
                    )
                );
            }
        }

        if (!$error) {
            foreach ($this->fieldGroups as $groupName => $groupFields) {
                foreach ($groupFields as $fieldName => $fieldDefinition) {
                    $fieldErrorMessages = array();
                    $fieldError         = false;
                    $checkFormData      = new CheckFormData($data[$fieldName]);

                    // Formale Erfordernisse pruefen, die dieses Feld erfuellen muss.
                    if (!isset($fieldDefinition['checkRequirements'])) {
                        continue;
                    }

                    // Check if the field shall be validated
                    if (in_array($fieldName, $this->noValidationFields)) {
                        continue;
                    }

                    foreach ($fieldDefinition['checkRequirements'] as $requirement => $requiredValue) {
                        if (empty($requirement)) {
                            continue;
                        }
                        
                        // --------------------------------------------------------
                        // Sonderfaelle:
                        // --------------------------------------------------------

                        // Kriterium bezieht sich auf ein anderes Feld
                        if ($requirement == 'mustEqual' ||
                            $requirement == 'mustNotEqual' ||
                            strpos($requirement, 'mustEqual__') === 0 ||
                            strpos($requirement, 'mustNotEqual__') === 0) {
                            
                            if (strpos($requirement, 'mustEqual__') === 0 ||
                                strpos($requirement, 'mustNotEqual__') === 0) {
                                $requirement = substr($requirement, 0, strpos($requirement, '_'));
                            }

                            $requiredValue = array(
                                'fieldName'  => $requiredValue,
                                'fieldTitle' => $groupFields[$requiredValue]['title'] ? $groupFields[$requiredValue]['title'] : $requiredValue,
                                'value'      => $data[$requiredValue]
                            );
                        }
                        if ($requirement == 'mustEqualDependantOn' ||
                            $requirement == 'mustNotEqualDependantOn' ||
                            strpos($requirement, 'mustEqualDependantOn__') === 0 ||
                            strpos($requirement, 'mustNotEqualDependantOn__') === 0) {
                            
                            if (strpos($requirement, 'mustEqualDependantOn__') === 0 ||
                                strpos($requirement, 'mustNotEqualDependantOn__') === 0) {
                                $requirement = substr($requirement, 0, strpos($requirement, '_'));
                            }

                            $targetField   = $requiredValue['targetField'];
                            $requiredValue = array(
                                $requiredValue,
                                $data,
                                array(
                                    'fieldName'  => $targetField,
                                    'fieldTitle' => $groupFields[$targetField]['title'] ? $groupFields[$targetField]['title'] : $targetField,
                                    'value'      => $data[$targetField]
                                )
                            );
                        }

                        // Feld muss ausgefuellt sein, wenn anderes Feld
                        // ausgefuellt ist
                        if ($requirement == 'isFilledInDependantOn') {
                            $requiredValue = array(
                                $requiredValue,
                                $data,
                                true
                            );
                        }

                        // PtCaptchaField
                        if ($requirement == 'PtCaptchaInput') {
                            $requiredValue = array(
                                'formName'  => $this->class,
                                'fieldName' => $this->name.'PtCaptchaImageField'
                            );
                        }
                        // GoogleRecaptchaField
                        if ($requirement == 'GoogleRecaptchaField') {
                            $requiredValue = array(
                                'formName'  => $this->class,
                                'fieldName' => $this->name.'GoogleRecaptchaField'
                            );
                        }

                        // Callbackfunktion verwenden
                        if ($requirement == 'callBack') {
                            $fieldCheckResult = $this->$requiredValue($data[$fieldName]);
                        } else {
                            $fieldCheckResult = $checkFormData->$requirement($requiredValue);
                        }

                        if (!$fieldCheckResult['success']) {
                            $fieldErrorMessages[]   = $fieldCheckResult['errorMessage'];
                            $fieldError             = true;
                        }
                    }

                    // Bei diesem Feld sind ein oder mehrere Fehler aufgetreten, also
                    // diese zuordnen und speichern.
                    if ($fieldError) {
                        // Fehler an das Formularfeld anhaengen
                        foreach ($this->SSformFields['fields'] as $field) {
                            if ($field->name == $fieldName) {
                                $field->errorMessage = new ArrayData(array(
                                    'message' => implode("\n", $fieldErrorMessages)
                                ));
                            }
                        }

                        // Fehler in eigenem Feld speichern
                        $errorMessages[$fieldName] = array(
                            'message'   => implode("\n", $fieldErrorMessages),
                            'fieldname' => $fieldDefinition['title'] ? $fieldDefinition['title'] : $fieldName,
                            $fieldName => array(
                                'message' => implode("\n", $fieldErrorMessages)
                            )
                        );
                        $error = true;
                    }
                }
            }
        }

        $this->errorMessages = $errorMessages;
    }

    /**
     * creates the form's input fields and action fields and fills missing data
     * with standard values.
     * Returns:
     *      array(
     *          'fields'    => FieldList,
     *          'actions'   => FieldList
     *      )
     *
     * @return array retunrs form fields and form actions
     */
    protected function getForm() {
        if (is_null($this->form)) {
            $fields = new FieldList();

            // --------------------------------------------------------------------
            // define meta data
            // --------------------------------------------------------------------
            if (!empty($this->customParameters)) {
                foreach ($this->customParameters as $customParameterKey => $customParameterValue) {
                    $field = new HiddenField($customParameterKey, '', $customParameterValue, null, null);
                    $fields->push($field);
                }
            }

            $field = new HiddenField('CustomHtmlFormName', '', $this->getCustomHtmlFormName(), null, null);
            $fields->push($field);

            // --------------------------------------------------------------------
            // create field set from definition
            // --------------------------------------------------------------------
            foreach ($this->fieldGroups as $groupFields) {
                foreach ($groupFields as $fieldName => $fieldDefinition) {
                    $field = $this->getFormField(
                        $fieldName,
                        $fieldDefinition
                    );

                    $fields->push($field);
                }
            }

            $formAction = new FormAction(
                $this->getSubmitAction(),
                $this->getSubmitButtonTitle(),
                $this
            );
            $formAction->description = $this->getSubmitButtonToolTip();
            
            if ($this->getPreference('submitButtonUseButtonTag')) {
                $formAction->setUseButtonTag(true);
            }
            $extraClasses = $this->getPreference('submitButtonExtraClasses');
            if (is_array($extraClasses)) {
                foreach ($extraClasses as $extraClass) {
                    $formAction->addExtraClass($extraClass);
                }
            }

            $actions = new FieldList(
                $formAction
            );

            $this->form = array(
                'fields'    => $fields,
                'actions'   => $actions
            );
        }
        return $this->form;
    }

    /**
     * Returns the requested formField or customParameter if available.
     *
     * @param string $fieldName The name to search for
     *
     * @return mixed
     */
    public function getFormFieldDefinition($fieldName) {
        if (isset($this->customParameters[$fieldName])) {
            return $this->customParameters[$fieldName];
        }
        
        if (isset($this->formFields[$fieldName])) {
            return $this->formFields[$fieldName];
        }
        
        return false;
    }
    
    /**
     * fill required fields with standard values if they where not specified
     * 
     * @param array &$fieldDefinition Field definition
     * @param array &$fieldReference  Field reference
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 04.03.2014
     */
    protected function addRequiredFieldParams(&$fieldDefinition, &$fieldReference) {
        $requiredFieldParams = array(
            'isRequired'            => false,
            'checkRequirements'     => array(),
            'title'                 => '',
            'value'                 => '',
            'selectedValue'         => '',
            'size'                  => null,
            'multiple'              => null,
            'tabIndex'              => 0,
            'form'                  => $this,
            'maxLength'             => Tools::isTextField($fieldDefinition['type']) ? 255 : null,
        );
        foreach ($requiredFieldParams as $param => $default) {
            if (!array_key_exists($param, $fieldDefinition)) {
                $fieldDefinition[$param] = $default;
                $fieldReference[$param]  = $default;
            }
        }
    }

    /**
     * creates a form field from the definition; sets standard values if they
     * are not defined
     *
     * @param string $fieldName       the field's name
     * @param array  $fieldDefinition the field definitions
     *
     * @return Field
     *
     * @throws Exception
     */
    public function getFormField($fieldName, $fieldDefinition) {

        if (!isset($fieldDefinition['type'])) {
            throw new Exception(
                'CustomHtmlForm: Field type must be specified.'
            );
        }

        // SelectionGroup fields use '//' as separator for key/label definition
        if (strpos($fieldName, '//') !== false) {
            list($fieldName, $fieldLabel) = explode('//', $fieldName, 2);
        }

        foreach ($this->fieldGroups as $groupFields) {
            if (isset($groupFields[$fieldName])) {
                $fieldReference = &$groupFields[$fieldName];
                break;
            }
        }

        $this->addRequiredFieldParams($fieldDefinition, $fieldReference);

        // create field
        if (Tools::isListboxField($fieldDefinition['type'])) {
            $field = new $fieldDefinition['type'](
                $fieldName,
                $fieldDefinition['title'],
                $fieldDefinition['value'],
                $fieldDefinition['selectedValue'],
                $fieldDefinition['size'],
                $fieldDefinition['multiple'],
                $fieldDefinition['form']
            );
        } else if (Tools::isDropdownField($fieldDefinition['type'])) {
            if ($fieldDefinition['type'] == TreeDropdownField::class) {
                Requirements::css(FRAMEWORK_DIR . '/css/TreeDropdownField.css');
                Requirements::css('customhtmlform/css/TreeDropdownField.css');
                $field = new $fieldDefinition['type'](
                    $fieldName,
                    $fieldDefinition['title'],
                    $fieldDefinition['sourceObject'],
                    $fieldDefinition['selectedValue'],
                    $fieldDefinition['labelField'],
                    $fieldDefinition['showSearch']
                );
                if (array_key_exists('treeBaseID', $fieldDefinition)) {
                    $field->setTreeBaseID($fieldDefinition['treeBaseID']);
                }
                if (array_key_exists('value', $fieldDefinition)) {
                    $field->setValue($fieldDefinition['value']);
                }
            } else {
                $field = new $fieldDefinition['type'](
                    $fieldName,
                    $fieldDefinition['title'],
                    $fieldDefinition['value'],
                    $fieldDefinition['selectedValue'],
                    $fieldDefinition['form']
                );
            }
        } else if (Tools::isOptionsetField($fieldDefinition['type'])) {
            $field = new $fieldDefinition['type'](
                $fieldName,
                $fieldDefinition['title'],
                $fieldDefinition['value'],
                $fieldDefinition['selectedValue'],
                $fieldDefinition['form']
            );
        } else if (Tools::isSelectiongroupField($fieldDefinition['type'])) {
            $groupFields = array();

            foreach ($fieldDefinition['items'] as $itemFieldName => $item) {
                $itemObj                     = $this->getFormField($itemFieldName, $item);
                $groupFields[$itemFieldName] = $itemObj;
            }

            if (empty($groupFields)) {
                return false;
            }

            $field = new $fieldDefinition['type'](
                $fieldName,
                $groupFields
            );
            $field->value = $fieldDefinition['value'];
        } else if (Tools::isTextField($fieldDefinition['type'])) {
            $field = new $fieldDefinition['type'](
                $fieldName,
                $fieldDefinition['title'],
                $fieldDefinition['value']
            );
            $field->setMaxLength($fieldDefinition['maxLength']);
            $field->setForm($fieldDefinition['form']);
            if (isset($fieldDefinition['placeholder']) &&
                method_exists($field, 'setPlaceholder')) {
                $field->setPlaceholder($fieldDefinition['placeholder']);
            }

            if (isset($fieldDefinition['configuration']) &&
                is_array($fieldDefinition['configuration']) &&
                method_exists($field, 'setConfig')) {

                foreach ($fieldDefinition['configuration'] as $key => $value) {
                    $field->setConfig($key, $value);
                }
            }
        } else if ($fieldDefinition['type'] == PtCaptchaImageField::class) {
            $field = new $fieldDefinition['type'](
                $fieldName,
                $fieldDefinition['title'],
                $fieldDefinition['value'],
                $fieldDefinition['maxLength'],
                $fieldDefinition['form']
            );
        } else if ($fieldDefinition['type'] == PasswordField::class) {
            $field = new $fieldDefinition['type'](
                $fieldName,
                $fieldDefinition['title'],
                $fieldDefinition['value']
            );
            $field->setMaxLength($fieldDefinition['maxLength']);
        } else if ($fieldDefinition['type'] == DateField::class) {
            $field = new $fieldDefinition['type'](
                $fieldName,
                $fieldDefinition['title'],
                $fieldDefinition['value'],
                $fieldDefinition['form']
            );
            $field->setMaxLength(16);

            if (isset($fieldDefinition['configuration']) &&
                is_array($fieldDefinition['configuration'])) {

                foreach ($fieldDefinition['configuration'] as $key => $value) {
                    $field->setConfig($key, $value);
                }
            }
        } else if (Tools::isTextareaField($fieldDefinition['type'])) {

            if (!isset($fieldDefinition['rows'])) {
                $fieldDefinition['rows'] = 10;
                $fieldReference['rows'] = $fieldDefinition['rows'];
            }
            if (!isset($fieldDefinition['cols'])) {
                $fieldDefinition['cols'] = 10;
                $fieldReference['cols'] = $fieldDefinition['cols'];
            }

            $field = new $fieldDefinition['type'](
                $fieldName,
                $fieldDefinition['title'],
                $fieldDefinition['value']
            );
            $field->setColumns($fieldDefinition['cols']);
            $field->setRows($fieldDefinition['rows']);
            $field->setForm($fieldDefinition['form']);
            if (isset($fieldDefinition['placeholder']) &&
                method_exists($field, 'setPlaceholder')) {
                $field->setPlaceholder($fieldDefinition['placeholder']);
            }
        } else if ($fieldDefinition['type'] == UploadField::class ||
                   in_array(UploadField::class, class_parents($fieldDefinition['type']))) {
            $field = new $fieldDefinition['type'](
                $fieldName,
                $fieldDefinition['title'],
                $fieldDefinition['value']
            );

            if (array_key_exists('configuration', $fieldDefinition) &&
                is_array($fieldDefinition['configuration'])) {
                foreach ($fieldDefinition['configuration'] as $key => $value) {
                    $field->setConfig($key, $value);
                }
            }
            if (array_key_exists('allowedExtensions', $fieldDefinition) &&
                is_array($fieldDefinition['allowedExtensions'])) {
                $field->getValidator()->setAllowedExtensions($fieldDefinition['allowedExtensions']);
            }
            if (array_key_exists('folderName', $fieldDefinition)) {
                $field->setFolderName($fieldDefinition['folderName']);
            }
            if (array_key_exists('record', $fieldDefinition)) {
                $field->setRecord($fieldDefinition['record']);
            }
            if (array_key_exists('alternativeLink', $fieldDefinition)) {
                $field->setAlternativeLink($fieldDefinition['alternativeLink']);
            }
            if (array_key_exists('showTitleField', $fieldDefinition)) {
                $field->setShowTitleField($fieldDefinition['showTitleField']);
            }
            if (array_key_exists('showDescriptionField', $fieldDefinition)) {
                $field->setShowDescriptionField($fieldDefinition['showDescriptionField']);
            }
        } else if ($fieldDefinition['type'] == TreeMultiselectField::class) {
            Requirements::css(FRAMEWORK_DIR . '/css/TreeDropdownField.css');
            Requirements::css('customhtmlform/css/TreeDropdownField.css');
            if (array_key_exists('keyField', $fieldDefinition)) {
                $fieldDefinition['selectedValue'] = $fieldDefinition['keyField'];
            }
            $field = new $fieldDefinition['type'](
                $fieldName,
                $fieldDefinition['title'],
                $fieldDefinition['sourceObject'],
                $fieldDefinition['selectedValue'],
                $fieldDefinition['labelField'],
                $fieldDefinition['showSearch']
            );
            if (array_key_exists('treeBaseID', $fieldDefinition)) {
                $field->setTreeBaseID($fieldDefinition['treeBaseID']);
            }
            if (array_key_exists('value', $fieldDefinition)) {
                $field->setValue($fieldDefinition['value']);
            }
        } else {
            $formFieldHandler = self::getRegisteredFormFieldHandlerForType($fieldDefinition['type']);
            if ($formFieldHandler) {
                $field = $formFieldHandler->getFormField($fieldName, $fieldDefinition);
            } else {
                $field = new $fieldDefinition['type'](
                    $fieldName,
                    $fieldDefinition['title'],
                    $fieldDefinition['value']
                );
                $field->setForm($fieldDefinition['form']);
            }
        }
        
        if (method_exists($field, 'addExtraClass') &&
            array_key_exists('extraClasses', $fieldDefinition) &&
            is_array($fieldDefinition['extraClasses'])) {
            foreach ($fieldDefinition['extraClasses'] as $extraClass) {
                $field->addExtraClass($extraClass);
            }
        }

        // add error message for a field if defined
        if (isset($this->errorMessages[$fieldName])) {
            $field->errorMessage = new ArrayData(array(
                'message' => $this->errorMessages[$fieldName]['message']
            ));
        }

        // set identifier for mandatory fields
        if (isset($fieldDefinition['checkRequirements']) &&
            isset($fieldDefinition['checkRequirements']['isFilledIn']) &&
            $fieldDefinition['checkRequirements']['isFilledIn']) {

            $field->isRequiredField = true;
        } else {
            $field->isRequiredField = false;
        }

        if ($this->useCustomTabIndex()) {
            if (!in_array($this->name, self::$customTabIndexForms)) {
                self::$customTabIndexForms[] = $this->name;
            }
            $baseIndex  = count(self::$customTabIndexForms) * 100;
            $tabIndex   = $baseIndex + (int) $fieldDefinition['tabIndex'];
            if ($tabIndex > $this->highestTabIndex) {
                $this->highestTabIndex = $tabIndex;
            }
            if ($tabIndex == $baseIndex) {
                $tabIndex = ++$this->highestTabIndex;
            }
            $field->setTabIndex($tabIndex);
        }
        
        if (array_key_exists('isReadonly', $fieldDefinition) &&
            $fieldDefinition['isReadonly'] == true &&
            method_exists($field, 'setReadonly')) {
            $field->setReadonly(true);
        }
        if (array_key_exists('isDisabled', $fieldDefinition) &&
            $fieldDefinition['isDisabled'] == true &&
            method_exists($field, 'setDisabled')) {
            $field->setDisabled(true);
        }

        if (array_key_exists('data', $fieldDefinition) &&
            $field instanceof FormField) {
            foreach ($fieldDefinition['data'] as $key => $value) {
                $field->setAttribute('data-' . $key, $value);
            }
        }
        
        return $field;
    }
    
    /**
     * Builds and returns the form action to use.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 04.07.2013
     */
    protected function buildFormAction() {
        if (is_null($this->customHtmlFormAction)) {
            $formAction = Controller::join_links(
                    $this->getFormController(
                            $this->controller,
                            $this->basePreferences
                    )->Link(),
                    $this->getSubmitAction()
            );
        } else {
            $formAction = Director::baseUrl() . 'customhtmlformaction/' . $this->customHtmlFormAction;
        }
        return $formAction;
    }

    /**
     * returns the form objects name
     *
     * @return string
     */
    public function getCustomHtmlFormName() {
        return $this->name;
    }

    /**
     * Returns the forms fields.
     * 
     * @param bool $withUpdate Call the method with extension updates or not?
     *
     * @return array
     */
    public function getFormFields($withUpdate = true) {
        if (!is_null($this->class)) {
            if (!$this->injectedSpecialFormFields) {
                $this->injectedSpecialFormFields = true;
                $this->injectSpecialFormFields();
            }

            if ($withUpdate &&
               !$this->updatedFormFields) {

                $this->updatedFormFields = true;
                $this->extend('updateFormFields', $this->formFields);
            }
        }

        return $this->formFields;
    }

    /**
     * Injects special form fields before the form gets calculated.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.02.2013
     */
    public function injectSpecialFormFields() {
        if (array_key_exists($this->class, self::$useSpamCheck)) {
            $secret  = GoogleRecaptchaField::get_recaptcha_secret();
            $siteKey = GoogleRecaptchaField::get_recaptcha_site_key();
            if (empty($secret) ||
                empty($siteKey)) {
                self::$use_google_recaptcha = false;
            }
            if (self::$use_google_recaptcha) {
                $this->formFields['GoogleRecaptchaField'] = array(
                    'type'      => GoogleRecaptchaField::class,
                    'title'     => _t(GoogleRecaptchaField::class . '.Title', ' '),
                    'form'      => $this,
                    'checkRequirements' => array(
                        'GoogleRecaptchaField' => true
                    )
                );
            } else {
                $this->formFields['PtCaptchaInputField'] = array(
                    'type'              => PtCaptchaInputField::class,
                    'title'             => _t(PtCaptchaInputField::class . '.Title', 'Captcha code:'),
                    'form'              => $this,
                    'checkRequirements' => array
                    (
                        'isFilledIn'        => true,
                        'hasLength'         => SiteConfigExtension::SpamCheck_numberOfCharsInCaptcha(),
                        'PtCaptchaInput'    => true
                    )
                );
                $this->formFields['PtCaptchaImageField'] = array(
                    'type'      => PtCaptchaImageField::class,
                    'title'     => _t(PtCaptchaImageField::class . '.Title', 'Enter this captcha code in the following field please:'),
                    'form'      => $this,
                    'maxLength' => SiteConfigExtension::SpamCheck_numberOfCharsInCaptcha(),
                );
            }
        }
    }

    /**
     * Set spam check for a form
     *
     * @param string $formName The name of the form that should use the spam check-
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 2012-12-11
     */
    public static function useSpamCheckFor($formName) {
        self::$useSpamCheck[$formName] = true;
    }
    
    /**
     * Returns whether to use a custom tabindex order for the form
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.02.2013
     */
    public function useCustomTabIndex() {
        return $this->getUseCustomTabIndex();
    }
    
    /**
     * Sets whether to use a custom tabindex order for the form
     * 
     * @param bool $useCustomTabIndex Set to true to use a custom tabindex order for the formfields
     * 
     * @return void
     */
    public function setUseCustomTabIndex($useCustomTabIndex) {
        $this->useCustomTabIndex = $useCustomTabIndex;
    }
    
    /**
     * Returns whether to use a custom tabindex order for the form
     * 
     * @return bool
     */
    public function getUseCustomTabIndex() {
        return $this->useCustomTabIndex;
    }

    /**
     * defines a new message for the form
     *
     * @param string $message the message's text
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 13.01.2014
     */
    public function addMessage($message) {
        $this->messages[] = new ArrayData(array('message' => $message));
    }

    /**
     * Returns the messages to loop in template.
     *
     * @return ArrayList
     */
    public function getMessagesForTemplate() {
        if (is_null($this->messagesForTemplate)) {
            $this->messagesForTemplate = new ArrayList();
            foreach ($this->messages as $message) {
                $this->messagesForTemplate->add($message);
            }
        }
        return $this->messagesForTemplate;
    }

    /**
     * Returns the whether there are messages to loop in template.
     *
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 28.01.2014
     */
    public function HasMessagesForTemplate() {
        return $this->getMessagesForTemplate()->count() > 0;
    }

    /**
     * Defines a new message for a form field
     *
     * @param string  $fieldName   The name of the field
     * @param string  $message     The message's text
     * @param string  $messageType The message type (not used)
     * @param boolean $escapeHtml  Escape HTML (not used)
     *
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 14.07.2014
     */
    public function addErrorMessage($fieldName, $message, $messageType = '', $escapeHtml = true) {
        $definition = $this->getFormFieldDefinition($fieldName);
        $fieldTitle = $fieldName;
        if ($definition !== false) {
            $fieldTitle = $definition['title'];
        }
        $this->errorMessages[$fieldName] = array(
            'message'   => $message,
            'fieldname' => $fieldTitle,
            $fieldName  => array(
                'message' => $message
            )
        );
    }

    /**
     * Returns the CustomHtmlForm object with the given identifier; if it's not
     * found a boolean false is returned.
     *
     * @param string $formIdentifier The identifier of the form
     *
     * @return CustomHtmlForm|bool
     */
    public function getRegisteredCustomHtmlForm($formIdentifier) {
        $formObj = false;

        if (isset($this->registeredCustomHtmlForms[$formIdentifier])) {
            $formObj = $this->registeredCustomHtmlForms[$formIdentifier];
        }

        return $formObj;
    }
    
    /**
     * Returns a registered handler for the given field type if available.
     *
     * @param string $fieldType The field type
     *
     * @return mixed
     */
    public static function getRegisteredFormFieldHandlerForType($fieldType) {
        if (array_key_exists($fieldType, self::$registeredFormFieldHandlers)) {
            return self::$registeredFormFieldHandlers[$fieldType];
        }
        
        return false;
    }

    /**
     * Return a CSRF-preventing ID to insert into a form.
     *
     * @return string
     */
    public function getSecurityID() {
        return $this->getSecurityToken()->getValue();
    }

    /**
     * passes the meta data for form submission to the template;
     * called by the template
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.02.2013
     */
    public function CustomHtmlFormMetadata() {
        $metadata = '';

        // form name
        $metadata .= $this->Fields()->dataFieldByName('CustomHtmlFormName')->Field();
        
        // SecurityID
        if ($this->securityTokenEnabled) {
            if ($this->Fields()->dataFieldByName('SecurityID')) {
                $metadata .= $this->Fields()->dataFieldByName('SecurityID')->Field();
            } else {
                $metadata .= sprintf(
                    '<input type="hidden" id="%s" name="SecurityID" value="%s" />',
                    $this->FormName().'_SecurityID',
                    $this->getSecurityID()
                );
            }
        }
        
        // custom data fields
        if (!empty($this->customParameters)) {
            foreach ($this->customParameters as $customParameterKey => $customParameterValue) {
                $metadata .= $this->Fields()->dataFieldByName($customParameterKey)->Field();
            }
        }

        return $metadata;
    }

    /**
     * does a group with the passed name exist?
     * 
     * @param string $groupName the group's name
     *
     * @return bool
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 13.01.2011
     */
    public function CustomHtmlFormFieldGroupExists($groupName) {
        $groupExists = false;
        
        if (isset($this->fieldGroups[$groupName])) {
            $groupExists = true;
        }
        
        return $groupExists;
    }
    
    /**
     * returns HTML code for a field group
     *
     * @param string $groupName group's name
     * @param string $template  name of the template that should be used for all
     *                          fields of the group
     * @param mixed  $argument1 An optional argument
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 27.10.2010
     */
    public function CustomHtmlFormFieldsByGroup($groupName, $template = null, $argument1 = null) {
        $fieldGroup = new ArrayList();

        if ($this->extend('overwriteCustomHtmlFormFieldsByGroup', $groupName, $template, $fieldGroup, $argument1)) {
            return $fieldGroup;
        }

        if (!isset($this->fieldGroups[$groupName])) {
            return $fieldGroup;
        }

        foreach ($this->fieldGroups[$groupName] as $fieldName => $fieldDefinitions) {
            $fieldGroup->push(
                new ArrayData(
                    array(
                        'CustomHtmlFormField' => $this->CustomHtmlFormFieldByName($fieldName, $template)
                    )
                )
            );
        }

        return $fieldGroup;
    }

    /**
     * Returns all special fields if configured so as HTML string.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>,
     *         Sebastian Diel <sdiel@pixeltricks.de>
     * @since 02.07.2013
     */
    public function CustomHtmlFormSpecialFields() {
        $fields = '';
        
        $this->extend('onBeforeCustomHtmlFormSpecialFields', $fields);
        
        if (array_key_exists($this->class, self::$useSpamCheck)) {
            $fields .= $this->SpamCheckField();
        }
        
        $this->extend('updateCustomHtmlFormSpecialFields', $fields);

        $this->extend('onAfterCustomHtmlFormSpecialFields', $fields);
        
        return $fields;
    }

    /**
     * Returns the HTML Code for a spam check field.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 07.12.2012
     */
    public function SpamCheckField() {
        if (self::$use_google_recaptcha) {
            $field  = $this->CustomHtmlFormFieldByName('GoogleRecaptchaField');
        } else {
            $field  = $this->CustomHtmlFormFieldByName('PtCaptchaImageField');
            $field .= $this->CustomHtmlFormFieldByName('PtCaptchaInputField');
        }

        return $field;
    }

    /**
     * Returns the HTML code for the passed field; created with the standard
     * template for fields
     *
     * @param string $fieldName the field's name
     * @param string $template  optional; path to template snippet, relative to
     *                  the site root; by dot notation search in modul directory
     *                  can be set:
     *                  "module.myTemplate" searches in the modul directory
     *                  "modul/templates" for the template "myTemplate.ss
     *
     * @return string
     *
     * @throws Exception
     * @author Sascha Koehler <skoehler@pixeltricks.de>,
     *         Sebastian Diel <sdiel@pixeltricks.de>
     * @since 04.07.2013
     */
    public function CustomHtmlFormFieldByName($fieldName, $template = null) {
        $fieldReference = '';
        $templatePath   = '';
        $output         = '';
        $templateFound  = false;

        foreach ($this->fieldGroups as $groupName => $groupFields) {
            if (isset($groupFields[$fieldName])) {
                $fieldReference = $groupFields[$fieldName];
                break;
            }
        }
        if ($fieldReference === '') {
            throw new Exception(
                sprintf('The field "%s" is called via template but not defiened on the form object.', $fieldName)
            );
        }

        // set the default template
        if (empty($template)) {
            $template = 'CustomHtmlFormField';
        }

        // sort the registered themes, so that the highest priority ones
        // are searched first.
        $registeredThemes = self::$registeredThemes;
        arsort($registeredThemes);

        // sort the registered modules, so that the highest priority ones
        // are searched first.
        $registeredModules = self::$registeredModules;
        arsort($registeredModules);

        // the paths inside modules that could contain templates
        $templateDirs = array(
            '/templates/',
            '/templates/Layout/',
            '/templates/forms/',
            '/templates/Forms/',
            '/templates/form_fields/',
        );
        
        global $project;
        foreach ($templateDirs as $templateDir) {
            $templatePath = $project . $templateDir . $template . '.ss';
            if (Director::fileExists($templatePath)) {
                $templateFound = true;
                break;
            }
        }
        if (!$templateFound) {
            // search the template in a variety of possible paths
            foreach ($registeredThemes as $themeName => $priority) {
                foreach ($templateDirs as $templateDir) {
                    $templatePath = 'themes/'.$themeName.$templateDir.$template.'.ss';

                    if (Director::fileExists($templatePath)) {
                        $templateFound = true;
                        break(2);
                    }
                }
            }

            if (!$templateFound) {
                foreach ($registeredModules as $moduleName => $priority) {
                    foreach ($templateDirs as $templateDir) {
                        $templatePath = $moduleName.$templateDir.$template.'.ss';
                        $themedPath   = 'themes/' . $templatePath;

                        if (Director::fileExists($templatePath)) {
                            break(2);
                        } elseif (Director::fileExists($themedPath)) {
                            $templatePath = $themedPath;
                            break(2);
                        }
                    }
                }
            }
        }

        if (!empty($templatePath)) {
            $templatePathRel    = '/'.$templatePath;
            $templatePathAbs    = Director::baseFolder().$templatePathRel;
            $viewableObj        = new ViewableData();

            if (isset($fieldReference['checkRequirements']) &&
                isset($fieldReference['checkRequirements']['isFilledIn']) &&
                $fieldReference['checkRequirements']['isFilledIn']) {

                $isRequiredField = true;
            } else {
                $isRequiredField = false;
            }

            $field = $this->SSformFields['fields']->dataFieldByName($fieldName);
            $placeholder = '';
            if (array_key_exists('placeholder', $fieldReference)) {
                $placeholder = $fieldReference['placeholder'];
            }
            $field->placeholder = $placeholder;
            $output = $viewableObj->customise(
                array(
                    'FormName'            => $this->name,
                    'FieldName'           => $fieldName,
                    'FieldValue'          => $field->Value(),
                    'Label'               => isset($fieldReference['title']) ? $fieldReference['title'] : '',
                    'errorMessage'        => isset($this->errorMessages[$fieldName]) ?  $this->errorMessages[$fieldName] : '',
                    'FieldTag'            => $field,
                    'FieldHolder'         => $field->FieldHolder(),
                    'FieldID'             => $field->id(),
                    'FieldObject'         => $field,
                    'Parent'              => $this,
                    'isRequiredField'     => $isRequiredField,
                    'RequiredFieldMarker' => $this->RequiredFieldMarker($isRequiredField),
                    'FieldDescription'    => isset($fieldReference['description']) ? $fieldReference['description'] : '',
                    'FieldDefinition'     => new ArrayData($fieldReference),
                )
            )->renderWith($templatePathAbs);
        } else {
            $output = 'Template '.$template.' could not be found!';
        }

        return $output;
    }
    
    /**
     * Returns the custom data field with the given name.
     * 
     * @param string $name Name of the field to get.
     * 
     * @return FormField
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 08.07.2013
     */
    public function customDataFieldByName($name) {
        return $this->SSformFields['fields']->dataFieldByName($name);
    }

    /**
     * returns error message as HTML text
     *
     * @param string $template optional; rendering template's name
     *
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 13.01.2015
     */
    public function CustomHtmlFormErrorMessages($template = 'CustomHtmlFormErrorMessages') {
        global $project;
        
        $data = array(
            'errorMessages' => new ArrayList($this->errorMessages),
            'messages'      => new ArrayList($this->messages)
        );
        $templateBaseDir = '';
        foreach (SSViewer::get_themes() as $theme) {
            $templateBaseDir = THEMES_DIR . '/' . $theme;
        }

        $templates = array(
            $project . '/templates/forms/' . $template . '.ss',
            $project . '/templates/Layout/' . $template . '.ss',
            $templateBaseDir . '_customhtmlform/templates/forms/' . $template . '.ss',
            $templateBaseDir . '_customhtmlform/templates/Layout/' . $template . '.ss',
            $templateBaseDir . '/templates/forms/' . $template . '.ss',
            $templateBaseDir . '/templates/Layout/' . $template . '.ss',
            'customhtmlform/templates/forms/' . $template . '.ss',
        );
        foreach ($templates as $templatePath) {
            if (Director::fileExists($templatePath)) {
                $templatePathRel = '/' . $templatePath;
                break;
            }
        }

        $templatePathAbs = Director::baseFolder() . $templatePathRel;
        $viewableObj     = new ViewableData();
        $output          = $viewableObj->customise(
            $data
        )->renderWith($templatePathAbs);

        return $output;
    }
    
    /**
     * Indicates wether there are error messages.
     *
     * @return boolean
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 01.12.2011
     */
    public function HasCustomHtmlFormErrorMessages() {
        $hasMessages = false;
        
        if (count($this->errorMessages) + count($this->messages) > 0) {
            $hasMessages = true;
        }
        
        return $hasMessages;
    }

    /**
     * returns the form's name
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 25.10.2010
     */
    public function FormName() {
        if ($this->htmlID) {
            return $this->htmlID;
        } else {
            return $this->name;
        }
    }

    /**
     * Returns HTML markup for the requested form
     *
     * @param string $formIdentifier   unique form name which can be called via template
     * @param Object $renderWithObject object array; in those objects context the forms shall be created
     *
     * @return CustomHtmlForm
     *
     * @throws Exception
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 08.04.2011
     */
    public function InsertCustomHtmlForm($formIdentifier = null, $renderWithObject = null) {
        if (is_null($formIdentifier)) {
            $formToRender = $this;
        } else {
            if (!isset($this->registeredCustomHtmlForms[$formIdentifier])) {
                throw new Exception(
                    sprintf(
                        'The requested CustomHtmlForm "%s" is not registered.',
                        $formIdentifier
                    )
                );
            } else {
                $formToRender = $this->registeredCustomHtmlForms[$formIdentifier];
            }
        }

        // Inject controller
        $customFields = array(
            'Controller' => $this->controller
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

        $outputForm = $formToRender->customise($customFields)->renderWith(
            array(
                $formToRender->class,
            )
        );

        return $outputForm;
    }

    /**
     * Registers a form object
     *
     * @param string         $formIdentifier unique form name which can be called via template
     * @param CustomHtmlForm $formObj        The form object with field definitions and preocessing methods
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 08.04.2010
     */
    public function registerCustomHtmlForm($formIdentifier, CustomHtmlForm $formObj) {
        $this->registeredCustomHtmlForms[$formIdentifier] = $formObj;
    }
    
    /**
     * Registers a form field handler.
     *
     * @param string $fieldType The field type this handler is responsible for.
     * @param mixed  $handler   The handler object itself.
     *
     * @return void
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 06.12.2011
     */
    public static function registerFormFieldHandler($fieldType, $handler) {
        if (!array_key_exists($fieldType, self::$registeredFormFieldHandlers)) {
            self::$registeredFormFieldHandlers[$fieldType] = $handler;
        }
    }
    
    /**
     * Disable the security token.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 22.07.2011
     */
    public function setSecurityTokenDisabled() {
        $this->securityTokenEnabled = false;
    }

    /**
     * Deactivate Validation for the given field.
     *
     * @param string $fieldName The name of the field
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 24.01.2014
     */
    public function deactivateValidationFor($fieldName) {
        if (!in_array($fieldName, $this->noValidationFields)) {
            $this->noValidationFields[] = $fieldName;
        }
    }

    /**
     * Activate Validation for the given field.
     *
     * @param string $fieldName The name of the field
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 24.01.2014
     */
    public function activateValidationFor($fieldName) {
        if (in_array($fieldName, $this->noValidationFields)) {
            for ($index = 0; $index < count($this->noValidationFields); $index++) {
                if ($fieldName == $this->noValidationFields[$index]) {
                    break;
                }
            }
            unset($this->noValidationFields[$index]);
            $this->noValidationFields = array_values($this->noValidationFields);
        }
    }

    /**
     * Returns the value for the given preference.
     *
     * @param string $preferenceName The name of the preference
     *
     * @return mixed
     */
    public function getPreference($preferenceName) {
        if (isset($this->preferences[$preferenceName])) {
            $result = $this->preferences[$preferenceName];
        } else {
            $result = $this->basePreferences[$preferenceName];
        }

        return $result;
    }

    /**
     * returns submit button title
     *
     * @return string
     */
    protected function getSubmitButtontitle() {
        return $this->getPreference('submitButtonTitle');
    }

    /**
     * returns submit button title
     *
     * @return string
     */
    protected function getSubmitButtonToolTip() {
        $toolTip = $this->getPreference('submitButtonToolTip');

        if (empty($toolTip)) {
            $toolTip = $this->getSubmitButtontitle();
        }

        return $toolTip;
    }

    /**
     * Indicates wether the shoppingcart modules should be loaded.
     *
     * @return array
     */
    public function getLoadShoppingCartModules() {
        return $this->getPreference('loadShoppingcartModules');
    }

    /**
     * Indicates wether the shoppingcart forms should be drawn.
     *
     * @return array
     */
    public function getCreateShoppingCartForms() {
        return $this->getPreference('createShoppingcartForms');
    }

    /**
     * is JS validation defined?
     *
     * @return bool
     */
    protected function getDoJsValidation() {
        return $this->getPreference('doJsValidation');
    }

    /**
     * Should the form scroll to the first field after validation?
     *
     * @return bool
     */
    protected function getDoJsValidationScrolling() {
        return $this->getPreference('doJsValidationScrolling');
    }

    /**
     * Should the form fields be filled with submitted values from the request object?
     *
     * @return bool
     */
    protected function getFillInRequestValues() {
        return $this->getPreference('fillInRequestValues');
    }

    /**
     * Should JS validation messages be shown?
     *
     * @return bool
     */
    protected function getShowJsValidationErrorMessages() {
        return $this->getPreference('showJsValidationErrorMessages');
    }
    
    /**
     * returns the submit button's title
     *
     * @return string
     */
    protected function getSubmitAction() {
        return $this->getPreference('submitAction');
    }

    /**
     * adds a field to the group
     *
     * @param string $groupName        the group's name
     * @param string $fieldName        the field's name
     * @param array  $fieldDefinitions the field definitions
     *
     * @return void
     *
     * @throws Exception
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 27.10.2010
     */
    protected function addFieldToGroup($groupName, $fieldName, $fieldDefinitions) {
        // create group if it does not exist yet
        if (!isset($this->fieldGroups[$groupName])) {
            $this->fieldGroups[$groupName] = array();
        }

        // check if a field with the same name exists already in the group
        if (isset($this->fieldGroups[$groupName][$fieldName])) {
            throw new Exception(
                sprintf(
                    'In the CustomHtmlForm fieldgroup "%s" the field "%s" is already defined.',
                    $groupName,
                    $fieldName
                )
            );
        }

        $this->fieldGroups[$groupName][$fieldName] = $fieldDefinitions;
    }

    /**
     * returns the controller object that should be used
     *
     * @param ContentController $controller  the calling controller
     * @param array             $preferences optional preferences
     *
     * @return ContentController
     */
    protected function getFormController($controller, $preferences) {
        if (isset($preferences['controller'])) {
            return $preferences['controller'];
        } else {
            return $controller;
        }
    }

    /**
     * Returns whether there was an successful submission or not.
     *
     * @return bool 
     */
    public function getSubmitSuccess() {
        return $this->submitSuccess;
    }

    /**
     * Sets whether there was an successful submission or not.
     *
     * @param bool $submitSuccess Submission succeeded or not?
     *
     * @return void 
     */
    public function setSubmitSuccess($submitSuccess) {
        $this->submitSuccess = $submitSuccess;
    }
    
    /**
     * Sets the custom parameters.
     * The custom parameters will be inserted as hidden fields.
     * Expected is a key value pair of the hidden fields name and its value
     * 
     * @param array $customParameters Custom parameters
     * 
     * @return void
     */
    public function setCustomParameters($customParameters) {
        $this->customParameters = $customParameters;
    }
    
    /**
     * Returns the custom parameters.
     * The custom parameters will be inserted as hidden fields.
     * Returned is a key value pair of the hidden fields name and its value
     * 
     * @return array
     */
    public function getCustomParameters() {
        return $this->customParameters;
    }
    
    /**
     * Sets the custom parameter with the given name to the given value.
     * The custom parameters will be inserted as hidden fields.
     * 
     * @param string $customParameterName  Name of the parameter to set value for
     * @param string $customParameterValue Value to set
     * 
     * @return void
     */
    public function setCustomParameter($customParameterName, $customParameterValue) {
        $this->customParameters[$customParameterName] = $customParameterValue;
    }

    /**
     * Builds the cache key of this form
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.11.2014
     */
    public function buildCacheKey() {
        $customParameters       = $this->getCustomParameters();
        $request                = $this->controller->getRequest();
        $requestString          = '';
        $formFieldString        = '';
        $formFields             = $this->getFormFields();
        $this->cacheKey         = $this->name;
        if (count($customParameters) > 0) {
            $customParameterString  = '';
            foreach ($customParameters as $parameterName => $parameterValue) {
                $customParameterString .= $parameterName . ':' . $parameterValue . ';';
            }
            $this->cacheKey .= sha1($customParameterString);
        }
        
        if (!is_null($request)) {
            foreach ($formFields as $fieldName => $fieldDefinition) {
                $this->addRequiredFieldParams($fieldDefinition, $fieldDefinition);
                $requestString   .= $fieldName . ':' . $request[$fieldName] . ';';
                $fieldDefinitionValue = $fieldDefinition['value'];
                if (is_string($fieldDefinitionValue)) {
                    $formFieldString .= $fieldName . ':' . $fieldDefinitionValue . ';';
                } elseif (is_array($fieldDefinitionValue)) {
                    $formFieldString .= $fieldName . ':' . implode('-', $fieldDefinitionValue) . ';';
                }
            }
        }
        
        if (class_exists('Translatable')) {
            $requestString .= '_' . Translatable::get_current_locale();
        }

        $this->cacheKey .= sha1($requestString);
        $this->cacheKey .= sha1($formFieldString);
        $this->cacheKey .= md5($formFieldString);
        if (SecurityToken::is_enabled()) {
            $this->cacheKey .= $this->getSecurityID();
        }
        if ($this->hasCacheKeyExtension()) {
            $this->cacheKey .= $this->getCacheKeyExtension();
        }
        $this->cacheKey = str_replace(array('{','}','(',')','/','\\','@',':'), '-', $this->cacheKey);
    }

    /**
     * Returns the cache key of this form.
     * The cache key will be build if not exists.
     * 
     * @return string
     */
    public function getCacheKey() {
        if (is_null($this->cacheKey)) {
            $this->buildCacheKey();
        }
        return $this->cacheKey;
    }

    /**
     * Returns the cache key extension of this form.
     * 
     * @return string
     */
    public function getCacheKeyExtension() {
        return $this->cacheKeyExtension;
    }

    /**
     * Sets the cache key extension of this form.
     * 
     * @param string $cacheKeyExtension Cache key extension
     * 
     * @return void
     */
    public function setCacheKeyExtension($cacheKeyExtension) {
        $this->cacheKeyExtension = $cacheKeyExtension;
    }

    /**
     * Returns the cache key extension of this form.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.11.2012
     */
    public function hasCacheKeyExtension() {
        $hasCacheKeyExtension   = false;
        $cacheKeyExtension      = $this->getCacheKeyExtension();
        if (!empty($cacheKeyExtension)) {
            $hasCacheKeyExtension = true;
        }
        return $hasCacheKeyExtension;
    }

    /**
     * Returns the Cache object for CustomHtmlForm
     * 
     * @return CacheInterface
     */
    public static function getCache() {
        self::$cache = Injector::inst()->get(CacheInterface::class . '.CustomHtmlForm');
        return self::$cache;
    }

    /**
     * Adds a rendered form output to the cache
     * 
     * @param string $output Output to cache
     * 
     * @return void
     */
    public function setCachedFormOutput($output) {
        if (self::$cache_enabled &&
            $this->excludeFromCache === false) {
            $cache = self::getCache();
            $cache->set($this->getCacheKey(), $output);
        }
    }
    
    /**
     * Returns a cached form output
     * 
     * @return string
     */
    public function getCachedFormOutput() {
        $cachedFormOutput = '';
        if (self::$cache_enabled &&
            $this->excludeFromCache === false) {
            $cache = self::getCache();
            $cachedFormOutput = $cache->get($this->getCacheKey());
        }
        return $cachedFormOutput;
    }
    
    /**
     * Disables the CustomHtmlForm file cache
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2013
     */
    public static function disableCache() {
        self::$cache_enabled = false;
    }
    
    /**
     * Enables the CustomHtmlForm file cache
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2013
     */
    public static function enableCache() {
        self::$cache_enabled = true;
    }
    
    /**
     * Creates and returns a field definition.
     * 
     * @param string $type                   Type
     * @param string $title                  Title
     * @param mixed  $value                  Value
     * @param bool   $isFilledIn             Field needs to be filled in?
     * @param array  $additionalRequirements Additional requirements
     * @param string $selectedValue          Selected value
     * @param array  $additionalDefinitions  Additional definitions
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 17.11.2014
     */
    public function createFieldDefinition($type, $title, $value, $isFilledIn = false, $additionalRequirements = array(), $selectedValue = null, $additionalDefinitions = array()) {
        $definition = array(
            'type'              => $type,
            'title'             => $title,
            'value'             => $value,
            'checkRequirements' => array(),
        );
        if ($isFilledIn) {
            $definition['checkRequirements']['isFilledIn'] = true;
        }
        if (!is_null($selectedValue)) {
            $definition['selectedValue'] = $selectedValue;
        }
        foreach ($additionalRequirements as $requirement => $requirementDefinition) {
            $definition['checkRequirements'][$requirement] = $requirementDefinition;
        }
        foreach ($additionalDefinitions as $additionalDefinitionKey => $additionalDefinitionValue) {
            $definition[$additionalDefinitionKey] = $additionalDefinitionValue;
        }
        
        return $definition;
    }
    
    /**
     * Creates and returns a HiddenField definition.
     * 
     * @param mixed $value                  Value
     * @param bool  $isFilledIn             Field needs to be filled in?
     * @param array $additionalRequirements Additional requirements
     * @param array $additionalDefinitions  Additional definitions
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 19.11.2014
     */
    public function createHiddenFieldDefinition($value, $isFilledIn = false, $additionalRequirements = array(), $additionalDefinitions = array()) {
        return $this->createFieldDefinition(HiddenField::class, '', $value, $isFilledIn, $additionalRequirements, null, $additionalDefinitions);
    }
    
    /**
     * Creates and returns a TextField definition.
     * 
     * @param string $title                  Title
     * @param mixed  $value                  Value
     * @param bool   $isFilledIn             Field needs to be filled in?
     * @param array  $additionalRequirements Additional requirements
     * @param string $selectedValue          Selected value
     * @param array  $additionalDefinitions  Additional definitions
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 31.07.2014
     */
    public function createTextFieldDefinition($title, $value, $isFilledIn = false, $additionalRequirements = array(), $selectedValue = null, $additionalDefinitions = array()) {
        return $this->createFieldDefinition(TextField::class, $title, $value, $isFilledIn, $additionalRequirements, $selectedValue, $additionalDefinitions);
    }
    
    /**
     * Creates and returns a TextareaField definition.
     * 
     * @param string $title                  Title
     * @param mixed  $value                  Value
     * @param bool   $isFilledIn             Field needs to be filled in?
     * @param int    $rows                   Row count
     * @param int    $cols                   Column count
     * @param array  $additionalRequirements Additional requirements
     * @param string $selectedValue          Selected value
     * @param array  $additionalDefinitions  Additional definitions
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 31.07.2014
     */
    public function createTextareaFieldDefinition($title, $value, $isFilledIn = false, $rows = null, $cols = null, $additionalRequirements = array(), $selectedValue = null, $additionalDefinitions = array()) {
        if (!is_null($rows)) {
            $additionalDefinitions['rows'] = $rows;
        }
        if (!is_null($cols)) {
            $additionalDefinitions['cols'] = $cols;
        }
        return $this->createFieldDefinition(TextareaField::class, $title, $value, $isFilledIn, $additionalRequirements, $selectedValue, $additionalDefinitions);
    }
    
    /**
     * Creates and returns a CheckboxField definition.
     * 
     * @param string $title                  Title
     * @param mixed  $value                  Value
     * @param bool   $isFilledIn             Field needs to be filled in?
     * @param array  $additionalRequirements Additional requirements
     * @param string $selectedValue          Selected value
     * @param array  $additionalDefinitions  Additional definitions
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 31.07.2014
     */
    public function createCheckboxFieldDefinition($title, $value, $isFilledIn = false, $additionalRequirements = array(), $selectedValue = null, $additionalDefinitions = array()) {
        return $this->createFieldDefinition(CheckboxField::class, $title, $value, $isFilledIn, $additionalRequirements, $selectedValue, $additionalDefinitions);
    }
    
    /**
     * Creates and returns a DropdownField definition.
     * 
     * @param string $title                  Title
     * @param mixed  $value                  Value
     * @param string $selectedValue          Selected value
     * @param bool   $isFilledIn             Field needs to be filled in?
     * @param array  $additionalRequirements Additional requirements
     * @param array  $additionalDefinitions  Additional definitions
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 31.07.2014
     */
    public function createDropdownFieldDefinition($title, $value, $selectedValue = null, $isFilledIn = false, $additionalRequirements = array(), $additionalDefinitions = array()) {
        return $this->createFieldDefinition(DropdownField::class, $title, $value, $isFilledIn, $additionalRequirements, $selectedValue, $additionalDefinitions);
    }
    
    /**
     * Creates and returns a TreeDropdownField definition.
     * 
     * @param string $title                  Title
     * @param string $sourceObject           Name of the source object
     * @param int    $treeBaseID             ID of the trees root object
     * @param array  $value                  Selected value(s)
     * @param string $keyField               Key field
     * @param string $labelField             Label field
     * @param bool   $showSearch             Show search?
     * @param bool   $isFilledIn             Field needs to be filled in?
     * @param array  $additionalRequirements Additional requirements
     * @param array  $additionalDefinitions  Additional definitions
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 31.07.2014
     */
    public function createTreeDropdownFieldDefinition($title, $sourceObject, $treeBaseID = 0, $value = null, $keyField = 'ID', $labelField = 'TreeTitle', $showSearch = true, $isFilledIn = false, $additionalRequirements = array(), $additionalDefinitions = array()) {
        $additionalDefinitions['sourceObject'] = $sourceObject;
        $additionalDefinitions['labelField']   = $labelField;
        $additionalDefinitions['showSearch']   = $showSearch;
        $additionalDefinitions['treeBaseID']   = $treeBaseID;
        return $this->createFieldDefinition(TreeDropdownField::class, $title, $value, $isFilledIn, $additionalRequirements, $keyField, $additionalDefinitions);
    }
    
    /**
     * Creates and returns a TreeMultiselectField definition.
     * 
     * @param string $title                  Title
     * @param string $sourceObject           Name of the source object
     * @param int    $treeBaseID             ID of the trees root object
     * @param array  $value                  Selected value(s)
     * @param string $keyField               Key field
     * @param string $labelField             Label field
     * @param bool   $showSearch             Show search?
     * @param bool   $isFilledIn             Field needs to be filled in?
     * @param array  $additionalRequirements Additional requirements
     * @param array  $additionalDefinitions  Additional definitions
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 31.07.2014
     */
    public function createTreeMultiselectFieldDefinition($title, $sourceObject, $treeBaseID = 0, $value = null, $keyField = 'ID', $labelField = 'TreeTitle', $showSearch = true, $isFilledIn = false, $additionalRequirements = array(), $additionalDefinitions = array()) {
        $additionalDefinitions['sourceObject'] = $sourceObject;
        $additionalDefinitions['labelField']   = $labelField;
        $additionalDefinitions['showSearch']   = $showSearch;
        $additionalDefinitions['treeBaseID']   = $treeBaseID;
        return $this->createFieldDefinition(TreeMultiselectField::class, $title, $value, $isFilledIn, $additionalRequirements, $keyField, $additionalDefinitions);
    }
    
    /**
     * Creates and returns a MoneyField definition.
     * 
     * @param string $title                  Title
     * @param mixed  $value                  Value
     * @param bool   $isFilledIn             Field needs to be filled in?
     * @param array  $additionalRequirements Additional requirements
     * @param string $selectedValue          Selected value
     * @param array  $additionalDefinitions  Additional definitions
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 31.07.2014
     */
    public function createMoneyFieldDefinition($title, $value, $isFilledIn = false, $additionalRequirements = array(), $selectedValue = null, $additionalDefinitions = array()) {
        $fieldClass = MoneyField::class;
        if (class_exists('SilverCart\\Forms\\FormFields\\MoneyField')) {
            $fieldClass = \SilverCart\Forms\FormFields\MoneyField::class;
        }
        return $this->createFieldDefinition($fieldClass, $title, $value, $isFilledIn, $additionalRequirements, $selectedValue, $additionalDefinitions);
    }
    
    /**
     * Creates and returns a CheckboxSetField definition.
     * 
     * @param string $title                  Title
     * @param mixed  $value                  Value
     * @param string $selectedValue          Selected value
     * @param bool   $isFilledIn             Field needs to be filled in?
     * @param array  $additionalRequirements Additional requirements
     * @param array  $additionalDefinitions  Additional definitions
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 01.10.2014
     */
    public function createCheckboxSetFieldDefinition($title, $value, $selectedValue = null, $isFilledIn = false, $additionalRequirements = array(), $additionalDefinitions = array()) {
        return $this->createFieldDefinition(CheckboxSetField::class, $title, $value, $isFilledIn, $additionalRequirements, $selectedValue, $additionalDefinitions);
    }
}
