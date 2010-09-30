<div id="{$FormName}_{$FieldName}_Box" class="type-text<% if errorMessage %> error<% end_if %>">
    <% if errorMessage %>
        <p class="message">
            $errorMessage
        </p>
    <% end_if %>

    <p><label for="{$FormName}_{$FieldName}">{$Label}: </label></p>
    $FieldTag
</div>