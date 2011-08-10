{literal}
<script type='text/javascript' src='include/javascript/overlibmws.js'></script>
<style>
#snip_title {
	float:left;
    margin-bottom:5px;
    font-size:15px;
    display:inline;
}
#snip_title_error {
	color:red;
	font-weight:bold;
}
#snip_summary {
	float:left;
    margin-bottom:10px;
}
#snip_summary_error {
	width:100%;
	background-color:#ffaa99;
	margin-top:3px;
	padding:2px;
	font-weight:bold;
	height:13px;
}

div.snipTitle{
	font-size:28px;
	color:#333333;
	letter-spacing:3px
}
.snipDesc{
	width:auto;
	margin:6px;
	margin-bottom:8px;
}
.snipLicenseWrapper{
	margin:0;
	width:auto;
}

.snipLicenseSubWrapper{
	border: 1px solid black;
	padding: 4px 0 4px 4px;
	width:80%;
	margin:auto;
	margin-top:4px;
}

.snipLicense{
	width:auto;
	color:#7A7A7A;
	overflow:auto;
	height:300px
}
.snipUiWrapper{
	margin:auto;
	width:600px;
	height:47px;
	margin-top:7px;
}


.snipCheckboxWrapper{
	float:left;
	width:375px;
	margin-top:10px
}
.snipCheckbox{
	margin-left:5px
}
.snipButtonWrapper{
	float:right
}
.snipEnableButton{
	height:40px;width:200px
}
.snipCenterButtonWrapper{
	margin:auto;
	height:40px;
	width:200px;
	margin-bottom:10px;
	margin-top:-2px
}

div.snipError{
	position:relative;
	margin:2px 8px 0px 8px; 
	background-color:#ffaa99;
	padding:2px;
	padding-left:4px;
	line-height:16px;
	
}
</style>
{/literal}
{$TITLE}
{* //FILE SUGARCRM flav=pro ONLY *}
	{if $SNIP_STATUS!='notpurchased'}
	<table class="actionsContainer" border="0" cellpadding="0" cellspacing="1" width="100%">
	<tbody>
	    <tr>
	        <td>
	            <form method="post">

	            {if $SNIP_STATUS =='purchased'}
	                <input title="" class="button" name="disable" value="  {$MOD.LBL_SNIP_BUTTON_DISABLE}  " type="submit">
	                <input type='hidden' value='disable_snip' name='snipaction'>
	            {else}
	                <input title="" class="button primary" name="tryagain" value="  {$MOD.LBL_SNIP_BUTTON_RETRY}  " onclick='window.location.reload()' type="button">
	            {/if}

	            &nbsp;<input title="" onclick="document.location.href='index.php?module=Administration&amp;action=index'" class="button" name="cancel" value="  {$MOD.LBL_CANCEL_BUTTON_TITLE}  " type="button">

	            </form>
	        </td>
	    </tr>
	</tbody></table>
{/if}

	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="edit view">
		<tr>
		<td>
			{if $EXTRA_ERROR != ''}
				<div style='color:red;font-weight:bold;margin-left:10px;'>
					{$EXTRA_ERROR}
				</div>
			{/if}
					
			{if $SNIP_STATUS=='notpurchased'}
				<div class='snipDesc'>
					{$MOD.LBL_SNIP_SUMMARY}
				</div>

				
				<div class='snipLicenseWrapper'>
					<div class = 'snipLicenseSubWrapper'>
						<div class='snipLicense'>{$MOD.LBL_SNIP_AGREEMENT}</div>
					</div>
					<div class='snipUiWrapper'>
						<div class='snipCheckboxWrapper'>
							<input type='checkbox' onchange="document.getElementById('enableSnipButton').disabled = !document.getElementById('agreementCheck').checked;" id='agreementCheck' class='snipCheckbox'>
							<label for='agreementCheck' class='snipCheckbox'>{$MOD.LBL_SNIP_AGREE} <a href="http://www.sugarcrm.com/crm/TRUSTe/privacy.html" target="_blank">{$MOD.LBL_SNIP_PRIVACY}</a>.</label>
						</div>
						<div class='snipButtonWrapper'>
						<form method="post">
							<input type='submit' class='snipEnableButton' disabled value='{$MOD.LBL_SNIP_BUTTON_ENABLE}' id='enableSnipButton'>
							<input type='hidden' name='snipaction' value='enable_snip'>
						</form>
						</div>
					</div>

					</div>
				
			{else}

			
			<table border="0" cellpadding="0" cellspacing="1" width="100%">
				<tr width="50%">
					<td scope="row">
						{$MOD.LBL_SNIP_STATUS}:&nbsp;{sugar_help text=$MOD.LBL_SNIP_MOUSEOVER_STATUS}
					</td>
					<td>
						<form name='ToggleSnipStatus' method="POST" action="index.php?module=SNIP&action=RegisterForSnip">
						<input type='hidden' id='save_config' name='save_config' value='0'/>
						{if $SNIP_STATUS == 'purchased'}
							<div id='snip_title'><span style='color:green;font-weight:bold'>{$MOD.LBL_SNIP_STATUS_OK}</span></div>
							<div style='clear:both'></div>
							<div id='snip_summary'>{$MOD.LBL_SNIP_STATUS_OK_SUMMARY}</div>
						{elseif $SNIP_STATUS == 'down'}
							<div id='snip_title'><span style='color:red;font-weight:bold'>{$MOD.LBL_SNIP_STATUS_FAIL}</span></div>
							<div style='clear:both'></div>
							<div id='snip_summary'>{$MOD.LBL_SNIP_STATUS_FAIL_SUMMARY}</div>
						{elseif $SNIP_STATUS == 'purchased_error'}
							<div id='snip_title'><span style='color:red;font-weight:bold'>{$MOD.LBL_SNIP_STATUS_ERROR}</span></div>
							<div style='clear:both'></div>
							<div id='snip_summary'>{$SNIP_ERROR_MESSAGE}<br>
							</div>
						{/if}
						</form>
						<br>
					</td>
				
					<td scope="row" style='width:140px'>
						{$MOD.LBL_SNIP_CALLBACK_URL}:&nbsp;{sugar_help text=$MOD.LBL_SNIP_MOUSEOVER_SERVICE_URL}
					</td>
					<td>
						{$SNIP_URL}
					</td>
					
				</tr>
				<tr>
					
					<td scope="row" style='width:100px'>
						{$MOD.LBL_SNIP_EMAIL}:&nbsp;{sugar_help text=$MOD.LBL_SNIP_MOUSEOVER_EMAIL}
					</td>
					<td>
						{$SNIP_EMAIL}
					</td>
				
					<td scope="row" style='width:170px'>
						{$MOD.LBL_SNIP_SUGAR_URL}:&nbsp;{sugar_help text=$MOD.LBL_SNIP_MOUSEOVER_INSTANCE_URL}
					</td>
					<td>
						{$SUGAR_URL}
					</td>
				</tr>
			</table>
			{/if}

			{if $SNIP_STATUS == 'purchased_error' || $SNIP_STATUS == 'down'}
			<div style='margin-left:4px;margin-top:4px;margin-bottom:7px'> <a href='http://www.sugarcrm.com/crm/case-tracker/submit.html?lsd=supportportal&tmpl=' target='_blank'>{$MOD.LBL_SNIP_SUPPORT}</a></div>
			{/if}

		</td>
		</tr>
	</table>