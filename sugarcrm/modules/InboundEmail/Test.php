<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement 
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.  
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may 
 *not use this file except in compliance with the License. Under the terms of the license, You 
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or 
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or 
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit 
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the 
 *Software without first paying applicable fees is strictly prohibited.  You do not have the 
 *right to remove SugarCRM copyrights from the source code or user interface. 
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and 
 * (ii) the SugarCRM copyright notice 
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer 
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.  
 ********************************************************************************/
/*********************************************************************************
 * $Id: Test.php 51719 2009-10-22 17:18:00Z mitani $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
//FILE SUGARCRM flav=int ONLY
 
global $dictionary;


$ie = new InboundEmail();
$r = $ie->db->query('SELECT id,name FROM inbound_email WHERE deleted=0 AND status=\'Active\' AND mailbox_type != \'bounce\'');

while($a = $ie->db->fetchByAssoc($r)) {
	$ieX = new InboundEmail();
	$ieX->retrieve($a['id']);
	$ieX->connectMailserver();

	echo "<b>Polling [ {$a['name']} ]</b><br>";
	
	//$newMsgs = $ieX->getNewMessageIds();
	$newMsgs = array();
	if ($ieX->isPop3Protocol()) {
		$newMsgs = $ieX->getPop3NewMessagesToDownload();
	} else {
		$newMsgs = $ieX->getNewMessageIds();
	}
	
	if(is_array($newMsgs)) {
		foreach($newMsgs as $k => $msgNo) {
			echo "got a message [ <b>{$msgNo}</b> ]<br>";
			$uid = $msgNo;
			if ($ieX->isPop3Protocol()) {
				$uid = $ieX->getUIDLForMessage($msgNo);
			} else {
				$uid = imap_uid($ieX->conn, $msgNo);
			} // else
			$ieX->importOneEmail($msgNo, $uid);
		}
	}
	imap_expunge($ieX->conn);
	imap_close($ieX->conn);
}
echo 'done<br>';
?>
