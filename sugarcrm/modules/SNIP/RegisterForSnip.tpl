{literal}
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
	border:1px solid #999999;
	background-color:#F5F5F5;
	padding:5px;
	font-size:15px;
	margin:6px
}
.snipLicenseWrapper{
	margin:auto;
	width:600px;
}
.snipLicense{
	width:600px;
	padding:5px;
	overflow:auto;
	height:300px
}
.snipUiWrapper{
	float:left;
	padding:5px;
	width:600px;
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
</style>
{/literal}
{$TITLE}
{* //FILE SUGARCRM flav=pro ONLY *}
{literal}
<script>
	function divExpand(){
		console.log("div expanded");
		return false;	
	}
</script>
{/literal}

	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="edit view">
		<tr>
		<td>
			<center><div class='snipTitle'>SNIP</div></center>
				<div class='snipDesc'>
					{$MOD.LBL_SNIP_SUMMARY}

					<a href='#' onclick='divExpand()' id='snipMoreLink'>{$MOD.LBL_SNIP_MORE}</a>
				</div>

				<br>
					
			{if $SNIP_STATUS=='notpurchased'}
				<div class='snipLicenseWrapper'>
					<div class='snipLicense'>{$MOD.LBL_SNIP_AGREEMENT}</div>
					<div class='snipUiWrapper'>
					<hr>
						<div class='snipCheckboxWrapper'>
							
							<input type='checkbox' onchange="document.getElementById('enableSnipButton').disabled = !document.getElementById('agreementCheck').checked;" id='agreementCheck'class='snipCheckbox'><label for='agreementCheck' class='snipCheckbox'>{$MOD.LBL_SNIP_AGREE}</a>.</label>
						</div>
						<div class='snipButtonWrapper'>
						<input type='button' class='snipEnableButton' disabled value='{$MOD.LBL_SNIP_BUTTON_ENABLE}' id='enableSnipButton'>
						</div>
					</div>

					</div>
				
			{else}

			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td scope="row">
						{$MOD.LBL_SNIP_STATUS}
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
							<div id='snip_summary'>{$MOD.LBL_SNIP_STATUS_ERROR_SUMMARY}<br>
							<div id='snip_summary_error'>{$SNIP_ERROR_MESSAGE}</div></div>
						{/if}
						</form>
						<br>
					</td>
				</tr>
				<tr>
					<td scope="row">
						{$MOD.LBL_SNIP_EMAIL}
					</td>
					<td>
						foo2145@ondemand.sugarcrm.com
					</td>
				</tr>
				<tr>
					<td width="20%" scope="row">
						{$MOD.LBL_SNIP_SUGAR_URL}
					</td>
					<td width="80%">
						{$SUGAR_URL}
					</td>
				</tr>
				
			</table><br>
				{if $SNIP_STATUS =='purchased'}
					<div class='snipCenterButtonWrapper'><input type='button' class='snipEnableButton'  value='{$MOD.LBL_SNIP_BUTTON_DISABLE}' id='enableSnipButton'></div>
				{else}
					<div class='snipCenterButtonWrapper'><input type='button' class='snipEnableButton' onclick='window.location.reload()' value='{$MOD.LBL_SNIP_BUTTON_RETRY}' id='tryAgainButton'></div>
				{/if}
			{/if}

		</td>
		</tr>
	</table>