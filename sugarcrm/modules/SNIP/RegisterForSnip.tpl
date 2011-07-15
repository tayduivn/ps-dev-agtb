{*
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
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
<form name="RegisterForSnip" method="POST" action="index.php" >
<input type='hidden' name='action' value='RegisterForSnip'/>
<input type='hidden' name='module' value='SNIP'/>
<input type='hidden' id='save_config' name='save_config' value='0'/>
<table border="0" cellspacing="1" cellpadding="1">
	<tr>
		<td>
		<input title="{$MOD.LBL_CONFIGURE_SNIP}" class="button" onclick="document.getElementById('save_config').value='1'" type="submit" name="button" value="{$MOD.LBL_CONFIGURE_SNIP}">
{if $SNIP_ACTIVE}
		<input title="{$MOD.LBL_DISABLE_SNIP}" class="button" onclick="document.getElementById('save_config').value='disable'" type="submit" name="button" value="{$MOD.LBL_DISABLE_SNIP}">
{/if}
		<input title="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="document.location.href='index.php?module=Administration&action=index'" type="button" name="cancel" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
		</td>
	</tr>
</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>
				<table id="registerForSnip" name="registerForSnip" width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
					<tr>
						<th align="left" scope="row" colspan="4">
							<h4>
								{$MOD.LBL_REGISTER_SNIP}
							</h4>
						</th>
					</tr>
					<tr>
						<!--<td>
							<table width="100%" border="0" cellspacing="0" cellpadding="0">
								<tr>
							-->
									<td  scope="row" width='25%'>
										{$MOD.LBL_SNIP_SUGAR_URL}:
									</td>
									<td width='25%' >
										<input type='text' size='42' disabled='true' name='unique_key' value='{$SUGAR_URL}'>
										<input type='hidden'  name='unique_key' value='{$SUGAR_URL}'>
									</td>
									<td width='25%' colspan='2'>
										&nbsp;
									</td>
								</tr>
								<tr>
									<td  scope="row" width='25%'>
										{$MOD.LBL_SNIP_CALLBACK_URL}:
									</td>
									<td width='25%' >
										<input type='text' size='42' name='snip_url' value='{$SNIP_URL}'>
									</td>
									<td width='25%' colspan='2'>
										&nbsp;
									</td>
								</tr>
							<!--</table>
						</td>
					</tr>-->
				</table>
			</td>
		</tr>
	</table>

<table border="0" cellspacing="1" cellpadding="1">
	<tr>
		<td>
		<input title="{$MOD.LBL_CONFIGURE_SNIP}" class="button" onclick="document.getElementById('save_config').value='1'" type="submit" name="button" value="{$MOD.LBL_CONFIGURE_SNIP}">
{if $SNIP_ACTIVE}
		<input title="{$MOD.LBL_DISABLE_SNIP}" class="button" onclick="document.getElementById('save_config').value='disable'" type="submit" name="button" value="{$MOD.LBL_DISABLE_SNIP}">
{/if}
		<input title="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="document.location.href='index.php?module=Administration&action=index'" type="button" name="cancel" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
		</td>
	</tr>
</table>
{if $SNIP_ACTIVE}
<br/>
<table class="list view" border="1" cellspacing="1" cellpadding="1" width="80%" align="center">
<tr>
<td colspan="3" align="center">{$MOD.LBL_SNIP_STATUS_SUMMARY}</td>
</tr>
<tr class="oddListRowS1">
<th>{$MOD.LBL_SNIP_ACCOUNT}</th>
<th>{$MOD.LBL_SNIP_STATUS|escape}</th>
<th>{$MOD.LBL_SNIP_LAST_SUCCESS}</th>
</tr>
<tr>
<td>{$MOD.LBL_SNIP_ACCOUNT_INSTANCE}</td>
<td>
{if !$SNIP_STATUS_OK}
<span class="error">
{else}
<span>
{/if}
{$SNIP_STATUS}</span></td>
</tr>
{foreach from=$SNIP_ACCTS item=acct}
{if $smarty.foreach.rowIteration.iteration is odd}
	{assign var='_rowColor' value="oddListRowS1"}
{else}
	{assign var='_rowColor' value="evenListRowS1"}
{/if}
<tr class="{$_rowColor}">
<td>{$acct.name|escape}</td>
<td>
{if !$acct.ok}
<span class="error">
{else}
<span>
{/if}
{$acct.status|escape}</span></td>
<td>{$acct.last}</td>
</tr>
{/foreach}
</table>
{/if}
