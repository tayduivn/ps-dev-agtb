<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * Apply "repair&rebuild" to each bean's table
 * Rebuild relationships
 */
class SugarUpgradeRebuild extends UpgradeScript
{
    public $order = 2100;
    public $type = self::UPGRADE_ALL;

    public function run()
    {
        global $dictionary, $beanFiles;
        include "include/modules.php";
        require_once("modules/Administration/QuickRepairAndRebuild.php");
        $rac = new RepairAndClear('', '', false, false);
        $rac->clearVardefs();
        $rac->rebuildExtensions();
        $rac->clearExternalAPICache();
        // this is dirty, but otherwise SugarBean caches old defs :(
        $GLOBALS['reload_vardefs'] = true;
        $repairedTables = array();
        foreach ($beanFiles as $bean => $file) {
    	    if(file_exists($file)){
		        unset($GLOBALS['dictionary'][$bean]);
		        require_once($file);
		        $focus = new $bean ();
		        if(empty($focus->table_name) || isset($repairedTables[$focus->table_name])) {
		           continue;
		        }

        		if (($focus instanceOf SugarBean)) {
		        	if(!isset($repairedTables[$focus->table_name])) {
				            $sql = $this->db->repairTable($focus, true);
                            if(trim($sql) != '') {
				                $this->log('Running sql: ' . $sql);
                            }
				            $repairedTables[$focus->table_name] = true;
			        }

        			//Check to see if we need to create the audit table
		            if($focus->is_AuditEnabled() && !$focus->db->tableExists($focus->get_audit_table_name())){
                        $this->log('Creating audit table:' . $focus->get_audit_table_name());
		                $focus->create_audit_table();
                    }
		        }
	        }
        }

        unset ($dictionary);
        include ("modules/TableDictionary.php");
        foreach ($dictionary as $meta) {
	        $tablename = $meta['table'];

	        if(isset($repairedTables[$tablename])) {
	           continue;
	        }

	        $fielddefs = $meta['fields'];
	        $indices = $meta['indices'];
	        $sql = $this->db->repairTableParams($tablename, $fielddefs, $indices, true);
	        if(!empty($sql)) {
	            $this->log('Running sql: '. $sql);
	            $repairedTables[$tablename] = true;
	        }

        }

        $this->log('Database repaired');

        $this->log('Start rebuilding relationships');
        $_REQUEST['silent'] = true;
        include('modules/Administration/RebuildRelationship.php');
        $_REQUEST['upgradeWizard'] = true;
        include('modules/ACL/install_actions.php');
        $this->log('Done rebuilding relationships');
        unset($GLOBALS['reload_vardefs']);
    }
}
