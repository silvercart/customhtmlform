<% if errorMessages %>
    <div class="error">
        <p>
            <strong>Bitte pr&uuml;fen Sie Ihre Eingaben in folgenden Feldern:</strong>
        </p>
        <ul>
            <% control errorMessages %>
                <li>$fieldname</li>
            <% end_control %>
        </ul>
    </div>
<% end_if %>

<% if messages %>
    <div class="note">
        <ul>
            <% control messages %>
                <li>$message</li>
            <% end_control %>
        </ul>
    </div>
<% end_if %>