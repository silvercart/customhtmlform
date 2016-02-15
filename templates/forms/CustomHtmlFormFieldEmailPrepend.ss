<div id="{$FormName}_{$FieldName}_Box" class="control-group<% if errorMessage %> error<% end_if %>">
    <div class="controls">
        <% if errorMessage %>
        <span class="help-inline"><i class="icon-remove"></i><% with errorMessage %> {$message}<% end_with %></span>
        <% end_if %>
    </div>
    <div class="input-prepend row-fluid">
        <span class="add-on"><label class="control-label" for="{$FieldID}"><i class="icon-envelope"></i></label></span>
        <input class="span10" type="text" placeholder="{$Label}" name="{$FieldName}" id="{$FieldID}">
    </div>
</div>