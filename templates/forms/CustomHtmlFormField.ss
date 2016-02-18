<div id="{$FormName}_{$FieldName}_Box" class="control-group type-text <% if errorMessage %>error<% end_if %>">
    <label class="control-label" for="{$FieldID}">{$Label}
        <% if isRequiredField %>
        <span class="<% if errorMessage %>text-error<% end_if %>">{$RequiredFieldMarker}</span>
        <% end_if %>
    </label>

    <div class="controls">
        {$FieldTag}
        <% if errorMessage %>
        <span class="help-inline"><i class="icon-remove"></i><% with errorMessage %> {$message}<% end_with %></span>
        <% end_if %>
    </div>
</div>