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

<form name="SnipConfig" method="POST" action="" >
<input type='hidden' id='save_config' name='save_config' value='0'/>


<h2>SNIP</h2>
{if $SNIP_STATUS == 'purchased_enabled'}
	<b>Status message: </b>SNIP is enabled and purchased.<br>
	<b>Additional info: </b>
	<ul>
		<li>This Sugar instance URL: {$SUGAR_URL}</li>
		<li>SNIP service URL: {$SNIP_URL}</li>
	</ul>
	<b>UI options: </b> <input class='button' type='submit' value='Disable Snip' onclick='document.getElementById("save_config").value="disable"'>
{elseif $SNIP_STATUS == 'purchased_disabled'}
	<b>Status message: </b>Snip is currently disabled.<br>
	<b>Additional info: </b>
	<ul>
		<li>This Sugar instance URL: {$SUGAR_URL}</li>
		<li>SNIP service URL: {$SNIP_URL}</li>
	</ul>
	<b>UI options: </b> <input class='button' type='submit' value='Enable Snip' onclick='document.getElementById("save_config").value="enable"'>
{elseif $SNIP_STATUS == 'purchased_down'}
	<b>Status message: </b>Snip is enabled but the SNIP server is down. Sorry but you cannot send requests to the SNIP server right now.<br>
	<b>Additional info: </b>
	<ul>
		<li>This Sugar instance URL: {$SUGAR_URL}</li>
		<li>SNIP service URL: {$SNIP_URL}</li>
	</ul>
{elseif $SNIP_STATUS == 'notpurchased'}
	<b>Status message: </b>This Sugar instance does not have a SNIP license. Would you like to purchase one?<br>
	<b>Additional info: </b>
	<ul>
		<li>This Sugar instance URL: {$SUGAR_URL}</li>
		<li>SNIP service URL: {$SNIP_URL}</li>
	</ul>
	<b>UI options: </b> &lt;link to purchase snip with key {$UNIQUEKEY}, snip user {$SNIP_USER} and snip user password {$SNIP_PASS}&gt;
{/if}
<br><br>
{if $FORM_ERROR}
	<b>Form Error: </b>{$FORM_ERROR}
{elseif $FORM_SUCCESS}
	<b>Form Success Message: </b>{$FORM_SUCCESS}
{/if}