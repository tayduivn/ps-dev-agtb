<form name="RegisterForSnip" method="POST" action="index.php" >
<input type='hidden' name='action' value='OAuth'/>
<input type='hidden' name='module' value='Administration'/>
<input type='hidden' name='sid' value='{$sid}'/>

{if !empty($VERIFY)}
<b>{$MOD.LBL_OAUTH_VALIDATION}: {$VERIFY}</b><br/>
{/if}

<table>
<tr>
<td>{$MOD.LBL_OAUTH_CONSUMERKEY}: </td><td><input name="ckey" value="{$ckey}"/></td>
</tr>
<tr>
<td>{$MOD.LBL_OAUTH_CONSUMERSECRET}: </td><td><input name="csecret" value="{$csecret}"/></td>
</tr>
</table>
<input type="submit" name="cregister" value="{$MOD.LBL_OAUTH_CONSUMER}"/><br/>
</form>
<br/>
<form name="RegisterForSnip" method="POST" action="index.php" >
<input type='hidden' name='action' value='OAuth'/>
<input type='hidden' name='module' value='Administration'/>
<input type='hidden' name='sid' value='{$sid}'/>
<table>
<tr>
<td>{$MOD.LBL_OAUTH_REQUEST}: </td><td><input name="token" value="{$token}"/></td>
</tr>
<tr>
<td>{$MOD.LBL_OAUTH_ROLE}: </td><td>{html_options name="role" id="role" options=$roles}</td>
</tr>
</table>

<input type="submit" name="authorize" value="{$MOD.LBL_OAUTH_AUTHORIZE}"/><br/>
</form>
