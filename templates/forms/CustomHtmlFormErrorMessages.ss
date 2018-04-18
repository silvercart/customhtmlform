<% if errorMessages %>
    <div class="alert alert-error">
        <p><strong><%t CustomHtmlForm\Forms\CustomHtmlForm.ERROR_CHECK_FIELDS 'Please check your input on the following fields:' %></strong></p>
        <ul>
        <% loop errorMessages %>
            <li>$fieldname</li>
        <% end_loop %>
        </ul>
    </div>
<% end_if %>

<% if messages %>
    <div class="alert alert-info">
    <% loop messages %>
        <p>$message</p>
    <% end_loop %>
    </div>
<% end_if %>