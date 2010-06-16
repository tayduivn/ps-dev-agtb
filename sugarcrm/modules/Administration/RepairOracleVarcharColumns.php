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
 *Portions created by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: Delete.php,v 1.22 2006/01/17 22:50:52 majed Exp $
 * Description:
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 * *******************************************************************************/

//FILE SUGARCRM flav=ent ONLY

/**
 * This script pulls all columns of type VARCHAR2 and of byte-length semantic  to dynamically update them to character-
 * length semantics.
 */

global $sugar_config;
$db = DBManagerFactory::getInstance();

$userName = strtoupper($sugar_config['dbconfig']['db_user_name']);
$q = "SELECT TABLE_NAME, COLUMN_NAME, CHAR_LENGTH FROM ALL_TAB_COLS WHERE TABLE_NAME IN (SELECT TABLE_NAME FROM USER_TABLES) AND DATA_TYPE = 'VARCHAR2' AND CHAR_USED = 'B' AND OWNER = '{$userName}' ORDER BY TABLE_NAME";
$r = $db->query($q);

$display = '';
while($a = $db->fetchByAssoc($r)) {
	if(isset($_REQUEST['commit']) && $_REQUEST['commit'] == 'true' && !isset($_SESSION['REPAIR_ORACLE_VARCHAR_COLS'])) {
		$db->query("ALTER TABLE {$a['table_name']} MODIFY {$a['column_name']} VARCHAR2({$a['char_length']} CHAR)");
	} else {
		if(!empty($display))
			$display .= "\n";
		$display .= "ALTER TABLE {$a['table_name']} MODIFY {$a['column_name']} VARCHAR2({$a['char_length']} CHAR);";
	}
}

///////////////////////////////////////////////////////////////////////////////
////	OUTPUT
if(isset($_REQUEST['commit']) && $_REQUEST['commit'] == 'true') {
	$_SESSION['REPAIR_ORACLE_VARCHAR_COLS'] = true;
	echo "<br /><div>{$mod_strings['LBL_REPAIR_ORACLE_COMMIT_DONE']}</div>";	
}

if(!empty($display)) {
	$out =<<<eoq
	<div>
		{$mod_strings['LBL_REPAIR_ORACLE_VARCHAR_DESC_LONG_1']}
	</div>
	<br \>
	<div>
		<textarea cols='100' rows='10'>{$display}</textarea>
	</div>
	<div>
		<form name='form' action='index.php' method='POST'>
			<input type='hidden' name='module' value='Administration'>
			<input type='hidden' name='action' value='RepairOracleVarcharColumns'>
			<input type='hidden' name='commit' value='true'>
			<input type='submit' class='button' name='submit' value="   {$mod_strings['LBL_REPAIR_ORACLE_COMMIT']}   ">
		</form>
	</div>
eoq;
	echo $out;
} else {
	echo $mod_strings['LBL_REPAIR_ORACLE_VARCHAR_DESC_LONG_2'];
}
?>