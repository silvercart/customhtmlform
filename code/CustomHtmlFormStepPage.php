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
        'basename'      => 'Varchar(255)'
    );
    
    /**
     * Erweitert die Eingabemaske des Admins
     * 
     * @return FieldSet
     */
    public function  getCMSFields() {

        $basenameField = new TextField('basename', 'Basisname für Formular Objekt- und Templatedateien: ');

        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Content.Main', $basenameField);

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
    protected $nrOfSteps;
    
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
     * Ruft die gleichnamige Methode der Elternseite auf und erstellt den
     * passenden Parameter.
     */
    public function InsertCustomHtmlForm() {
        $formIdentifier = $this->basename.$this->getCurrentStep();

        return parent::InsertCustomHtmlForm($formIdentifier);
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
     *
     */
    protected function initialiseSessionData() {
        if (!isset($_SESSION['CustomHtmlFormStep'])) {
            $_SESSION['CustomHtmlFormStep'] = array();
        }
        if (!isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID])) {
            $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID] = array();
        }
        if (!isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['currentStep'])) {
            $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['currentStep'] = 1;
        }
        if (!isset($_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['completedSteps'])) {
            $_SESSION['CustomHtmlFormStep'][$this->ClassName.$this->ID]['completedSteps'] = array();
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
}