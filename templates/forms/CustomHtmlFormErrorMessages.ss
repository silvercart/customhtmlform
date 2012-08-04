<% if errorMessages %>
    <div class="message bad">
        <p>
            <strong><% _t('CustomHtmlFormErrorMessages.CHECK_FIELDS','Please check your input on the following fields:') %></strong>
        </p>
        <ul>
            <% loop errorMessages %>
                <li>$fieldname</li>
            <% end_loop %>
        </ul>
    </div>
<% end_if %>

<% if messages %>
    <div class="message note">
        <ul>
            <% loop messages %>
                <li>$message</li>
            <% end_loop %>
        </ul>
    </div>
<% end_if %>