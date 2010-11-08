// Namensraum initialisieren und ggfs. vorhandenen verwenden
var pixeltricks         = pixeltricks       ? pixeltricks       : [];
    pixeltricks.forms   = pixeltricks.forms ? pixeltricks.forms : [];

/**
 * Formular Validator.
 */
pixeltricks.forms.validator = function()
{
    /**
     * Enthaelt die Definitionen der zu pruefenden Formularfelder.
     */
    this.formFields     = [];
    /**
     * Enthaelt die Fehlermeldungen nach Pruefung der eingetragenen Feldwerte.
     */
    this.errorMessages  = [];
    /**
     * Name des zu pruefenden Formulars.
     */
    this.formName       = '';
    /**
     * Trennzeichen fuer Formular- und Feldname.
     */
    this.nameSeparator  = '_';
    /**
     * Gibt an, ob die Validierung durchgefuehrt werden soll
     */
    this.doValidation   = true;
    /**
     * Gibt an, ob das Formular validiert wurde.
     */
    this.isValidated    = false;
    /**
     * Enthaelt das Resultat der Validierung.
     *
     * Kann folgende Werte enthalten:
     *   PENDING -> es wurde noch nicht validiert
     *   SUCCESS -> Validierung war erfolgreich
     *   FAILURE -> Validierung ist fehlgeschlagen
     */
    this.validationResult = 'PENDING';
    /**
     * Workaround fuer Selbstreferenzierung in Closures.
     */
    var that            = this;

    /**
     * Einstiegsfunktion: prueft alle angegebenen Felder und liefert eine
     * entsprechende Meldung und Kennzeichnung zurueck.
     */
    this.checkForm = function(restrictCheckToField)
    {
        // Validierung nicht durchfuehren, wenn nicht gewuenscht
        if (this.doValidation === false)
        {
            return true;
        }
        
        var fieldName;
        var requirements;
        var result;
        var errors = false;
        var checkFormData = new pixeltricks.forms.checkFormData();
        var errorMessages = {};
        
        $.each(
            this.formFields,
            function(fieldName, definitions)
            {
                // Dieser Wert kann je nach Feldtyp noch ueberschrieben werden
                // (siehe z.B. CheckboxFields)
                checkFormData.setFieldValue(
                    that.getFormFieldValue(that.formName + that.nameSeparator + fieldName)
                );

                $.each(
                    definitions,
                    function(definition, values)
                    {
                        // Requirements pruefen
                        if (definition == 'type')
                        {
                            if (!restrictCheckToField || (restrictCheckToField == fieldName)) {
                                checkFormData.setFieldType(values);

                                // Fuer Checkboxen uebergeben wir als Feldwert den
                                // Zustand der Box (checked / unchecked) als Boolean-Wert
                                if (values == 'CheckboxField') {
                                    checkFormData.setFieldValue(
                                        $('#' + that.formName + that.nameSeparator + fieldName).attr('checked')
                                    );
                                }
                                // Fuer Radiobuttons wird der Wert des selektierten Buttons
                                // ausgelesen.
                                if (values == 'OptionSetField') {
                                    checkFormData.setFieldValue(
                                        $('#' + that.formName + that.nameSeparator + fieldName + '  :checked').val()
                                    );
                                }
                            }
                        }
                        else if (definition == 'checkRequirements')
                        {
                            var fieldErrorMessages  = [];
                            var fieldErrors         = false;

                            $.each(
                                values,
                                function(requirement, requiredValue)
                                {
                                    if (!restrictCheckToField || (restrictCheckToField == fieldName)) {
                                        // ----------------------------------------
                                        // Sonderfaelle bearbeiten
                                        // ----------------------------------------

                                        // Feld muss ausgefuellt sein, wenn anderes Feld
                                        // ausgefuellt ist
                                        if (requirement == 'isFilledInDependantOn')
                                        {
                                            requiredValue = [
                                                requiredValue,
                                                {
                                                    formName:       that.formName,
                                                    nameSeparator:  that.nameSeparator
                                                }
                                            ];
                                        }

                                        // Kriterium bezieht sich auf ein anderes Feld
                                        if (requirement == 'mustEqual' ||
                                            requirement == 'mustNotEqual')
                                        {
                                            requiredValue = {
                                                fieldName:  that.formFields[requiredValue].title ? that.formFields[requiredValue].title : requiredValue,
                                                value:      $('#' + that.formName + that.nameSeparator + requiredValue).val()
                                            };
                                        }

                                        if (requirement == 'PtCaptchaInput') {
                                            requiredValue = {
                                                formName:   that.formName,
                                                fieldName:  fieldName
                                            };
                                        }

                                        // Callbackfunktion verwenden
                                        if (requirement == 'callBack')
                                        {
                                            var strFun  = requiredValue;
                                            var fun     = eval(strFun);

                                            fieldCheckResult = fun(that, fieldName);
                                        }
                                        else
                                        {
                                            fieldCheckResult = checkFormData[requirement](requiredValue);
                                        }

                                        // Fehler speichern
                                        if (fieldCheckResult)
                                        {
                                            if (!fieldCheckResult.success)
                                            {
                                                fieldErrorMessages.push(fieldCheckResult.errorMessage);
                                                fieldErrors = true;
                                            }
                                        }
                                    }
                                }
                            );

                            // Bei diesem Feld sind ein oder mehrere Fehler aufgetreten,
                            // die hier zusammengefasst und ausgeben werdeb.
                            if (fieldErrors)
                            {
                                errorMessages[fieldName] = fieldErrorMessages;
                                errors                   = true;
                            }
                            else
                            {
                                if (!restrictCheckToField || (restrictCheckToField == fieldName)) {
                                    errorMessages[fieldName] = -1;
                                }
                            }
                        }
                    }
                );
            }
        );
        
        this.isValidated = true;

        if (errors)
        {
            this.validationResult = 'FAILURE';
            this.toggleErrorFields(errorMessages);

            return false;
        }
        else
        {
            this.validationResult = 'SUCCESS';
            this.toggleErrorFields(errorMessages);

            return true;
        }
    }

    /**
     * Zeigt Fehlermeldungen an oder blendet sie aus.
     */
    this.toggleErrorFields = function(errorMessages)
    {
        $.each(
            errorMessages,
            function (fieldName, messages)
            {
                var fieldErrorMessages  = '';
                var fieldID             = that.formName + that.nameSeparator + fieldName;
                var errorFieldID        = that.formName + that.nameSeparator + fieldName + that.nameSeparator + 'Error';
                var fieldBoxID          = that.formName + that.nameSeparator + fieldName + that.nameSeparator + 'Box';
                var errorField          = $('#' + errorFieldID);
                var messageStr          = '';

                for (messageIdx = 0; messageIdx < messages.length; messageIdx++) {
                    messageStr += '<strong class="message">';
                    messageStr += messages[messageIdx];
                    messageStr += '</strong>';
                }

                if (errorField.length == 0 &&
                    messages != -1)
                {
                    // --------------------------------------------------------
                    // Fehlerbox und Meldungstext neu erzeugen
                    // --------------------------------------------------------
                    
                    messageStr = '<div class="errorList" id="' + errorFieldID + '" style="display: none;">' + messageStr + '</div>';

                    if ($('#' + fieldBoxID))
                    {
                        $('#' + fieldBoxID).prepend(messageStr);
                        $('#' + fieldBoxID).addClass('error');
                    }
                    else
                    {
                        $('#' + fieldID).after(messageStr);
                    }
                }
                else if (messages != -1)
                {
                    // --------------------------------------------------------
                    // Meldungstext in vorhandener Fehlerbox ersetzen
                    // --------------------------------------------------------
                    $('#' + errorFieldID).html(messageStr);
                }

                // ------------------------------------------------------------
                // Fehlerbox ein- oder ausfahren
                // ------------------------------------------------------------
                if ($('#' + errorFieldID).css('display') == undefined)
                {
                    $('#' + errorFieldID).css('display', 'none');
                }

                if ( ($('#' + errorFieldID).css('display') == 'inline' ||
                      $('#' + errorFieldID).css('display') == 'block') &&
                    messages == -1)
                {
                    // Box ist sichtbar, aber keine Fehler mehr
                    $('#' + errorFieldID).fadeOut(300, function() {});

                    if ($('#' + fieldBoxID))
                    {
                        $('#' + fieldBoxID).removeClass('error');
                    }
                }
                else if ($('#' + errorFieldID).css('display') == 'none' &&
                        messages != -1)
                {
                    // Box ist unsichtbar, aber Fehler vorhanden
                    $('#' + errorFieldID).fadeIn(300, function() {});
                    $('#' + fieldBoxID).addClass('error');
                }
                else if ( ($('#' + errorFieldID).css('display') == 'block' ||
                           $('#' + errorFieldID).css('display') == 'inline') &&
                         messages != -1)
                {
                    // Box ist sichtbar, und es gibt noch Fehler
                    $('#' + errorFieldID).fadeTo(
                        200,
                        1
                    );
                }
            }
        );
        $.scrollTo($('#' + that.formName), 400);
    }

    /**
     * Loescht alle Fehlermeldungen fuer die zu pruefenden Felder.
     */
    this.resetErrorMessages = function()
    {
        $.each(
            this.formFields,
            function(fieldName, definitions)
            {
                var fieldID = that.formName + that.nameSeparator + fieldName + that.nameSeparator + 'Error';
                $('#' + fieldID).remove();
            }
        );
    }

    /**
     * Liest den Wert eines Feldes aus.
     *
     * @param String fieldName
     * @return String
     */
    this.getFormFieldValue = function(fieldName)
    {
        return $('#' + fieldName).val();
    }

    /**
     * Nimmt die zu pruefenden Formularfelder entgegen, sowie die Pruefvor-
     * gaben fuer diese Felder.
     *
     * @var array checkFormFields
     */
    this.setFormFields = function(checkFormFields)
    {
        this.formFields = checkFormFields;
    }
    
    /**
     * Setzt den Formularname
     * 
     * @param String formName
     */
    this.setFormName = function(formName)
    {
        this.formName = formName;
    }

    /**
     * Installiert Eventhandler fuer Buttons, bei denen keine
     * Validierung erfolgen soll.
     *
     * @param array buttons
     */
    this.setNoValidationHandlers = function(buttons)
    {
        var buttonIdx;

        if (typeof buttons != 'object')
        {
            buttons = [buttons];
        }

        for (buttonIdx = 0; buttonIdx < buttons.length; buttonIdx++)
        {
            // Eventhandler setzen
            $('#' + this.formName + '_' + buttons[buttonIdx]).bind(
                'mousedown', function() {
                    that.disableValidation();
                }
            );
        }
    }

    /**
     * Schaltet die Validierung aus.
     */
    this.disableValidation = function()
    {
        this.doValidation = false;
    }

    /**
     * Schaltet die Validierung aus.
     */
    this.enableValidation = function()
    {
        this.doValidation = true;
    }
}
