<div id="{$FormName}_{$FieldName}_Box" class="control-group<% if errorMessage %> error<% end_if %><% if isRequiredField %> requiredField<% end_if %>">
<% if errorMessage %>
    <div class="alert alert-error">
        <% loop errorMessage %>
        <p>{$message}</p>
        <% end_loop %>
    </div>
<% end_if %>

    $FieldHolder
</div>