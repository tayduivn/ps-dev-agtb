<?php
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

 * Description:
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 * *******************************************************************************/

if(ob_get_level() < 1)
	ob_start();
ob_implicit_flush(1);

$persistence = sugar_cache_retrieve('upgrade_wizard_persistence');
if (empty($persistence)) {
    $persistence = array();
}
require_once('modules/UpgradeWizard/uw_utils.php');

switch($_REQUEST['systemCheckStep']) {
	case 'find_all_files':
		ob_end_flush();
		$persistence['files_to_check'] = getFilesForPermsCheck();
        break;

	case 'check_found_files':
		if(empty($persistence['files_to_check'])) {
			logThis('*** ERROR: could not find persistent array of files to check');
			echo $mod_strings['ERR_UW_NO_FILES'];
		} else {
			ob_end_flush();
			$persistence = checkFiles($persistence['files_to_check'], true);
		}
	break;

	case 'check_files_status':
		$ret = ($persistence['filesNotWritable']) ? 'true' : 'false';
		echo $ret;
	break;
}

write_array_to_file('persistence', $persistence, $persist);
