<div id="{$FormName}_{$FieldName}_Box" class="type-selectiongroup clearfix <% if errorMessage %> error<% end_if %><% if isRequiredField %> requiredField<% end_if %>">
    <% if errorMessage %>
        <div class="errorList">
            <% control errorMessage %>
            <strong class="message">
                {$message}
            </strong>
            <% end_control %>
        </div>
    <% end_if %>

    <label for="{$FieldID}">{$Label} </label>
    $FieldTag
</div>