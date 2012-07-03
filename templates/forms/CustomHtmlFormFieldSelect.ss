<div id="{$FormName}_{$FieldName}_Box" class="field select<% if errorMessage %> error<% end_if %><% if isRequiredField %> requiredField<% end_if %>">
    <% if errorMessage %>
        <div class="message bad">
            <% control errorMessage %>
            <strong>
                {$message}
            </strong>
            <% end_control %>
        </div>
    <% end_if %>

    <label class="left" for="{$FieldID}">{$Label} $RequiredFieldMarker</label>
    <div class="middleColumn">
        $FieldTag
    </div>
</div>
