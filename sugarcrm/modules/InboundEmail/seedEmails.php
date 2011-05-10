<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
//FILE SUGARCRM flav=int ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/



$defaults = array(
	'number' => 10,
	'to_address' => 'email2test@toastedpixel.com',
	'from_address'	=> 'chris@sugarcrm.com',
	'prescript' => 'Test',
	'body_text'	=> 'Testing body text',
);
if(isset($_REQUEST['execute'])) {
	$defaults = $_REQUEST;

	for($i=0; $i<$_REQUEST['number']; $i++) {
		$email = new Email();
		$email->name = $_REQUEST['prescript'] ." ". str_pad($i, 3, "0", STR_PAD_LEFT);
		$email->description = $_REQUEST['body_text'];
		$email->from_addr = $_REQUEST['from_address'];
		$email->to_addrs_arr = array(0 => array('email' => $_REQUEST['to_address']));
		$email->cc_addrs_arr = array();
		$email->bcc_addrs_arr = array();
		$email->saved_attachments = array();
		$email->send();
	}

	echo "<b class='error'>Emails Sent</b><br>";
}


$out =<<<eoq
	<form method="POST">
		<input type="hidden" name="execute" value="1">
		<input type="hidden" name="module" value="InboundEmail">
		<input type="hidden" name="action" value="seedEmails">
	<table>
		<tr>
			<td>
				Number of Test Emails:
			</td>
			<td>
				<input name="number" value="{$defaults['number']}">
			</td>
		</tr>
		<tr>
			<td>
				To Address:
			</td>
			<td>
				<input name="to_address" value="{$defaults['to_address']}">
			</td>
		</tr>
		<tr>
			<td>
				From Address:
			</td>
			<td>
				<input name="from_address" value="{$defaults['from_address']}">
			</td>
		</tr>
		<tr>
			<td>
				Prescript:
			</td>
			<td>
				<input name="prescript" value="{$defaults['prescript']}">
			</td>
		</tr>
		<tr>
			<td>
				Body Text:
			</td>
			<td>
				<input name="body_text" value="{$defaults['body_text']}">
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" value="   GO   ">
			</td>
		</tr>
	</form>
eoq;

echo $out;