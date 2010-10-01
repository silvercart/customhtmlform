<?php
/**
 * Stellt zusaetzliche Methoden und Mechanismen fuer die Page.php bereit, die von Pixeltricks-
 * modulen verwendet werden.
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
        'customHtmlFormSubmit'
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
     * @param string $snippet
     */
    public function addJavascriptOnloadSnippet($snippet)
    {
        $this->JavascriptOnloadSnippets[] = $snippet;
    }

    /**
     * Fuegt ein Snippet in die Liste der Javascripte ein, die im Headbereich
     * eingefuegt werden sollen.
     *
     * @param string $snippet
     */
    public function addJavascriptSnippet($snippet)
    {
        $this->JavascriptSnippets[] = $snippet;
    }

    /**
     * Registriert ein Formularobjekt.
     *
     * @param CustomHtmlForm $formObj
     */
    public function registerCustomHtmlForm($formIdentifier, CustomHtmlForm $formObj) {
        $this->registeredCustomHtmlForms[$formIdentifier] = $formObj;
    }

    /**
     * Liefert den HTML-Quelltext des angeforderten Formulars zurueck.
     *
     * @param string $formIdentifier
     * @param Object $renderWithObject: Objekt, in dessen Kontext das Formular
     *               erstellt werden soll.
     * @return CustomHtmlForm
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

        if ($renderWithObject) {
            $customFields = $renderWithObject->getAllFields();
            unset($customFields['ClassName']);
            unset($customFields['RecordClassName']);
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

    public function ptInit() {
    
        $currentTheme = SSViewer::current_theme();
        Validator::set_javascript_validation_handler('none');
        
        // -------------------------------------------------------------------
        // Javascript Onload Snippets einfuegen
        // -------------------------------------------------------------------
        $onLoadSnippetStr   = '';
        $snippetStr         = '';
        
        foreach ($this->JavascriptOnloadSnippets as $snippet)
        {
            $onLoadSnippetStr .= $snippet;
        }

        foreach ($this->JavascriptSnippets as $snippet)
        {
            $snippetStr .= $snippet;
        }

        if (!empty($snippetStr) ||
            !empty($onLoadSnippetStr))
        {
            Requirements::customScript('

                '.$snippetStr.'

                window.onload = function()
                {
                    '.$onLoadSnippetStr.'
                };
            ');
        }
        
        // -------------------------------------------------------------------
        // Scripte laden
        // -------------------------------------------------------------------
        Requirements::javascript('pixeltricks_module/script/jquery.js');
        Requirements::javascript('pixeltricks_module/script/jquery.scrollTo.min.js');
        Requirements::javascript('pixeltricks_module/script/jquery.pixeltricks.forms.checkFormData.js');
        Requirements::javascript('pixeltricks_module/script/jquery.pixeltricks.forms.validator.js');
    }

    /**
     * Verarbeitungsmethode fuer alle CustomHtmlFormObjekte.
     */
    public function customHtmlFormSubmit($form) {
        // TODO: Herausfinden, welches Formular gesendet wurde und dessen
        // Submit-Methode aufrufen.
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
}