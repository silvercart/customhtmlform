<?php
/**
 * Ein Schritt einer MultiFormreihe.
 *
 * @package fashionbids
 * @author Sascha Koehler (skoehler@pixeltricks.de)
 * @copyright Pixeltricks GmbH
 */

class CustomHtmlFormStep extends CustomHtmlForm
{
    protected $step = 1;
    protected $data = array();
    protected $name;
    
    /**
     * Enthaelt die zu pruefenden und zu verarbeitenden Formularfelder.
     *
     * @var array
     */
    protected $formFields = array();

    /**
     * Setzt Initialwerte in Formularfeldern.
     *
     * @param Controller $controller
     * @param string $name
     */
    public function __construct($controller, $name)
    {
        $this->name         = $name;
        $this->controller   = $controller;
        $this->step         = $this->controller->Sort;
        $this->data         = Session::get($this->controller->ClassName);

        $this->redirectIfStepIsNotAllowed();

        parent::__construct($controller, $name);
    }

    /**
     * Wenn die vorherigen Schritte nicht durchgefuehrt wurden, dann
     * auf den ersten Schritt umleiten.
     *
     * Wenn der User nicht eingeloggt ist, dann auf die Elternseite umleiten.
     */
    public function redirectIfStepIsNotAllowed()
    {
        $url        = $this->controller->request->getURL();
        $parentUrl  = substr($url, 0, strrpos($url, '/'));

        if (!Member::currentUser())
        {
            header('Location: /'.$parentUrl.'/');
            exit();
        }

        if (!$this->isStepAllowed())
        {
            header('Location: /'.$parentUrl.'/'.$this->getStep(1)->URLSegment);
            exit();
        }
    }

    /**
     * Ersetzt die Actions des Formulars.
     *
     * @return array
     */
    protected function getForm()
    {
        $formNoValidationActions    = '';
        $formDefinition             = parent::getForm($this->name);

        $buttonSubmit = new FormAction(
            'submit',
            'Weiter'
        );
        $buttonCancel = new FormAction(
            'cancel',
            'Abbrechen'
        );

        $actions = new FieldSet();
        $actions->push($buttonSubmit);

        if ($this->step > 1)
        {
            $buttonBack = new FormAction(
                'back',
                'Zurück'
            );
            $actions->push($buttonBack);
        }
        $actions->push($buttonCancel);
        $formDefinition['actions'] = $actions;

        // Javascript Validierung fuer Zurueck- und Abbrechenbuttons
        // deaktivieren
        foreach ($formDefinition['actions'] as $formAction)
        {
            if ($formAction->Name() == 'action_cancel' ||
                $formAction->Name() == 'action_back')
            {
                if ($formNoValidationActions != '')
                {
                    $formNoValidationActions .= ',';
                }

                $formNoValidationActions .= '\''.$formAction->Name().'\'';
            }
        }

        // Eventhandler fuer Zurueck- und Abbrechenbuttons installieren
        $this->specialRequirements = get_class($this).'_'.$this->name.'.setNoValidationHandlers(
            [
                '.$formNoValidationActions.'
            ]
        );';

        return $formDefinition;
    }

    /**
     * Verarbeitet das gesendete Formular. Treten Validierungsfehler auf,
     * wird das Formular mit den entsprechenden Hinweisen angezeigt, ansonsten
     * wird der neue User eingeloggt und auf die Accountseite weitergeleitet.
     *
     * Loescht die gespeicherten Daten der nachfolgenden Schritte aus der
     * Session.
     *
     * @param SS_HTTPRequest $data
     * @param Form $form
     * @return ViewableData
     */
    public function submit($data, $form)
    {
        $currentStep    = $this->controller->Sort;
        $nrOfSteps      = $this->getNrOfSteps();

        if ($currentStep < $nrOfSteps)
        {
            for ($stepIdx = $nrOfSteps; $stepIdx > $currentStep; $stepIdx--)
            {
                if (!isset($_SESSION[$this->controller->ClassName]['step'.$stepIdx]))
                {
                    continue;
                }

                $fields = $_SESSION[$this->controller->ClassName]['step'.$stepIdx];

                // Felder aus globalem Namensraum loeschen
                foreach ($fields as $fieldName => $fieldValue)
                {
                    unset($_SESSION[$this->controller->ClassName][$fieldName]);
                }

                // Felder aus Stepdaten loeschen
                unset($_SESSION[$this->controller->ClassName]['step'.$stepIdx]);
            }
        }

        return parent::submit($data, $form);
    }

    /**
     * Wird ausgefuehrt, wenn nach dem Senden des Formulars keine Validierungs-
     * fehler aufgetreten sind.
     * Speichert die gesendeten Formulardaten in der Session zum spaeteren
     * Abruf.
     *
     * @param SS_HTTPRequest $data
     * @param Form $form
     * @param array $formData
     */
    public function submitSuccess($data, $form, $formData)
    {
        $this->runSubmitActions($data);

        if (!isset($_SESSION[$this->controller->ClassName]))
        {
            $_SESSION[$this->controller->ClassName] = array();
        }

        if (!isset($_SESSION[$this->controller->ClassName]['step'.$this->controller->Sort]))
        {
            $_SESSION[$this->controller->ClassName]['step'.$this->controller->Sort] = array();
        }

        $_SESSION[$this->controller->ClassName]['step'.$this->controller->Sort] = array_merge(
            $_SESSION[$this->controller->ClassName]['step'.$this->controller->Sort],
            $formData
        );
    }

    /**
     * Wird als Wrapper verwendet, um den Formularnamen an den Parent zu
     * uebergeben.
     *
     * @param SS_HTTPRequest $data
     * @param Form $form
     * @param array $errorMessages
     * @return Viewable
     */
    public function submitFailure($data, $form)
    {
        $this->runSubmitActions($data);

        return parent::submitFailure(
            $data,
            'stepForm',
            $this->errorMessages
        );
    }

    /**
     * Prueft, ob bestimmte Parameter mit dem Formular abgeschickt wurden,
     * wie z.B. Abbrechen oder Zurueckblaettern-Aktionen.
     *
     * @param <type> SS_HTTPRequest
     */
    public function runSubmitActions($data)
    {
        // Auktionserstellung abbrechen
        if (isset($data['action_cancel']))
        {
            unset($_SESSION[$this->controller->ClassName]);

            $url        = $this->controller->request->getURL();

            // Da wir uns in der Action "submit" befinden, muessen wir zwei
            // Bestandteile der URL herausnehmen
            $parentUrl  = substr($url, 0, strrpos($url, '/'));
            $parentUrl  = substr($url, 0, strrpos($parentUrl, '/'));

            header('Location: /'.$parentUrl.'/');
            exit();
        }
        // Zurueckblattern
        if (isset($data['action_back']))
        {
            $this->gotoPrevStep();
        }
    }

    /**
     * Weiterleitung zum naechsten Schritt der Reihe.
     */
    protected function gotoNextStep()
    {
        $nextPage = DataObject::get_one(
            $this->controller->ClassName,
            'ParentID = '.$this->controller->ParentID.' AND ShowInMenus = 1 AND Sort > '.$this->controller->Sort,
            true,
            'Sort ASC'
        );

        if ($nextPage)
        {
            $url        = $this->controller->request->getURL();

            // Da wir uns in der Action "submit" befinden, muessen wir zwei
            // Bestandteile der URL herausnehmen
            $parentUrl  = substr($url, 0, strrpos($url, '/'));
            $parentUrl  = substr($url, 0, strrpos($parentUrl, '/'));

            header('Location: /'.$parentUrl.'/'.$nextPage->URLSegment);
            exit();
        }
    }

    /**
     * Weiterleitung zum vorherigen Schritt der Reihe.
     */
    protected function gotoPrevStep()
    {
        $prevPage = DataObject::get_one(
            $this->controller->ClassName,
            'SiteTree_Live.ParentID = '.$this->controller->ParentID.' AND SiteTree_Live.ShowInMenus = 1 AND SiteTree_Live.Sort < '.$this->controller->Sort,
            true,
            'SiteTree_Live.Sort DESC'
        );
        
        if ($prevPage)
        {
            $url        = $this->controller->request->getURL();

            // Da wir uns in der Action "submit" befinden, muessen wir zwei
            // Bestandteile der URL herausnehmen
            $parentUrl  = substr($url, 0, strrpos($url, '/'));
            $parentUrl  = substr($url, 0, strrpos($parentUrl, '/'));

            header('Location: /'.$parentUrl.'/'.$prevPage->URLSegment);
            exit();
        }
    }

    /**
     * Liefert die Anzahl der Schritte zurueck.
     *
     * @return int
     */
    protected function getNrOfSteps()
    {
        $nrOfPages  = 0;
        $pages      = DataObject::get(
            $this->controller->ClassName,
            'SiteTree_Live.ParentID = '.$this->controller->ParentID.' AND SiteTree_Live.ShowInMenus = 1'
        );

        if ($pages)
        {
            $nrOfPages = count($pages);
        }

        return $nrOfPages;
    }

    /**
     * Liefert den Schritt mit der angegeben Nummer zurueck.
     *
     * @param int $stepNr
     * @return DataObject
     */
    protected function getStep($stepNr)
    {
        return DataObject::get_one(
            $this->controller->ClassName,
            'SiteTree_Live.ParentID = '.$this->controller->ParentID.' AND SiteTree_Live.Sort = '.$stepNr
        );
    }

    /**
     * Prueft, ob ein Schritt bearbeitet werden darf.
     *
     * Dazu wird geschaut, ob wir beim ersten Schritt sind oder ein Eintrag des
     * Vorgaengers in der Session existiert.
     *
     * @return boolean
     */
    protected function isStepAllowed()
    {
        if ((int) $this->controller->Sort === 1)
        {
            return true;
        }

        $checkStepNr = (int) $this->controller->Sort - 1;

        return isset($_SESSION[$this->controller->ClassName]['step'.$checkStepNr]);
    }
}