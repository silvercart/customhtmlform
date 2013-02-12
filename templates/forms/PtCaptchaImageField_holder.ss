
<div>
    {$Title}:<br />
    <input type="text" name="{$Name}Field" />
</div>

<div class="captchaField" style="margin-top: 2px;">
    {$Field}
<% if $Message %>
    <span class="message {$MessageType}">{$Message}</span>
<% end_if %>
</div>