{$TITLE}
<p>
<form action='index.php' method='post' name='Save'>
<input type="hidden" name="module" value="Leads">
<input type="hidden" name="return_module" value="{$RETURN_MODULE}">
<input type="hidden" name="return_action" value="{$RETURN_ACTION}">
<input type="hidden" name="return_id" value="{$RETURN_ID}">
<input type="hidden" name="inbound_email_id" value="{$INBOUND_EMAIL_ID}">
<input type="hidden" name="start" value="{$START}">
<input type="hidden" name="dup_checked" value="true">
<input type="hidden" name="action" value="">
{$INPUT_FIELDS}
<table cellpadding="0" cellspacing="0" width="100%" border="0" >
<tr>
<td>
<table cellpadding="0" cellspacing="0" width="100%" border="0" >
<tr>
<td  valign='top' align='left'>{$FORMBODY}{$FORMFOOTER}{$POSTFORM}</td>
</tr>
</table>
</td>
</tr>
</table>
<p>
