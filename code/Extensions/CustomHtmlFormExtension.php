<?php

namespace CustomHtmlForm\Extensions;

/**
 * Interface for a DataExtension to decorate a CustomHtmlForm
 *
 * @package CustomHtmlForm
 * @subpackage Extensions
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 12.10.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
interface CustomHtmlFormExtension {
    
    /**
     * This method will be called instead of a CustomHtmlForms process method
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.03.2012
     */
    public function extendedProcess();
    
    /**
     * This method will be called after CustomHtmlForm's __construct().
     * 
     * @param \SilverStripe\CMS\Controllers\ContentController $controller  the calling controller instance
     * @param array                                           $params      optional parameters
     * @param array                                           $preferences optional preferences
     * @param bool                                            $barebone    defines if a form should only be instanciated or be used too
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.01.2014
     */
    public function onAfterConstruct($controller, $params, $preferences, $barebone);
    
    /**
     * Adds some custom markup to the CustomHtmlFormSpecialFields markup on 
     * after the default markup will be added.
     * 
     * @param string &$fieldsMarkup Fields markup to update
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 02.07.2013
     */
    public function onAfterCustomHtmlFormSpecialFields(&$fieldsMarkup);
    
    /**
     * This method will be called after CustomHtmlForm's default submitFailure.
     * You can manipulate the relevant data here.
     * 
     * @param \SilverStripe\Control\HTTPRequest &$data submit data
     * @param \SilverStripe\Forms\Form          &$form form object
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.11.2011
     */
    public function onAfterSubmitFailure(&$data, &$form);
    
    /**
     * This method will be called after CustomHtmlForm's default submitSuccess.
     * You can manipulate the relevant data here.
     * 
     * @param \SilverStripe\Control\HTTPRequest &$data     submit data
     * @param \SilverStripe\Forms\Form          &$form     form object
     * @param array                             &$formData secured form data
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.11.2011
     */
    public function onAfterSubmitSuccess(&$data, &$form, &$formData);
    
    /**
     * This method will be called before CustomHtmlForm's __construct().
     * 
     * @param \SilverStripe\CMS\Controllers\ContentController $controller  the calling controller instance
     * @param array                                           $params      optional parameters
     * @param array                                           $preferences optional preferences
     * @param bool                                            $barebone    defines if a form should only be instanciated or be used too
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.01.2014
     */
    public function onBeforeConstruct($controller, $params, $preferences, $barebone);
    
    /**
     * Adds some custom markup to the CustomHtmlFormSpecialFields markup on 
     * before the default markup will be added.
     * 
     * @param string &$fieldsMarkup Fields markup to update
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 02.07.2013
     */
    public function onBeforeCustomHtmlFormSpecialFields(&$fieldsMarkup);
    
    /**
     * This method will be called before CustomHtmlForm's default submit.
     * You can manipulate the relevant data here.
     * 
     * @param \SilverStripe\Control\HTTPRequest &$data submit data
     * @param \SilverStripe\Forms\Form          &$form form object
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.01.2014
     */
    public function onBeforeSubmit(&$data, &$form);
    
    /**
     * This method will be called before CustomHtmlForm's default submitFailure.
     * You can manipulate the relevant data here.
     * 
     * @param \SilverStripe\Control\HTTPRequest &$data submit data
     * @param \SilverStripe\Forms\Form          &$form form object
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.11.2011
     */
    public function onBeforeSubmitFailure(&$data, &$form);
    
    /**
     * This method will be called before CustomHtmlForm's default submitSuccess.
     * You can manipulate the relevant data here.
     * 
     * @param \SilverStripe\Control\HTTPRequest &$data     submit data
     * @param \SilverStripe\Forms\Form          &$form     form object
     * @param array                             &$formData secured form data
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.11.2011
     */
    public function onBeforeSubmitSuccess(&$data, &$form, &$formData);
    
    /**
     * Updates form fields by group.
     * 
     * @param string                      &$groupName Group name
     * @param string                      &$template  Template name
     * @param \SilverStripe\ORM\ArrayList $fieldGroup Field group
     * @param mixed                       &$argument1 Optional argument
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.10.2017
     */
    public function overwriteCustomHtmlFormFieldsByGroup(&$groupName, &$template, $fieldGroup, &$argument1);
    
    /**
     * This method will replace CustomHtmlForm's default submitFailure. It's
     * important that this method returns sth. to ensure that the default 
     * submitFailure won't be called. The return value should be a rendered 
     * template or sth. similar.
     * You can also trigger a direct or redirect and return what ever you want
     * (perhaps boolean true?).
     * 
     * @param \SilverStripe\Control\HTTPRequest &$data submit data
     * @param \SilverStripe\Forms\Form          &$form form object
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.11.2011
     */
    public function overwriteSubmitFailure(&$data, &$form);
    
    /**
     * This method will replace CustomHtmlForm's default submitSuccess. It's
     * important that this method returns sth. to ensure that the default 
     * submitSuccess won't be called. The return value should be a rendered 
     * template or sth. similar.
     * You can also trigger a direct or redirect and return what ever you want
     * (perhaps boolean true?).
     * 
     * @param \SilverStripe\Control\HTTPRequest &$data     submit data
     * @param \SilverStripe\Forms\Form          &$form     form object
     * @param array                             &$formData secured form data
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.11.2011
     */
    public function overwriteSubmitSuccess(&$data, &$form, &$formData);
    
    /**
     * Updates the special fields.
     * 
     * @param string &$fieldsMarkup Fields string to update.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.10.2017
     */
    public function updateCustomHtmlFormSpecialFields(&$fieldsMarkup);
    
    /**
     * This method is called before CustomHtmlForm requires the form fields. You 
     * can manipulate the default form fields here.
     * 
     * @param array &$formFields Form fields to manipulate
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.11.2011
     */
    public function updateFormFields(&$formFields);
    
    /**
     * This method is called before CustomHtmlForm set the preferences. You 
     * can manipulate the default preferences here.
     * 
     * @param array &$preferences Preferences to manipulate
     * 
     * @return bool
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 28.11.2011
     */
    public function updatePreferences(&$preferences);
    
    /**
     * Updates the submitted form data.
     * 
     * @param array                             &$formData Form data to update
     * @param \SilverStripe\Control\HTTPRequest $data      HTTP request data
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.10.2017
     */
    public function updateSubmittedFormData(&$formData, $data);
}