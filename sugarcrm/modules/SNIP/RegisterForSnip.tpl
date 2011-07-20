{*
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.Sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: PasswordManager.tpl 37436 2009-06-01 01:14:03Z Faissah $
*}

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