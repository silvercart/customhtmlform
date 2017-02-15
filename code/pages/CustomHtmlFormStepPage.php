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
 * page type that must be instanciated in the backend for a multi step form
 *
 * A base name (field "basename" for the form object and the template files of
 * the form must be defined
 *
 * @package CustomHtmlForm
 * @author Sascha Koehler <skoehler@pixeltricks.de>,
 *         Sebastian Diel <sdiel@pixeltricks.de>
 * @since 16.07.2013
 * @copyright 2013 pixeltricks GmbH
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class CustomHtmlFormStepPage extends Page {

    /**
     * Definiert die Datenfelder.
     *
     * @var array
     */
    public static $db = array(
        'basename'          => 'Varchar(255)',
        'showCancelLink'    => 'Boolean(1)',
        'cancelPageID'      => 'Varchar(255)'
    );
    
    /**
     * The defined value will be added to the step number to show in frontend
     * checkout navigation.
     *
     * @var int
     */
    public static $add_to_visible_step_nr = 0;
    
    /**
     * defines the CMS interface for $this
     * 
     * @return FieldList
     */
    public function getCMSFields() {

        $basenameField       = new TextField('basename', _t('CustomHtmlFormStepPage.BASE_NAME', 'base name for form object and template files: ', null, 'Basisname für Formular Objekt- und Templatedateien: '));
        $showCancelLinkField = new CheckboxField('showCancelLink', _t('CustomHtmlFormStepPage.SHOW_CANCEL', 'show cancel link'));
        $cancelLinkField     = new TreeDropdownField('cancelPageID', _t('CustomHtmlFormStepPage.CANCEL_TARGET', 'To which page should the cancel link direct: ', null, 'Auf welche Seite soll der Abbrechen-Link fuehren: '), 'SiteTree');

        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.MultistepConfiguration', $basenameField);
        $fields->addFieldToTab('Root.MultistepConfiguration', $showCancelLinkField);
        $fields->addFieldToTab('Root.MultistepConfiguration', $cancelLinkField);

        return $fields;
    }
}

/**
 * corresponding controller
 *
 * a base name (field "basename") must be specified
 *
 * @package CustomHtmlForm
 * @author Sascha Koehler <skoehler@pixeltricks.de>,
 *         Sebastian Diel <sdiel@pixeltricks.de>
 * @since 19.11.2013
 * @copyright 2013 pixeltricks GmbH
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class CustomHtmlFormStepPage_Controller extends Page_Controller {
    
    /**
     * Allowed URL actions
     *
     * @var array
     */
    private static $allowed_actions = array(
        'Cancel',
        'GotoStep',
        'NextStep',
        'PreviousStep',
    );

    /**
     * number of form objects; set by init()
     * 
     * @var integer
     */
    protected $nrOfSteps = -1;

    /**
     * Contains the list of steps as DataList.
     *
     * @var DataList
     */
    protected $stepList;

    /**
     * step to be shown if no step is specified
     *
     * @var integer
     */
    protected $defaultStartStep = 1;
    
    /**
     * number of current step
     * 
     * @var integer
     */
    protected $currentStep;

    /**
     * Contains the current form instance.
     * 
     * @var CustomHtmlForm
     */
    protected $currentFormInstance;
    
    /**
     * preferences for the step form
     * 
     *  templateDir: Directory to look for templates
     *
     * @return array
     */
    protected $basePreferences = array(
        'templateDir' => '',
    );

    /**
     * Contains a list of all steps, their titles, visibility, etc.
     *
     * @var array
     */
    protected $stepMapping = array();

    /**
     * Contains the output of a CustomHtmlForm object that was rendered by
     * this controller.
     *
     * @var string
     */
    protected $initOutput = '';

    /**
     * initializes the step form
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>,
     *         Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.04.2014
     */
    public function init() {
        $this->initialiseSessionData();
        $this->generateStepMapping();

        $this->nrOfSteps            = $this->getNumberOfSteps();
        $this->currentFormInstance  = $this->registerCurrentFormStep();
        
        $action = $this->request->param('Action');
        
        if (empty($action)) {
            $this->initOutput = $this->callMethodOnCurrentFormStep($this->currentFormInstance, 'init');
            $extended         = $this->callMethodOnCurrentFormStep($this->currentFormInstance, 'extendedProcess');
            if (!$extended) {
                $this->callMethodOnCurrentFormStep($this->currentFormInstance, 'process');
            }
        }
        
        parent::init();
    }

    /**
     * Returns the output of a form that was initialised by a
     * CustomHtmlFormStepPage object.
     *
     * @return string
     */
    public function getInitOutput() {
        return $this->initOutput;
    }

    /**
     * Returns the current form instance object
     *
     * @return CustomHtmlForm
     */
    public function getCurrentFormInstance() {
        return $this->currentFormInstance;
    }

    /**
     * returns the id of the current step
     *
     * @return int
     */
    public function getCurrentStep() {
        return Session::get('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.currentStep');
    }

    /**
     * returns the completed steps as a numeric array
     *
     * @return array
     */
    public function getCompletedSteps() {
        return Session::get('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.completedSteps');
    }

    /**
     * records a step to be completed
     *
     * @param int $stepNr id of the step; if not defined the current step will
     *                    be chosen
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 25.10.2010
     */
    public function addCompletedStep($stepNr = null) {

        if ($stepNr === null) {
            $stepNr = $this->getCurrentStep();
        }
        
        if (!$this->isStepCompleted($stepNr)) {
            $completedSteps = Session::get('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.completedSteps');
            $completedSteps[] = $stepNr;
            Session::set('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.completedSteps', $completedSteps);
            Session::save();
        }
    }
    
    /**
     * removes a completed step
     *
     * @param int  $stepNr             id of the step; if not defined the current
     *                                 step will be chosen
     * @param bool $includeHigherSteps set to true to remove all steps above the 
     *                                 given one, too
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 11.07.2011
     */
    public function removeCompletedStep($stepNr = null, $includeHigherSteps = false) {

        if ($stepNr === null) {
            $stepNr = $this->getCurrentStep();
        }
        
        if ($this->isStepCompleted($stepNr)) {
            foreach ($this->getCompletedSteps() as $key => $value) {
                if ($value == $stepNr ||
                    ($includeHigherSteps
                     && $value > $stepNr)) {
                    Session::clear('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.completedSteps.' . $key);
                    if (!$includeHigherSteps) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * call to the parent method; the corresponding parameters will be set
     * Ruft die gleichnamige Methode der Elternseite auf und erstellt den
     * passenden Parameter.
     *
     * @param string $formIdentifier the forms unique id
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 25.10.2010
     */
    public function InsertCustomHtmlForm($formIdentifier = null) {
        global $project;

        if ($formIdentifier === null) {
            $formIdentifier = '';
            if (array_key_exists($this->getCurrentStep(), $this->stepMapping)) {
                $formIdentifier = $this->stepMapping[$this->getCurrentStep()]['class'];
            }
        }

        $projectPrefix          = ucfirst($project);
        $extendedFormIdentifier = $projectPrefix.$formIdentifier;

        if (class_exists($extendedFormIdentifier)) {
            return parent::InsertCustomHtmlForm($extendedFormIdentifier);
        } else {
            return parent::InsertCustomHtmlForm($formIdentifier);
        }
    }

    /**
     * saves form data of the present step
     *
     * @param array $formData form data for this step
     * @param int   $stepNr   id of the step; if not defined the current step will
     *                        be chosen
     *
     * @return void
     */
    public function setStepData($formData, $stepNr = null) {

        if ($stepNr === null) {
            $stepNr = $this->getCurrentStep();
        }

        Session::set('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.steps.' . $stepNr, $formData);
        Session::save();
    }

    /**
     * returns the data of the current step as an associative array;
     * if there is no data false will be returned
     *
     * @param int $stepNr id of the step; if not defined the current step will
     *                    be chosen
     *
     * @return array|boolean
     */
    public function getStepData($stepNr = null) {

        if ($stepNr === null) {
            $stepNr = $this->getCurrentStep();
        }

        $steps = Session::get('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.steps.' . $stepNr);
        if (is_null($steps)) {
            return false;
        } else {
            return $steps;
        }
    }

    /**
     * returns all session data as an associative array
     *
     * @return array
     */
    public function getCombinedStepData() {

        $combinedData = array();

        for ($idx = $this->defaultStartStep; $idx <= $this->getNumberOfSteps(); $idx++) {
            $stepData = $this->getStepData($idx);

            if (is_array($stepData)) {
                $combinedData = array_merge($combinedData, $stepData);
            }
        }

        return $combinedData;
    }
    
    /**
     * Checks, whether the step data has changed.
     *
     * @param array  $formData  Sent form data
     * @param string $fieldName Field name to check
     * @param int    $stepNr    Number of step to check
     * 
     * @return boolean 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 11.07.2011
     */
    public function stepDataChanged($formData, $fieldName = '', $stepNr = null) {
        $changed = false;
        if ($stepNr === null) {
            $stepNr = $this->getCurrentStep();
        }
        if (!empty ($fieldName)) {
            
            $steps = Session::get('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.steps');
            if (array_key_exists($stepNr,    $steps) &&
                array_key_exists($fieldName, $steps[$stepNr])) {
                if ($formData[$fieldName] != $steps[$stepNr][$fieldName]) {
                    $changed = true;
                }
            }
        } else {
            foreach ($formData as $formFieldName => $formFieldValue) {
                if ($formFieldValue != Session::get('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.steps.' . $stepNr . '.' . $formFieldName)) {
                    $changed = true;
                    break;
                }
            }
        }
        return $changed;
    }

    /**
     * fills in the form fields with available session data
     * 
     * @param array &$fields Die zu befuellenden Felder
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 13.02.2013
     */
    public function fillFormFields(&$fields) {
        $formSessionData = $this->getStepData();

        foreach ($fields as $fieldName => $fieldData) {
            if (isset($formSessionData[$fieldName])) {
                if ($fieldData['type'] == 'OptionsetField' ||
                    $fieldData['type'] == 'DropdownField' ||
                    $fieldData['type'] == 'ListboxField' ||
                    in_array('OptionsetField', class_parents($fieldData['type'])) ||
                    in_array('DropdownField', class_parents($fieldData['type'])) ||
                    in_array('ListboxField', class_parents($fieldData['type']))) {
                    $valueParam = 'selectedValue';
                } else {
                    $valueParam = 'value';
                }
                
                $fields[$fieldName][$valueParam] = $formSessionData[$fieldName];
            }
        }
    }

    /**
     * returns the id of the previous step
     *
     * @return integer
     */
    public function getPreviousStep() {
        $currentStep = $this->getCurrentStep();

        return $currentStep - 1;
    }

    /**
     * returns the id of the next step
     *
     * @return integer
     */
    public function getNextStep() {
        $currentStep = $this->getCurrentStep();

        return $currentStep + 1;
    }

    /**
     * sets the id of the current step
     *
     * @param integer $stepNr id to be assigned
     *
     * @return void
     */
    public function setCurrentStep($stepNr) {
        Session::set('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.currentStep', $stepNr);
        Session::save();
    }

    /**
     * returns the link to the previous step
     *
     * @return string|boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 25.10.2010
     */
    public function CustomHtmlFormStepLinkPrev() {
        $link = false;

        if ($this->getPreviousStep() > 0 &&
            $this->isStepCompleted($this->getPreviousStep()) ) {

            $link = $this->Link('PreviousStep');
        }

        return $link;
    }

    /**
     * returns the link to the next step
     *
     * @return string|boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 25.10.2010
     */
    public function CustomHtmlFormStepLinkNext() {
        $link = false;

        if ($this->getNextStep() <= $this->getNumberOfSteps() &&
            $this->isStepCompleted()) {
            
            $link = $this->Link('NextStep');
        }

        return $link;
    }

    /**
     * returns the canel link
     *
     * @return string|boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 25.10.2010
     */
    public function CustomHtmlFormStepLinkCancel() {
        $link = false;

        if ($this->showCancelLink) {
            $link = $this->Link('Cancel');
        }

        return $link;
    }

    /**
     * increments the present step and reloads page
     * 
     * @param bool $withExit Redirect with exit?
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>,
     *         Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.07.2013
     */
    public function NextStep($withExit = true) {
        if ($this->getNextStep() <= $this->getNumberOfSteps()) {
            $this->setCurrentStep($this->getNextStep());
        }
        if ($withExit) {
            header('Location: ' . $this->Link(), true, 302);
            exit();
        } else {
            $redirected_to = $this->redirectedTo();
            if (empty($redirected_to)) {
                $this->redirect($this->Link(), 302);
            }
        }
    }

    /**
     * decrements the current step an reloads the page
     * 
     * @param bool $withExit Redirect with exit?
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>,
     *         Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.07.2013
     */
    public function PreviousStep($withExit = true) {
        if ($this->getPreviousStep() > 0 &&
            $this->isStepCompleted($this->getPreviousStep()) ) {

            $this->setCurrentStep($this->getPreviousStep());
        }
        if ($withExit) {
            header('Location: ' . $this->Link(), true, 302);
            exit();
        } else {
            $redirected_to = $this->redirectedTo();
            if (empty($redirected_to)) {
                $this->redirect($this->Link(), 302);
            }
        }
    }

    /**
     * jumps to the defined step if it is compleated and relods the page
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 07.12.2010
     */
    public function GotoStep() {
        $stepNr = $this->urlParams['ID'];

        if ($this->isPreviousStepCompleted($stepNr)) {
            $this->setCurrentStep($stepNr);
        }
        header('Location: '.$this->Link(), true, 302);
        exit();
    }

    /**
     * cancels all form data an redirects to the first step
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 25.10.2010
     */
    public function Cancel() {
        $this->setCurrentStep($this->defaultStartStep);
        $this->deleteSessionData(false);

        if ($this->cancelPageID) {
            $link = DataObject::get_by_id('Page', $this->cancelPageID)->Link();
        } else {
            $link = $this->Link();
        }
        header('Location: '.$link, true, 302);
        exit();
    }

    /**
     *deletes all step data from session
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 25.10.2010
     */
    public function deleteSessionData() {
        $customHtmlFormStep = Session::get('CustomHtmlFormStep');
        if (!is_null($customHtmlFormStep) &&
            is_array($customHtmlFormStep)) {

            if (array_key_exists($this->ClassName . $this->ID, $customHtmlFormStep)) {
                Session::clear('CustomHtmlFormStep.' . $this->ClassName . $this->ID);
            }
        }
    }

    /**
     * returns the defined steps title
     *
     * @param int $stepNr step index
     *
     * @return string
     */
    public function getStepName($stepNr) {
        $stepName = '';

        if (isset($this->stepMapping[$stepNr]) &&
            isset($this->stepMapping[$stepNr]['name'])) {
            $stepName = $this->stepMapping[$stepNr]['name'];
        }

        return $stepName;
    }

    /**
     * returns all steps as DataList
     *
     * @return DataList
     */
    public function getStepList() {
        
        if (empty($this->stepList)) {
            $stepList           = array();
            $nrOfVisibleSteps   = 0;

            for ($stepIdx = 1; $stepIdx <= $this->getNumberOfSteps(); $stepIdx++) {

                if ($stepIdx == $this->getCurrentStep()) {
                    $isCurrentStep = true;
                } else {
                    $isCurrentStep = false;
                }

                if (isset($this->stepMapping[$stepIdx])) {
                    $stepClassName = $this->stepMapping[$stepIdx]['class'];

                    $stepList['step'.$stepIdx] = new ArrayData(
                            array(
                                'title'           => $this->stepMapping[$stepIdx]['name'],
                                'stepIsVisible'   => $this->stepMapping[$stepIdx]['visibility'],
                                'stepIsCompleted' => $this->isStepCompleted($stepIdx),
                                'isCurrentStep'   => $isCurrentStep,
                                'stepNr'          => $stepIdx,
                                'visibleStepNr'   => $nrOfVisibleSteps + 1 + CustomHtmlFormStepPage::$add_to_visible_step_nr,
                                'step'            => new $stepClassName($this, null, null ,false)
                            )
                    );
                    
                    if ($this->stepMapping[$stepIdx]['visibility']) {
                        $nrOfVisibleSteps++;
                    }
                }
            }
            
            // Set the number of visible steps and a tag for the last visible step
            foreach ($stepList as $stepNr => $stepListEntry) {
                if ($stepListEntry->stepIsVisible &&
                    ($stepListEntry->stepNr - 1) == $nrOfVisibleSteps) {
                    
                    $stepList[$stepNr]->isLastVisibleStep = true;
                } else {
                    $stepList[$stepNr]->isLastVisibleStep = false;
                }
            }
            
            $this->stepList = new ArrayList($stepList);
        }
        
        return $this->stepList;
    }
    
    /**
     * Returnst whether the given step is visible.
     * 
     * @param int $stepIdx Step number
     * 
     * @return type
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 11.12.2014
     */
    public function isStepVisible($stepIdx) {
        $isStepVisible = false;
        if (array_key_exists($stepIdx, $this->stepMapping)) {
            $isStepVisible = $this->stepMapping[$stepIdx]['visibility'];
        }
        return $isStepVisible;
    }

    /**
     * Is the current or defined step completed?
     *
     * @param bool $stepIdx Optional: index of step to be checked
     *
     * @return bool
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 22.12.2010
     */
    public function isStepCompleted($stepIdx = false) {
        
        $completed = false;

        if ($stepIdx === false) {
            $stepIdx = $this->getCurrentStep();
        }

        if (in_array($stepIdx, $this->getCompletedSteps())) {
            $completed = true;
        }

        return $completed;
    }

    /**
     * has the previous step been completed?
     *
     * @param bool $stepIdx Optional: index of step to be checked
     *
     * @return bool
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 23.12.2010
     */
    public function isPreviousStepCompleted($stepIdx = false) {
        
        $completed = false;

        if ($stepIdx === false) {
            $stepIdx = $this->getCurrentStep() - 1;
        } else {
            $stepIdx -= 1;
        }

        if ($stepIdx === 0 ||
            in_array($stepIdx, $this->getCompletedSteps())) {
            $completed = true;
        }

        return $completed;
    }

    /**
     * registers form for the current step
     *
     * @return CustomHtmlForm
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 25.10.2010
     */
    protected function registerCurrentFormStep() {
        if (!array_key_exists($this->getCurrentStep(), $this->stepMapping)) {
            $this->generateStepMapping();
        }
        $formClassName = $this->stepMapping[$this->getCurrentStep()]['class'];
        $formInstance  = new $formClassName($this);
        $this->registerCustomHtmlForm($formClassName, $formInstance);
        
        return $formInstance;
    }

    /**
     * Calls a method on the given form instance.
     *
     * @param CustomHtmlForm $formInstance instance of current form
     * @param string         $methodName   The name of the method to call
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 29.07.2016
     */
    protected function callMethodOnCurrentFormStep($formInstance, $methodName) {
        if (!array_key_exists($this->getCurrentStep(), $this->stepMapping)) {
            $this->generateStepMapping();
        }
        $formClassName  = $this->stepMapping[$this->getCurrentStep()]['class'];
        $checkClass     = new ReflectionClass($formClassName);
        $output         = '';
        
        if ($checkClass->hasMethod('onBefore' . ucfirst($methodName))) {
            $output = $formInstance->$methodName();
        }
        if ($checkClass->hasMethod($methodName)) {
            $output = $formInstance->$methodName();
        }
        if ($checkClass->hasMethod('onAfter' . ucfirst($methodName))) {
            $formInstance->$methodName();
        }
        
        return $output;
    }

    /**
     * stes the data structure for the CustomHtmlFormStep in the session
     *
     * $_SESSION
     *   CustomHtmlFormStep
     *     {PageClass}{PageID}
     *       currentStep    => Int          Default: 1
     *       completedSteps => array()      Default: empty
     *       steps          => array()      Default: empty
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.12.2016
     */
    protected function initialiseSessionData() {
        Session::start();
        $customHtmlFormStep = Session::get('CustomHtmlFormStep');
        if (is_null($customHtmlFormStep)) {
            $customHtmlFormStep = array();
            Session::set('CustomHtmlFormStep', $customHtmlFormStep);
        }
        if (!array_key_exists($this->ClassName . $this->ID, $customHtmlFormStep) ||
            is_null($customHtmlFormStep[$this->ClassName . $this->ID])) {
            $customHtmlFormStep[$this->ClassName . $this->ID] = array();
            Session::set('CustomHtmlFormStep.' . $this->ClassName . $this->ID, array());
        }
        if (!array_key_exists('currentStep', $customHtmlFormStep[$this->ClassName . $this->ID]) ||
            is_null($customHtmlFormStep[$this->ClassName . $this->ID]['currentStep'])) {
            $this->setCurrentStep($this->defaultStartStep);
        }
        if (!array_key_exists('completedSteps', $customHtmlFormStep[$this->ClassName . $this->ID]) ||
            is_null($customHtmlFormStep[$this->ClassName . $this->ID]['completedSteps'])) {
            Session::set('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.completedSteps', array());
        }
        if (!array_key_exists('steps', $customHtmlFormStep[$this->ClassName . $this->ID]) ||
            is_null($customHtmlFormStep[$this->ClassName . $this->ID]['steps'])) {
            Session::set('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.steps', array());
        }
        if (!array_key_exists('stepDirectories', $customHtmlFormStep[$this->ClassName . $this->ID]) ||
            is_null($customHtmlFormStep[$this->ClassName . $this->ID]['stepDirectories'])) {
            Session::set('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.stepDirectories', array());
        }
        Session::save();
    }

    /**
     * returns the number of form steps
     * it will be determined like this:
     * - does a template with name scheme {basename}{step}.ss exist?
     * - does a class with name scheme {basename}{step}.php exist?
     * the steps get counted by a loop. if one of those two conditions not true
     * the loop will be aborted
     *
     * @return integer
     */
    protected function getNumberOfSteps() {
        return count($this->stepMapping);
    }

    /**
     * returns the number of visible form steps
     * 
     * @param int $add Add
     *
     * @return integer
     */
    protected function getNumberOfVisibleSteps($add = 0) {
        $count = 0;
        foreach ($this->stepMapping as $mapping) {
            $count = $mapping['visibility'] == true ? $count + 1 : $count;
        }
        return $count + $add;
    }

    /**
     * Generates a map of all steps with links, names, etc.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 04.04.2011
     */
    public function generateStepMapping() {
        // Get steps from theme- or moduledirectories
        $this->getStepsFromModuleOrThemeDirectory();

        // Get Steps from additional directories
        $this->getStepsFromAdditionalDirectories();
    }

    /**
     * Clears the step mapping variable.
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 15.02.2017
     */
    public function resetStepMapping() {
        $this->stepMapping = array();
        Session::clear('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.stepDirectories');
        Session::save();
        Session::set('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.stepDirectories', array());
        Session::save();
    }

    /**
     * Fill class variable $stepMapping with steps from the module- or
     * themedirectory.
     *
     * @return void
     */
    private function getStepsFromModuleOrThemeDirectory() {
        global $project;

        $themePath          = $this->getTemplateDir();
        $projectPrefix      = ucfirst($project);
        $increaseStep       = true;
        $stepIdx            = 1;
        $includedStepIdx    = 1;

        while ($increaseStep) {
            $includeStep            = true;
            $stepClassName          = $this->basename.$stepIdx;
            $extendedStepClassName  = $projectPrefix.$this->basename.$stepIdx;

            if (!Director::fileExists($themePath.$extendedStepClassName.'.ss') &&
                !Director::fileExists($themePath.$stepClassName.'.ss')) {
                $increaseStep = false;
                continue;
            }
            if (!class_exists($extendedStepClassName) &&
                !class_exists($stepClassName)) {
                $increaseStep = false;
                continue;
            }

            if (class_exists($extendedStepClassName)) {
                $stepClass = new $extendedStepClassName($this, null, null, true);
                $stepClassNameUnified = $extendedStepClassName;
            } else {
                $stepClass = new $stepClassName($this, null, null, true);
                $stepClassNameUnified = $stepClassName;
            }

            // Check if we have a conditional step and the condition is met
            if ($stepClass->getIsConditionalStep()) {
                if ($stepClass->hasMethod('isConditionForDisplayFulfilled')) {
                    if ($stepClass->isConditionForDisplayFulfilled() === false) {
                        $includeStep = false;
                    }
                }
            }

            if ($includeStep) {
                $this->stepMapping[$includedStepIdx] = array(
                    'name'        => $stepClass->getStepTitle(),
                    'class'       => $stepClassNameUnified,
                    'visibility'  => $stepClass->getStepIsVisible()
                );
                $includedStepIdx++;
            }
            
            $stepIdx++;
        }
    }

    /**
     * Fill class variable $stepMapping with steps from the additional
     * directories.
     *
     * @return void
     */
    private function getStepsFromAdditionalDirectories() {
        $mappingIdx         = count($this->stepMapping);
        $stepIdx            = $mappingIdx + 1;
        $includedStepIdx    = $stepIdx;
        
        $stepDirectories = Session::get('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.stepDirectories');
        if (is_null($stepDirectories)) {
            return false;
        }

        foreach ($stepDirectories as $directory) {
            $prefix = $this->basename;
            
            if (is_array($directory)) {
                list($directory, $definition) = each($directory);

                if (isset($definition['prefix'])) {
                    $prefix = $definition['prefix'];
                }
            }
            
            // ----------------------------------------------------------------
            // Get steps from each directory
            // ----------------------------------------------------------------
            $increaseStep   = true;
            $moduleStepIdx  = 1;

            while ($increaseStep) {
                $includeStep   = true;
                $stepClassName = $prefix.$moduleStepIdx;

                if (!Director::fileExists($directory.$stepClassName.'.ss')) {
                    $increaseStep = false;
                    continue;
                }
                if (!class_exists($stepClassName)) {
                    $increaseStep = false;
                    continue;
                }

                $stepClass = new $stepClassName($this, null, null, true);

                if ($includeStep) {
                    $this->stepMapping[$includedStepIdx] = array(
                        'name'        => $stepClass->getStepTitle(),
                        'class'       => $stepClassName,
                        'visibility'  => $stepClass->getStepIsVisible()
                    );
                    $includedStepIdx++;
                }
                $moduleStepIdx++;
                $stepIdx++;
            }
        }
    }

    /**
     * Register an additional directory where CustomHtmlFormStepForms are
     * located.
     *
     * @param string $templateDirectory The directory where the additional
     *                                  StepForm templates are located.
     * 
     * @return boolean Indicates wether the given directory has been added
     *                 to the directory list. Returns also true, if the
     *                 directory has already been in the list.
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 04.04.2011
     */
    public function registerStepDirectory($templateDirectory) {
        $stepDirectories = Session::get('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.stepDirectories');
        if (is_null($stepDirectories)) {
            $stepDirectories = array();
        }

        $stepDirectories[] = $templateDirectory;
        Session::set('CustomHtmlFormStep.' . $this->ClassName . $this->ID . '.stepDirectories', $stepDirectories);
        Session::save();

        return true;
    }
    
    /**
     * if the template directory is defined via preferences it will be returned
     *
     * @return string
     */
    protected function getTemplateDir() {
        $templateDir = '';
        
        if (isset($this->preferences['templateDir']) &&
            !empty($this->preferences['templateDir'])) {
            $templateDir = $this->preferences['templateDir'];
        } else {
            $templateDir = THEMES_DIR.'/'.SSViewer::current_theme().'/templates/Layout/';
        }
        
        return $templateDir;
    }
}
