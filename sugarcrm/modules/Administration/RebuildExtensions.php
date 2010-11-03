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
if(is_admin($current_user)){
	require_once('ModuleInstall/ModuleInstaller.php');
	$mi = new ModuleInstaller();
	$mi->rebuild_all();

	//////////////////////////////////////////////////////////////////////////////
	// Remove the "Rebuild Extensions" red text message on admin logins
	
	echo "Updating the admin warning message...<BR>";
	
	// clear the database row if it exists (just to be sure)
	$query = "DELETE FROM versions WHERE name='Rebuild Extensions'";
	$GLOBALS['log']->info($query);
	$GLOBALS['db']->query($query);
	
	// insert a new database row to show the rebuild extensions is done
	$id = create_guid();
	$gmdate = gmdate($GLOBALS['timedate']->get_db_date_time_format());
	$date_entered = db_convert("'$gmdate'", 'datetime');
	$query = 'INSERT INTO versions (id, deleted, date_entered, date_modified, modified_user_id, created_by, name, file_version, db_version) '
		. "VALUES ('$id', '0', $date_entered, $date_entered, '1', '1', 'Rebuild Extensions', '4.0.0', '4.0.0')"; 
	$GLOBALS['log']->info($query);
	$GLOBALS['db']->query($query);
	
	// unset the session variable so it is not picked up in DisplayWarnings.php
	if(isset($_SESSION['rebuild_extensions'])) {
	    unset($_SESSION['rebuild_extensions']);
	}

	echo 'done';
}else{
	die('Admin Only Section');	
}
?>