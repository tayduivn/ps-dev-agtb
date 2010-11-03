<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
if(!empty($_SESSION['distribute_where']) && !empty($_REQUEST['distribute_method']) && !empty($_REQUEST['users']) && !empty($_REQUEST['use'])) {
	
	$focus = new Email();
		
	$emailIds = array();
	// CHECKED ONLY:
	if($_REQUEST['use'] == 'checked') {
		// clean up passed array
		$grabEx = explode('::',$_REQUEST['grabbed']);
		foreach($grabEx as $k => $emailId) {
			if($emailId != "undefined") {
				$emailIds[] = $emailId;
			}
		}
		
		// we have users and the items to distribute	
		if($_REQUEST['distribute_method'] == 'roundRobin') {
			if($focus->distRoundRobin($_REQUEST['users'], $emailIds)) {
				header('Location: index.php?module=Emails&action=ListViewGroup');
			}	
		} elseif($_REQUEST['distribute_method'] == 'leastBusy') {
			if($focus->distLeastBusy($_REQUEST['users'], $emailIds)) {
				header('Location: index.php?module=Emails&action=ListViewGroup');
			}
		} elseif($_REQUEST['distribute_method'] == 'direct') {
			// direct assignment
//			_ppd('count:'.count($_REQUEST['users']));
			if(count($_REQUEST['users']) > 1) {
				// only 1 user allowed in direct assignment
				$error = 1;
			} else {
				$user = $_REQUEST['users'][0];
				if($focus->distDirect($user, $emailIds)) {
					header('Location: index.php?module=Emails&action=ListViewGroup');
				}
			}
			
			header('Location: index.php?module=Emails&action=ListViewGroup&error='.$error);
		}
	} elseif($_REQUEST['use'] == 'all') {
		if($_REQUEST['distribute_method'] == 'direct') {
			// no ALL assignments to 1 user
			header('Location: index.php?module=Emails&action=ListViewGroup&error=2');
		}
		
		// we have the where clause that generated the view above, so use it
		$q = 'SELECT emails.id FROM emails WHERE '.$_SESSION['distribute_where'];
		$q = str_replace('&#039;', '"', $q);
		$r = $focus->db->query($q);
		$count = 0;
		while($a = $focus->db->fetchByAssoc($r)) {
			$emailIds[] = $a['id'];
			$count++;
		}
		// we have users and the items to distribute	
		if($_REQUEST['distribute_method'] == 'roundRobin') {
			if($focus->distRoundRobin($_REQUEST['users'], $emailIds)) {
				header('Location: index.php?module=Emails&action=ListViewGroup');
			}
		} elseif($_REQUEST['distribute_method'] == 'leastBusy') {
			if($focus->distLeastBusy($_REQUEST['users'], $emailIds)) {
				header('Location: index.php?module=Emails&action=ListViewGroup');
			}
		}
		
		if($count < 1) {
			$GLOBALS['log']->info('Emails distribute failed: query returned no results ('.$q.')');
			header('Location: index.php?module=Emails&action=ListViewGroup&error='.$error);
		}
	}

} else {
	// error
	header('Location: index.php?module=Emails&action=index');
}

?>
