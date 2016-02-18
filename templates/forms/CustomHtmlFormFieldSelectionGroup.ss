<div id="{$FormName}_{$FieldName}_Box" class="control-group type-selectiongroup clearfix <% if errorMessage %> error<% end_if %><% if isRequiredField %> requiredField<% end_if %>">
    <label for="{$FieldID}">{$Label} $RequiredFieldMarker</label>
    <div class="controls">
        {$FieldTag}
        <% if errorMessage %>
        <span class="help-inline"><i class="icon-remove"></i><% with errorMessage %> {$message}<% end_with %></span>
        <% end_if %>
    </div>
</div>
