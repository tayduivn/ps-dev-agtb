<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
/*********************************************************************************

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
//FILE SUGARCRM flav=int ONLY
 
global $dictionary;


$ie = BeanFactory::getBean('InboundEmail');
$r = $ie->db->query('SELECT id,name FROM inbound_email WHERE deleted=0 AND status=\'Active\' AND mailbox_type != \'bounce\'');

while($a = $ie->db->fetchByAssoc($r)) {
	$ieX = BeanFactory::getBean('InboundEmail', $a['id']);
    $ieX->disable_row_level_security = true;
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
