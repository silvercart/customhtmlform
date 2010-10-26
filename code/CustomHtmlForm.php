<?php
/**
 * Stellt Funktionalitaet bereit, die fuer Formulare mit frei anpassbarem
 * HTML-Code nuetzlich ist.
 *
 * @package pixeltricks_module
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @copyright 2010 pxieltricks GmbH
 * @since 25.10.2010
 * @license none
 */
class CustomHtmlForm extends Form {
    /**
     * Speichert den Controller der aufrufenden Klasse.
     *
     * @var Controller
     */
    protected $controller;
    
    /**
     * Gibt an, wohin die Umleitung nach erfolgreicher Validierung des
     * Formulars erfolgen soll.
     * 
     * @var string
     */
    protected $redirectTargetAfterSuccessfulSubmit;

    /**
     * Enthaelt die Formularfelder.
     *
     * Aufbau:
     * $SSformFields = array(
     *     'fields' => array(FieldSet),
     *     'actions' => array(FieldSet)
     * );
     *
     * @var FieldSet
     */
    protected $SSformFields;

    /**
     * Der Name des Objekts.
     *
     * @var string
     */
    protected $name;

    /**
     * Der Name des Objekts, der fuer die Javascript-Validatoren verwendet
     * wird.
     *
     * @var string
     */
    protected $jsName;

    /**
     * Enthaelt die Fehlermeldungen fuer ein Formular.
     *
     * @var array
     */
    protected $errorMessages;

    /**
     * Enthaelt die Benachrichtigungen fuer ein Formular.
     *
     * @var array
     */
    protected $messages;

    /**
     * Enthaelt spezielle Javascriptanweisungen; wird momentan nur von der
     * CustomHtmlFormStep-Klasse benutzt.
     *
     * @var string
     */
    protected $specialRequirements;

    /**
     * Enthaelt ein assoziatives Array mit Werten, die fuer die Instanz des
     * Formulars als Hiddenfields eingefuegt werden. Diese Felder werden
     * keiner Validierung unterzogen und dienen nur der Weitergabe von
     * Daten zur Steuerung der Auswertung.
     *
     * @var array
     */
    protected $customParameters;

    /**
     * Enthaelt die Nummer der aktuellen Instanziierung.
     *
     * @var integer
     */
    public static $instanceNr = 0;

    /**
     * Erstellt ein Formularobjekt, dessen Layout frei in einem Template
     * gestaltet werden kann.
     *
     * @param ContentController $controller Das aufrufende Controller-Objekt
     * @param array             $params     Optionale Parameter
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function __construct($controller, $params = null) {
        self::$instanceNr++;
        
        $this->controller   = $controller;
        $name               = 'customHtmlFormSubmit';
        $this->fillInFieldValues();
        
        parent::__construct(
            $this->controller,
            $name,
            new FieldSet(),
            new FieldSet()
        );

        if (is_array($params)) {
            $this->customParameters = $params;
        }

        $this->name               = $this->class.'_'.$name.'_'.self::$instanceNr;
        $this->jsName             = str_replace('/', '', $this->name);
        $this->SSformFields       = $this->getForm();
        $this->SSformFields['fields']->setForm($this);
        $this->SSformFields['actions']->setForm($this);
        parent::setFields($this->SSformFields['fields']);
        parent::setActions($this->SSformFields['actions']);

        // Action fuer das Formular setzen
        $this->setFormAction(Controller::join_links($this->controller->Link(), $name));

        // -------------------------------------------------------------------
        // Javascript-Validator laden und initialisieren.
        // Einbindung ins Formular erfolgt in Methode "FormAttributes()".
        // -------------------------------------------------------------------
        $validatorFields    = $this->generateJsValidatorFields();
        $currentTheme       = SSViewer::current_theme();
        Requirements::javascript('themes/'.$currentTheme.'/script/jquery.pixeltricks.forms.checkFormData.js');
        Requirements::javascript('themes/'.$currentTheme.'/script/jquery.pixeltricks.forms.validator.js');

        $this->controller->addJavascriptSnippet('
            var '.$this->jsName.';
        ');

        $this->controller->addJavascriptOnloadSnippet('
            '.$this->jsName.' = new pixeltricks.forms.validator();
            '.$this->jsName.'.setFormFields(
                {
                    '.$validatorFields.'
                }
            );
            '.$this->jsName.'.setFormName(\''.$this->jsName.'\');

            '.$this->specialRequirements.'
        ');
    }

    /**
     * Erstellt einen String mit Javascript-Code, der die Formularfelder an
     * den Javascript-Validator uebergibt.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    protected function generateJsValidatorFields() {
        $fieldStr = '';

        foreach ($this->formFields as $fieldName => $fieldProperties) {
            $checkRequirementStr = '';

            if (isset($fieldProperties['checkRequirements'])) {
                foreach ($fieldProperties['checkRequirements'] as $requirement => $definition) {
                    if (is_array($definition)) {

                        $subCheckRequirementStr = '';
                        foreach ($definition as $subRequirement => $subDefinition) {
                            if (is_bool($subDefinition)) {
                                $subDefinitionStr = $subDefinition ? 'true' : 'false';
                            } else if (is_int($subDefinition)) {
                                $subDefinitionStr = $subDefinition;
                            } else {
                                $subDefinitionStr = "'".$subDefinition."'";
                            }

                            $subCheckRequirementStr .= $subRequirement.": '".$subDefinitionStr."',";
                        }

                        if (!empty($subCheckRequirementStr)) {
                            $subCheckRequirementStr = substr($subCheckRequirementStr, 0, strlen($subCheckRequirementStr) - 1);

                            $checkRequirementStr .= $requirement.': {';
                            $checkRequirementStr .= $subCheckRequirementStr;
                            $checkRequirementStr .= '},';
                        }
                    } else {
                        if (is_bool($definition)) {
                            $definitionStr = $definition ? 'true' : 'false';
                        } else if (is_int($definition)) {
                            $definitionStr = $definition;
                        } else {
                            $definitionStr = "'".$definition."'";
                        }
                        
                        $checkRequirementStr .= $requirement.": ".$definitionStr.",\n";
                    }
                }
            }

            if (!empty($checkRequirementStr)) {
                $checkRequirementStr = substr($checkRequirementStr, 0, strlen($checkRequirementStr) - 1);
            }

            $fieldStr .= $fieldName.': {
                type: \''.$fieldProperties['type'].'\',
                checkRequirements: {
                    '.$checkRequirementStr.'
                }
            },';
        }

        if (!empty($fieldStr)) {
            $fieldStr = substr($fieldStr, 0, strlen($fieldStr) - 1);
        }
        
        return $fieldStr;
    }

    /**
     * Diese Methode kann optional in den abgeleiteten Klassen implementiert
     * werden.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    protected function fillInFieldValues() {
    }

    /**
     * Setzt die URL, auf die nach erfolgreicher Validierung des Formulars
     * umgeleitet werden soll.
     *
     * @param string $target Ziel, auf das umgeleitet werden soll.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function setRedirectTargetAfterSuccessfulSubmit($target) {
        $this->redirectTargetAfterSuccessfulSubmit = $target;
    }

    /**
     * Setzt die URL, auf die nach erfolgreicher Validierung des Formulars
     * umgeleitet werden soll.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function getRedirectTargetAfterSuccessfulSubmit() {
        if (empty($this->redirectTargetAfterSuccessfulSubmit)) {
            return '/';
        } else {
            return $this->redirectTargetAfterSuccessfulSubmit;
        }
    }

    /**
     * Verarbeitet das gesendete Formular. Treten Validierungsfehler auf,
     * wird das Formular mit den entsprechenden Hinweisen angezeigt, ansonsten
     * wird der neue User eingeloggt und auf die Accountseite weitergeleitet.
     *
     * @param SS_HTTPRequest $data Die gesendeten Rohdaten
     * @param Form           $form Das Formularobjekt
     *
     * @return ViewableData
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function submit($data, $form) {
        $formData = $this->getFormData($data);
        $this->checkFormData($formData);
       
        if (empty($this->errorMessages)) {
            // Es sind keine Fehler aufgetreten:
            return $this->submitSuccess(
                $data,
                $form,
                $formData
            );
        } else {
            // Es sind Fehler aufgetreten:
            return $this->submitFailure(
                $data,
                $form
            );
        }
    }

    /**
     * Die Validierung des Formulars ist fehlgeschlagen, also wird hier das
     * Formular mit den entsprechenden Fehlermeldungen ausgegeben.
     *
     * @param SS_HTTPRequest $data Die gesendeten Rohdaten
     * @param Form           $form Das Formularobjekt
     *
     * @return ViewableData
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function submitFailure($data, $form) {
        $this->SSformFields = $this->getForm();

        if (empty($form)) {
            $form = $this->class;
        }
        
        // aufgetretene Validierungsfehler in Template auswertbar machen
        $data = array(
            'errorMessages' => new DataObjectSet($this->errorMessages),
            'messages' => new DataObjectSet($this->messages),
            $this->SSformFields['fields'],
            $this->SSformFields['actions']
        );

        parent::__construct(
            $this->controller,
            $this->name,
            $this->SSformFields['fields'],
            $this->SSformFields['actions']
        );

        // Formular mit Validierungsergebnissen befuellen und rendern
        $outputForm = $this->customise($data)->renderWith(
            array(
                $this->class
            )
        );

        // Gerendertes Formular an Controller uebergeben
        return $this->controller->customise(
            array(
                $form => $outputForm
            )
        );
    }

    /**
     * Wird ausgefuehrt, wenn nach dem Senden des Formulars keine Validierungs-
     * fehler aufgetreten sind.
     *
     * @param SS_HTTPRequest $data     Die gesendeten Rohdaten
     * @param Form           $form     Das Formularobjekt
     * @param array          $formData Die abgesicherten Formulardaten
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    protected function submitSuccess($data, $form, $formData) {
        // In Instanz implementieren
    }

    /**
     * Uebertraegt die Werte aus einem SS_HTTPRequest-Objekt in die definierten
     * Formularfelder. Nicht uebermittelte Werte werden auf false gesetzt.
     *
     * Bei der Uebertragung werden die gesendeten Werte datenbanksicher
     * gemacht.
     *
     * @param SS_HTTPRequest $request Die gesendeten Rohdaten.
     *
     * @return array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    protected function getFormData($request) {
        $formData = array();

        if ($this->securityTokenEnabled()) {
            $formData['SecurityID'] = Convert::raw2sql($request['SecurityID']);
        }

        // Definierte Formularfelder auslesen
        if (isset($this->formFields)) {
            foreach ($this->formFields as $fieldName => $fieldDefinition) {
                if (isset($request[$fieldName])) {
                    $formData[$fieldName] = Convert::raw2sql($request[$fieldName]);
                } else {
                    $formData[$fieldName] = false;
                }
            }
        }

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
     * Prueft alle Formularfelder und gibt das Ergebnis als Array zurueck.
     *
     * @param SS_HTTPRequest $data Die zu pruefenden Formulardaten.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    protected function checkFormData($data) {
        $errorMessages  = array();
        $error          = false;

        if ($this->securityTokenEnabled()) {
            $securityID = Session::get('SecurityID');
            
            if (empty($securityID) ||
                empty($data['SecurityID']) ||
                $data['SecurityID'] != $securityID) {
                
                $error                      = true;
                $errorMessages['CSRF'] = array(
                    'message'   => 'CSRF Attacke!',
                    'fieldname' => 'CSRF',
                    'CSRF' => array(
                        'message' => 'CSRF Attacke!'
                    )
                );
            }
        }

        if (!$error && isset($this->formFields)) {
            foreach ($this->formFields as $fieldName => $fieldDefinition) {
                $fieldErrorMessages = array();
                $fieldError         = false;
                $checkFormData      = new CheckFormData($data[$fieldName]);

                // Formale Erfordernisse pruefen, die dieses Feld erfuellen muss.
                if (!isset($fieldDefinition['checkRequirements'])) {
                    continue;
                }
                
                foreach ($fieldDefinition['checkRequirements'] as $requirement => $requiredValue) {
                    // --------------------------------------------------------
                    // Sonderfaelle:
                    // --------------------------------------------------------

                    // Kriterium bezieht sich auf ein anderes Feld
                    if ($requirement == 'mustEqual' ||
                        $requirement == 'mustNotEqual') {

                        $requiredValue = array(
                            'fieldName' => $this->formFields[$requiredValue]['title'] ? $this->formFields[$requiredValue]['title'] : $requiredValue,
                            'value'     => $data[$requiredValue]
                        );
                    }

                    // Feld muss ausgefuellt sein, wenn anderes Feld
                    // ausgefuellt ist
                    if ($requirement == 'isFilledInDependantOn') {
                        $requiredValue = array(
                            $requiredValue,
                            $data
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

        $this->errorMessages = $errorMessages;
    }

    /**
     * Erstellt die Eingabe- und Aktionsfelder fuer das Formular und befuellt
     * fehlende Angaben in der Felddefinition mit Standardwerten.
     *
     * @return array Liefert die Fields und Actions des Formulars:
     *      array(
     *          'fields'    => FieldSet,
     *          'actions'   => FieldSet
     *      )
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    protected function getForm() {
        $fields = new FieldSet();

        // --------------------------------------------------------------------
        // Metadaten fuer das Formular setzen
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
        // Fieldset aus den Definitionen in $this->formFields erstellen
        // --------------------------------------------------------------------
        if (isset($this->formFields)) {
            foreach ($this->formFields as $fieldName => $fieldDefinition) {
                $field = $this->getFormField(
                    $fieldName,
                    $fieldDefinition
                );

                $fields->push($field);
            }
        }

        $actions = new FieldSet(
            new FormAction(
                'customHtmlFormSubmit',
                'Abschicken',
                $this
            )
        );
        
        return array(
            'fields'    => $fields,
            'actions'   => $actions
        );
    }

    /**
     * Erstellt ein Formularfeld anhand der uebergebenen Definition. Setzt die
     * Felddefinitionen auf Standardwerte, wenn nicht definiert.
     *
     * @param string $fieldName       Der Name des Feldes
     * @param array  $fieldDefinition Die Definition des Feldes
     *
     * @return Field
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    protected function getFormField($fieldName, $fieldDefinition) {
        if (!isset($fieldDefinition['type'])) {
            throw new Exception(
                'CustomHtmlForm: Feldtyp muss angegeben werden.'
            );
        }

        $fieldReference = &$this->formFields[$fieldName];

        // Erforderliche Felder mit Standardwerten befuellen, wenn sie
        // nicht angegeben sind.
        if (!isset($fieldDefinition['isRequired'])) {
            $fieldDefinition['isRequired'] = false;
            $fieldReference['isRequired'] = $fieldDefinition['isRequired'];
        }

        if (!isset($fieldDefinition['checkRequirements'])) {
            $fieldDefinition['checkRequirements'] = array();
            $fieldReference['checkRequirements'] = $fieldDefinition['checkRequirements'];
        }

        if (!isset($fieldDefinition['title'])) {
            $fieldDefinition['title'] = '';
            $fieldReference['title'] = $fieldDefinition['title'];
        }

        if (!isset($fieldDefinition['value'])) {
            $fieldDefinition['value'] = '';
            $fieldReference['value'] = $fieldDefinition['value'];
        }

        if (!isset($fieldDefinition['selectedValue'])) {
            $fieldDefinition['selectedValue'] = '';
            $fieldReference['selectedValue'] = $fieldDefinition['selectedValue'];
        }

        if (!isset($fieldDefinition['maxLength'])) {
            $fieldDefinition['maxLength'] = null;
            $fieldReference['maxLength'] = $fieldDefinition['maxLength'];
        }

        if (!isset($fieldDefinition['form'])) {
            $fieldDefinition['form'] = $this;
            $fieldReference['form'] = $fieldDefinition['form'];
        }

        // Feld erstellen
        if ($fieldDefinition['type'] == 'DropdownField') {
            $field = new $fieldDefinition['type'](
                $fieldName,
                $fieldDefinition['title'],
                $fieldDefinition['value'],
                $fieldDefinition['selectedValue'],
                $fieldDefinition['form']
            );
        } else if ($fieldDefinition['type'] == 'OptionSetField') {
            $field = new $fieldDefinition['type'](
                $fieldName,
                $fieldDefinition['title'],
                $fieldDefinition['value'],
                $fieldDefinition['selectedValue'],
                $fieldDefinition['form']
            );
        } else if ($fieldDefinition['type'] == 'TextField') {
            $field = new $fieldDefinition['type'](
                $fieldName,
                $fieldDefinition['title'],
                $fieldDefinition['value'],
                $fieldDefinition['maxLength'],
                $fieldDefinition['form']
            );
        } else if ($fieldDefinition['type'] == 'TextareaField') {
            $field = new $fieldDefinition['type'](
                $fieldName,
                $fieldDefinition['title'],
                10,
                10,
                $fieldDefinition['value'],
                $fieldDefinition['form']
            );
        } else if ($fieldDefinition['type'] == 'RecaptchaField') {
            $field = new RecaptchaField('Recaptcha');
            $recaptchaField->jsOptions = array('theme' => 'clean');
        } else {
            $field = new $fieldDefinition['type'](
                $fieldName,
                $fieldDefinition['title'],
                $fieldDefinition['value'],
                $fieldDefinition['form']
            );
        }

        // Wenn eine Fehlermeldung fuer dieses Feld existiert, dann einbauen
        if (isset($this->errorMessages[$fieldName])) {
            $field->errorMessage = new ArrayData(array(
                'message' => $this->errorMessages[$fieldName]['message']
            ));
        }

        return $field;
    }

    /**
     * Liefert den Namen des Formularobjekts zurueck.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function getCustomHtmlFormName() {
        return $this->name;
    }

    /**
     * Liefert die Attribute fuer den HTML-Formtag.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function FormAttributes() {
        $attributes  = parent::FormAttributes();
        $attributes .= ' onsubmit="return '.$this->jsName.'.checkForm();"';

        return $attributes;
    }

    /**
     * Setzt eine neue Nachricht fuer das Formular.
     *
     * @param string $message Der Nachrichtentext
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function addMessage($message) {
        $this->messages[] = array('message' => $message);
    }

    /**
     * Setzt die Metadaten fuer die Formularverarbeitung in das Formular-
     * template ein.
     * Wird vom Template aus aufgerufen.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function CustomHtmlFormMetadata() {
        $metadata = '';

        // Formularname
        $metadata .= $this->dataFieldByName('CustomHtmlFormName')->Field();
        
        // SecurityID
        $metadata .= $this->dataFieldByName('SecurityID')->Field();

        // Eigene Datenfelder
        if (!empty($this->customParameters)) {
            foreach ($this->customParameters as $customParameterKey => $customParameterValue) {
                $metadata .= $this->dataFieldByName($customParameterKey)->Field();
            }
        }

        return $metadata;
    }

    /**
     * Liefert den HTML-Code fuer das angegebene Feld zurueck. Dieser wird
     * mit dem Standardtemplate fuer Felder erzeugt.
     *
     * @param string $fieldName Der Feldname
     * @param string $template  optional. Pfad zum Template-Snippet, ausgehend relativ vom Siteroot
     * 
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function CustomHtmlFormFieldByName($fieldName, $template = null) {

        if (!isset($this->formFields[$fieldName])) {
            throw new Exception(
                printf('Das Feld "%s" wird im Template aufgerufen, ist aber nicht im Formularobjekt definiert.', $fieldName)
            );
        }

        $defaultTemplatePath = '/pixeltricks_module/templates/forms/CustomHtmlFormField.ss';

        if (!empty($template)) {

            $template = THEMES_DIR.'/'.SSViewer::current_theme().'/templates/Layout/'.$template.'.ss';

            if (Director::fileExists($template)) {
                $templatePathRel = '/'.$template;
            } else {
                $templatePathRel = $defaultTemplatePath;
            }

        } else {
            $templatePathRel = $defaultTemplatePath;
        }

        $templatePathAbs    = Director::baseFolder().$templatePathRel;
        $viewableObj        = new ViewableData();
        $output = $viewableObj->customise(
            array(
                'FormName'      => $this->name,
                'FieldName'     => $fieldName,
                'Label'         => $this->formFields[$fieldName]['title'],
                'errorMessage'  => isset($this->errorMessages[$fieldName]) ?  $this->errorMessages[$fieldName] : '',
                'FieldTag'      => $this->SSformFields['fields']->fieldByName($fieldName)->Field()
            )
        )->renderWith($templatePathAbs);

        return $output;
    }

    /**
     * Liefert die Fehlermeldungen als HTML-Text zurueck.
     *
     * @param string $template optional. Name des Templates, das zum Rendern der Meldungen benutzt werden soll.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function CustomHtmlFormErrorMessages($template = null) {
        
        // aufgetretene Validierungsfehler in Template auswertbar machen
        $data = array(
            'errorMessages' => new DataObjectSet($this->errorMessages),
            'messages' => new DataObjectSet($this->messages)
        );

        $defaultTemplatePath = '/pixeltricks_module/templates/forms/CustomHtmlFormErrorMessages.ss';

        if (!empty($template)) {

            $template = THEMES_DIR.'/'.SSViewer::current_theme().'/templates/Layout/'.$template.'.ss';

            if (Director::fileExists($template)) {
                $templatePathRel = '/'.$template;
            } else {
                $templatePathRel = $defaultTemplatePath;
            }

        } else {
            $templatePathRel = $defaultTemplatePath;
        }

        $templatePathAbs    = Director::baseFolder().$templatePathRel;
        $viewableObj        = new ViewableData();
        $output = $viewableObj->customise(
            $data
        )->renderWith($templatePathAbs);

        return $output;
    }

    /**
	 * Liefert den Name des Formulars.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
	 */
    public function FormName() {
        if ($this->htmlID) {
            return $this->htmlID;
        } else {
            return $this->name;
        }
    }
}