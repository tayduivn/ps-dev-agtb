<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
//FILE SUGARCRM flav=int ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */



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
		$email = BeanFactory::getBean('Emails');
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