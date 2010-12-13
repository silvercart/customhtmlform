<?php
/**
 * Stellt zusaetzliche Methoden und Mechanismen fuer die Page.php bereit, die von Pixeltricks-
 * modulen verwendet werden.
 *
 * @package pixeltricks_module
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @copyright 2010 pxieltricks GmbH
 * @since 25.10.2010
 * @license none
 */
class PixeltricksPage_Controller extends DataObjectDecorator {

    /**
     * Definiert die erlaubten Methoden.
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
     * Enthaelt alle Javascriptbloecke, die im onload-Event ausgefuehrt werden sollen.
     *
     * @var array
     */
    protected $JavascriptOnloadSnippets = array();

    /**
     * Enthaelt alle Javascriptbloecke, die nicht im onload-Event ausgefuehrt
     * werden sollen.
     *
     * @var array
     */
    protected $JavascriptSnippets = array();

    /**
     * Enthaelt eine Liste der registrierten CustomHtmlForm-Formulare.
     *
     * @var array
     */
    protected $registeredCustomHtmlForms = array();

    /**
     * Fuegt ein Snippet in die Liste der Javascript Onload-Events ein.
     *
     * @param string $snippet Textblock mit Javascript-Anweisungen
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function addJavascriptOnloadSnippet($snippet) {
        $this->JavascriptOnloadSnippets[] = $snippet;
    }

    /**
     * Fuegt ein Snippet in die Liste der Javascripte ein, die im Headbereich
     * eingefuegt werden sollen.
     *
     * @param string $snippet Textblock mit Javascript-Anweisungen
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function addJavascriptSnippet($snippet) {
        $this->JavascriptSnippets[] = $snippet;
    }

    /**
     * Registriert ein Formularobjekt.
     *
     * @param string         $formIdentifier Eindeutiger Name des Formulars, mit dem es in Templates aufgerufen werden kann.
     * @param CustomHtmlForm $formObj        Das Formularobjekt mit Felddefinitionen und Verarbeitungsmethoden.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function registerCustomHtmlForm($formIdentifier, CustomHtmlForm $formObj) {
        $this->registeredCustomHtmlForms[$formIdentifier] = $formObj;
    }

    /**
     * Liefert den HTML-Quelltext des angeforderten Formulars zurueck.
     *
     * @param string $formIdentifier   Eindeutiger Name des Formulars, mit dem es in Templates aufgerufen werden kann.
     * @param Object $renderWithObject Array mit Objekten, in deren Kontext das Formular erstellt werden soll.
     *
     * @return CustomHtmlForm
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function InsertCustomHtmlForm($formIdentifier, $renderWithObject = null) {

        if (!isset($this->registeredCustomHtmlForms[$formIdentifier])) {
            throw new Exception(
                printf(
                    'The requested CustomHtmlForm "%s" is not registered.',
                    $formIdentifier
                )
            );
        }

        if ($renderWithObject !== null) {
            if (is_array($renderWithObject)) {
                foreach ($renderWithObject as $renderWithSingleObject) {
                    if ($renderWithSingleObject instanceof DataObject) {
                        if (isset($customFields)) {
                            $customFields = array_merge($customFields, $renderWithSingleObject->getAllFields());
                        } else {
                            $customFields = $renderWithSingleObject->getAllFields();
                        }
                        unset($customFields['ClassName']);
                        unset($customFields['RecordClassName']);
                    }
                }
            } else {
                if ($renderWithObject instanceof DataObject) {
                    $customFields = $renderWithObject->getAllFields();
                    unset($customFields['ClassName']);
                    unset($customFields['RecordClassName']);
                }
            }
        } else {
            $customFields = null;
        }

        if ($customFields) {
            $outputForm = $this->registeredCustomHtmlForms[$formIdentifier]->customise($customFields)->renderWith(
                array(
                    $this->registeredCustomHtmlForms[$formIdentifier]->class,
                )
            );
        } else {
            $outputForm = $this->registeredCustomHtmlForms[$formIdentifier]->renderWith(
                array(
                    $this->registeredCustomHtmlForms[$formIdentifier]->class,
                )
            );
        }

        return $outputForm;
    }

    /**
     * Eigene Requirements als erste laden.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function onBeforeInit() {
        Validator::set_javascript_validation_handler('none');

        // -------------------------------------------------------------------
        // Scripte laden
        // -------------------------------------------------------------------
        Requirements::javascript('pixeltricks_module/script/jquery.js');
        Requirements::javascript('pixeltricks_module/script/jquery.scrollTo.min.js');
        Requirements::javascript('pixeltricks_module/script/jquery.pixeltricks.forms.checkFormData.js');
        Requirements::javascript('pixeltricks_module/script/jquery.pixeltricks.forms.events.js');
        Requirements::javascript('pixeltricks_module/script/jquery.pixeltricks.forms.validator.js');
    }

    /**
     * Erweitert die init-Methode des erweiterten Controllers.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function init() {
        // -------------------------------------------------------------------
        // Javascript Onload Snippets einfuegen
        // -------------------------------------------------------------------
        $onLoadSnippetStr   = '';
        $snippetStr         = '';

        foreach ($this->JavascriptOnloadSnippets as $snippet) {
            $onLoadSnippetStr .= $snippet;
        }

        foreach ($this->JavascriptSnippets as $snippet) {
            $snippetStr .= $snippet;
        }

        if (!empty($snippetStr) ||
            !empty($onLoadSnippetStr)) {

            Requirements::customScript('

                '.$snippetStr.'

                window.onload = function()
                {
                    '.$onLoadSnippetStr.'
                };
            ');
        }
    }

    /**
     * Verarbeitungsmethode fuer alle CustomHtmlFormObjekte.
     *
     * @param Form $form Das sendende Formularobjekt
     *
     * @return mixed (abhaengig von der verarbeitenden Formularmethode)
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pxieltricks GmbH
     * @since 25.10.2010
     */
    public function customHtmlFormSubmit($form) {
        $formName = $this->owner->request->postVar('CustomHtmlFormName');

        foreach ($this->registeredCustomHtmlForms as $registeredCustomHtmlForm) {
            if ($formName === $registeredCustomHtmlForm->name) {
                break;
            }
        }

        if ($registeredCustomHtmlForm instanceof CustomHtmlForm) {
            return $registeredCustomHtmlForm->submit($form, null);
        }
    }

    /**
     * Wrapper fuer Action auf Uploadify-Feld.
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
     * Wrapper fuer Action auf Uploadify-Feld.
     *
     * @param SS_HTTPRequest $request Die Anfrageparameter
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
     * Wrapper fuer Action auf Uploadify-Feld.
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
     * @copyright 2010 pixeltricks GmbH
     * @since 03.11.2010
     */
    protected function getFieldObject() {
        $formIdentifier = 'CreateAuctionFormStep5';
        $fieldName      = 'UploadImages';
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
