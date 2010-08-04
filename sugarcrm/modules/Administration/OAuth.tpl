<form name="RegisterForSnip" method="POST" action="index.php" >
<input type='hidden' name='action' value='OAuth'/>
<input type='hidden' name='module' value='Administration'/>

{if !empty($VERIFY)}
{$MOD.LBL_OAUTH_VALIDATION}: {$VERIFY}<br/>
{/if}

<p>{$MOD.LBL_OAUTH_REQUEST}: <input name="token"/><br/>
</p><br/>
<input type="submit" name="authorize" value="{$MOD.LBL_OAUTH_AUTHORIZE}"/><br/>
</form>