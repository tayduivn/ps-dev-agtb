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
</style>
{/literal}
{$TITLE}
{* //FILE SUGARCRM flav=pro ONLY *}
{literal}
<script>
	function divExpand(){
		console.log("HELLO. I AM YOUR COMPUTER. TYPE INTO THE CONSOLE TO SPEAK WITH ME");
		return false;	
	}
	
</script>
{/literal}

	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="edit view">
		<tr>
		<td>
			<center><div style='font-size:28px;color:#333333;letter-spacing:3px'>SNIP</div></center>
				<div style='width:auto;border:1px solid #999999;background-color:#F5F5F5;padding:5px;font-size:15px;margin:6px'>
					SNIP is an automatic email importing service that allows users to import emails into Sugar by cc'ing or forwarding emails from any email client or service to a Sugar-provided email address.  Email records are created in Sugar for each email that is imported and are automatically related to contacts and other records in Sugar based on matching email addresses.

					<a href='#' onclick='divExpand()' id='snipMoreLink'>More...</a>
				</div>

				<br>
					
			{if $SNIP_STATUS=='notpurchased'}
				<div style='margin:auto;width:600px;'>
						<div style='width:600pxpadding:5px;overflow:auto;height:300px'>
							
							<p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus</p><br><p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus</p><br><p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus</p><br><p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, commodo vitae, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. Donec non enim in turpis pulvinar facilisis. Ut felis. Praesent dapibus, neque id cursus faucibus, tortor neque egestas augue, eu vulputate magna eros eu erat. Aliquam erat volutpat. Nam dui mi, tincidunt quis, accumsan porttitor, facilisis luctus, metus</p>
					</div>
					<div style='float:left;padding:5px;width:600px;'>
					<hr>
						<script>
							{literal}
							function cbChange(){
								document.getElementById('enableSnipButton').disabled = !document.getElementById('agreementCheck').checked;
							}
							{/literal}
						</script>
						<div style='float:left;width:375px;margin-top:10px'>
							
							<input type='checkbox' onchange='cbChange()' id='agreementCheck' style='margin-left:5px'><label for='agreementCheck' style='margin-left:5px'>I agree to the above terms and the <a href='javascript:alert("ENTER PRIVACY AGREEMENT")'>privacy agreement</a>.</label>
						</div>
						<div style='float:right'>
						<input type='button' style='height:40px;width:200px' disabled value='Enable Snip' id='enableSnipButton'>
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
						SNIP Email
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
				<div style='margin:auto;height:40px;width:200px;margin-bottom:10px;margin-top:-2px'><input type='button' style='height:40px;width:200px;'  value='Disable Snip' id='enableSnipButton'></div>
			{else}
				<div style='margin:auto;height:40px;width:200px;margin-bottom:10px;margin-top:-2px'><input type='button' style='height:40px;width:200px;' onclick='window.location.reload()' value='Try Connecting Again' id='tryAgainButton'></div>
			{/if}
			{/if}

		</td>
		</tr>
	</table>