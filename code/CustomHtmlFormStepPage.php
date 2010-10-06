<?php
/**
 * Der Seitentyp, der im CMS als Seite angelegt wird.
 * 
 * Es muss ein Basisname (Feld "basename") fuer die Formularobject- und
 * Templatedateien angegeben werden, die zur Darstellung der Schritte
 * verwendet werden sollen.
 */

class CustomHtmlFormStepPage extends Page {

    /**
     *  Definiert die Datenfelder.
     *
     * @var array
     */
    public static $db = array(
        'basename'          => 'Varchar(255)',
        'showCancelLink'    => 'Boolean(1)',
        'cancelPageID'      => 'Varchar(255)'
    );
    
    /**
     * Erweitert die Eingabemaske des Admins.
     * 
     * @return FieldSet
     */
    public function  getCMSFields() {

        $basenameField          = new TextField('basename', 'Basisname für Formular Objekt- und Templatedateien: ');
        $showCancelLinkField    = new CheckboxField('showCancelLink', 'Abbrechen Link anzeigen');
        $cancelLinkField        = new TreeDropdownField('cancelPageID', 'Auf welche Seite soll der Abbrechen-Link fuehren: ', 'SiteTree');

        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Content.MultistepConfiguration', $basenameField);
        $fields->addFieldToTab('Root.Content.MultistepConfiguration', $showCancelLinkField);
        $fields->addFieldToTab('Root.Content.MultistepConfiguration', $cancelLinkField);

        return $fields;
    }
}

class CustomHtmlFormStepPage_Controller extends Page_Controller {

    /**
     * Wird von der Init-Methode befuellt. Enthaelt die Anzahl der Formular-
     * objekte.
     * 
     * @var integer
     */
    protected $nrOfSteps = -1;

    /**
     * Der Schritt, der als erstes angezeigt werden soll, wenn noch kein
     * Schritt gesetzt worden ist.
     *
     * @var integer
     */
    protected $defaultStartStep = 1;
    
    /**
     * Enthaelt die Nummer des aktuellen Schritts.
     * 
     * @var integer
     */
    protected $currentStep;

    public function init() {
        $this->initialiseSessionData();
        $this->nrOfSteps = $this->getNumberOfSteps();
        $this->registerCurrentFormStep();

        parent::init();
    }

    /**
     * Liefert die Nummer des aktuellen Schritts zurueck.
     *
     * @return int
     */
    public function getCurrentStep() {
        return $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['currentStep'];
    }

    /**
     * Liefert die als abgeschlossen markierten Schritte als numerisches Array
     * zurueck.
     *
     * @return array
     */
    public function getCompletedSteps() {
        return $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['completedSteps'];
    }

    /**
     * Speichert einen Schritt als abgeschlossen.
     *
     * @param integer $stepNr: Nummer des aktuellen Schritts. Wenn weggelassen,
     *                     wird der aktuelle Schritt automatisch eingesetzt.
     */
    public function addCompletedStep($stepNr = null) {

        if ($stepNr === null) {
            $stepNr = $this->getCurrentStep();
        }
        
        if (!in_array($this->getCurrentStep(), $this->getCompletedSteps())) {
            $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['completedSteps'][] = $stepNr;
        }
    }

    /**
     * Ruft die gleichnamige Methode der Elternseite auf und erstellt den
     * passenden Parameter.
     */
    public function InsertCustomHtmlForm($formIdentifier = null) {
        if ($formIdentifier === null) {
            $formIdentifier = $this->basename.$this->getCurrentStep();
        }

        return parent::InsertCustomHtmlForm($formIdentifier);
    }

    /**
     * Speichert die Formulardaten des aktuellen Schritts.
     *
     * @param array $formData
     */
    public function setStepData($formData, $stepNr = null) {

        if ($stepNr === null) {
            $stepNr = $this->getCurrentStep();
        }

        $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['steps'][$stepNr] = $formData;
    }

    /**
     * Liefert die Daten des aktuellen Schritts als assoziatives Array. Sind
     * keine Daten vorhanden, wird false zurueckgegeben.
     *
     * @return array | boolean false
     */
    public function getStepData($stepNr = null) {

        if ($stepNr === null) {
            $stepNr = $this->getCurrentStep();
        }

        if (isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['steps'][$stepNr])) {
            return $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['steps'][$stepNr];
        } else {
            return false;
        }
    }

    /**
     * Liefert alle in der Session gespeicherten Daten als assoziatives Array
     * zurueck.
     *
     * @return array
     */
    public function getCombinedStepData() {

        $combinedData = array();

        for ($idx = $this->defaultStartStep; $idx < $this->getNumberOfSteps(); $idx++) {
            $stepData = $this->getStepData($idx);

            $combinedData = array_merge($combinedData, $stepData);
        }

        return $combinedData;
    }

    /**
     * Befuellt die Felder des mitgelieferten Formulars mit den ggfs. in der
     * Session gespeicherten Werten.
     * 
     * @param array $fields 
     */
    public function fillFormFields($fields) {

        $formSessionData    = $this->getStepData();
        $fieldIdx           = 0;

        foreach ($fields as $fieldName => $fieldData) {

            if (isset($formSessionData[$fieldName])) {
                if ($fieldData['type'] == 'OptionSetField' ||
                    $fieldData['type'] == 'DropdownField') {
                    $valueParam = 'selectedValue';
                } else {
                    $valueParam = 'value';
                }
                
                $fields[$fieldName][$valueParam] = $formSessionData[$fieldName];
            }
        }
    }

    /**
     * Gibt die Nummer des vorhergehenden Schritts zurueck.
     *
     * @return integer
     */
    public function getPreviousStep() {
        $currentStep = $this->getCurrentStep();

        return $currentStep - 1;
    }

    /**
     * Gibt die Nummer des folgenden Schritts zurueck.
     *
     * @return integer
     */
    public function getNextStep() {
        $currentStep = $this->getCurrentStep();

        return $currentStep + 1;
    }

    /**
     * Setzt die Nummer des aktuellen Schritts
     *
     * @param integer $stepNr
     */
    public function setCurrentStep($stepNr) {
        $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['currentStep'] = $stepNr;
    }

    /**
     * Gibt den Link zur vorhergehenden Seite zurueck.
     *
     * @return string | boolean false
     */
    public function CustomHtmlFormStepLinkPrev() {
        $link = false;

        if ($this->getPreviousStep() > 0 &&
            in_array($this->getPreviousStep(), $this->getCompletedSteps()) ) {

            $link = $this->Link('PreviousStep');
        }

        return $link;
    }

    /**
     * Gibt den Link zur folgenden Seite zurueck.
     *
     * @return string | boolean false
     */
    public function CustomHtmlFormStepLinkNext() {
        $link = false;

        if ($this->getNextStep() <= $this->getNumberOfSteps() &&
            in_array($this->getCurrentStep(), $this->getCompletedSteps())) {
            
            $link = $this->Link('NextStep');
        }

        return $link;
    }

    /**
     * Gibt den Link zurueck, mit dem man Abbrechen kann.
     *
     * @return string | boolean false
     */
    public function CustomHtmlFormStepLinkCancel() {
        $link = false;

        if ($this->showCancelLink) {
            $link = $this->Link('Cancel');
        }

        return $link;
    }

    /**
     * Erhoeht den aktuellen Schritt
     */
    public function NextStep() {
        if ($this->getNextStep() <= $this->getNumberOfSteps()) {
            $this->setCurrentStep($this->getNextStep());
        }
        Director::redirect($this->Link(), 302);
    }

    /**
     * Vermindert den aktuellen Schritt
     */
    public function PreviousStep() {
        if ($this->getPreviousStep() > 0 &&
            in_array($this->getPreviousStep(), $this->getCompletedSteps()) ) {

            $this->setCurrentStep($this->getPreviousStep());
        }
        Director::redirect($this->Link(), 302);
    }

    /**
     * Bricht das Ausfuellen des Formulars ab und leitet den Nutzer zur
     * ersten Seite zurueck.
     */
    public function Cancel() {
        $this->setCurrentStep($this->defaultStartStep);
        $this->deleteSessionData();

        if ($this->cancelPageID) {
            $link = DataObject::get_by_id('Page', $this->cancelPageID)->Link();
        } else {
            $link = $this->Link();
        }

        Director::redirect($link, 302);
    }

    /**
     * Registriert das Formular fuer den aktuellen Schritt.
     */
    protected function registerCurrentFormStep() {
        $formClassName = $this->basename.$this->getCurrentStep();

        $this->registerCustomHtmlForm($formClassName, new $formClassName($this));
    }

    /**
     * Legt die fuer die CustomHtmlFormStep benoetigte Datenstruktur in der
     * Session an.
     *
     * $_SESSION
     *   CustomHtmlFormStep
     *     {PageClass}{PageID}
     *       currentStep    => Int          Default: 1
     *       completedSteps => array()      Default: empty
     *       steps          => array()      Default: empty
     */
    protected function initialiseSessionData() {
        if (!isset($_SESSION['CustomHtmlFormStep'])) {
            $_SESSION['CustomHtmlFormStep'] = array();
        }
        if (!isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID])) {
            $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID] = array();
        }
        if (!isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['currentStep'])) {
            $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['currentStep'] = $this->defaultStartStep;
        }
        if (!isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['completedSteps'])) {
            $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['completedSteps'] = array();
        }
        if (!isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['steps'])) {
            $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['steps'] = array();
        }
    }

    /**
     * Gibt die Anzahl der Formularschritte zurueck.
     * Diese wird folgendermassen bestimmt:
     * - Pruefung, ob Template mit Namensschema "{basename}{schritt}.ss"
     *   existiert.
     * - Pruefung, ob Klasse mit Namensschema "{basename}{schritt}.php"
     *   deklariert ist.
     * Die Schritte werden in einer Schleife hochgezaehlt; ist eine der beiden
     * Bedingungen nicht erfuellt, wird die Schleife abgebrochen.
     *
     * @return integer
     */
    protected function getNumberOfSteps() {

        if ($this->nrOfSteps > -1) {
            return $this->nrOfSteps;
        }

        $themePath      = THEMES_DIR.'/'.SSViewer::current_theme().'/templates/Layout/';
        $increaseStep   = true;
        $stepIdx        = 1;

         while ($increaseStep) {
            
            if (!Director::fileExists($themePath.$this->basename.$stepIdx.'.ss')) {
                $increaseStep = false;
            }
            if (!class_exists($this->basename.$stepIdx)) {
                $increaseStep = false;
            }

            if ($increaseStep) {
                $stepIdx++;
            }
        }

        return ($stepIdx - 1);
    }

    /**
     * Loescht die Daten aller Schritte aus der Session
     */
    protected function deleteSessionData() {
        if (isset($_SESSION['CustomHtmlFormStep']) &&
            isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID])) {
            
            unset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]);
        }
    }
}