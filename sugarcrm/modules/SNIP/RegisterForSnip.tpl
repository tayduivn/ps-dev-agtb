

	<h2>SNIP</h2>

	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="edit view">
		<tr>
		<td>
			{if $SNIP_STATUS=='notpurchased'}
				SNIP is an automatic email archiving system. It allows you to see emails that were sent to or from your contacts inside SugarCRM, without you having to manually import and link the emails.<br><br>

				In order to use SNIP, you must <a href="{$SNIP_PURCHASEURL}">purchase a license</a> for your SugarCRM instance.
			{else}
			
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td scope="row">
						<slot>SNIP Status</slot>

					</td>

					<td>
						<form name='ToggleSnipStatus' method="POST" action="index.php?module=SNIP&action=RegisterForSnip">
						<input type='hidden' id='save_config' name='save_config' value='0'/>
							

						{if $SNIP_STATUS == 'purchased'}
							<div style='float:left;margin-bottom:5px;font-size:15px;display:inline;'><span style='color:green;font-weight:bold'>Enabled (Service Online)</span></div>
							<div style='clear:both'></div>
							<div style='float:left;margin-bottom:10px'>This instance has a SNIP license, and the service is running.</div>
						{elseif $SNIP_STATUS == 'down'}
							<div style='float:left;margin-bottom:5px;font-size:15px;display:inline;'><span style='color:red;font-weight:bold'>Cannot connect to SNIP server</span></div>
							<div style='clear:both'></div>
							<div style='float:left;margin-bottom:10px'>Sorry, the SNIP service is currently unavailable (either the service is down or the connection failed on your end).</div>
							
						{elseif $SNIP_STATUS == 'purchased_error'}
							<div style='float:left;margin-bottom:5px;font-size:15px;display:inline;'><span style='color:red;font-weight:bold'>Error returned from SNIP server</span></div>
							<div style='clear:both'></div>
							<div style='float:left;margin-bottom:10px'>This instance has a valid SNIP license, but the SNIP server returned the following error message:<br><div style='width:100%;background-color:#ffaa99;margin-top:3px;padding:2px;font-weight:bold'>{$SNIP_ERROR_MESSAGE}</div></div>
						{/if}
						</form>
						<br>
					</td>
				</tr>
				<tr>
					<td width="15%" scope="row">
						<slot>{$MOD.LBL_SNIP_SUGAR_URL}</slot>
					</td>
					<td width="85%">
						<slot>{$SUGAR_URL}</slot>
					</td>
				</tr>
				<tr>
					<td scope="row">
						<slot>{$MOD.LBL_SNIP_CALLBACK_URL}</slot>
					</td>
					<td>
						<slot>{$SNIP_URL}</slot>
					</td>
				</tr>
			</table>
			{/if}
		</td>
		</tr>
	</table>