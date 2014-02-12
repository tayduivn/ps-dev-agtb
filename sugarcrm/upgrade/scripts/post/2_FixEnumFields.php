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
 * Fix ext4 in enum fields which can be messed up old versions thus causing notices
 */
class SugarUpgradeFixEnumFields extends UpgradeScript
{
    public $order = 2050;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if(version_compare($this->form_version, ">=", "7.2")) {
            return;
        }
        $this->log('Checking for broken enum fields');
        $drop_ids = array();
    	$res = $this->db->query("SELECT * FROM fields_meta_data WHERE type='enum' AND deleted=0 AND ext4 IS NOT NULL AND ext4 != '' AND ext4 != 's:0:\"\";'");
        while($row = $this->db->fetchByAssoc($res, false)) {
            if(empty($row['ext4'])) {
                // shouldn't happen but just in case
                continue;
            }
            $this->log("Dependent enum found with ext4: id {$row['id']} ext4 {$row['ext4']}");
            $drop_ids[] = $this->db->quoted($row['id']);
        }
        if(!empty($drop_ids)) {
            $this->db->query("UPDATE fields_meta_data SET ext4 = '' WHERE id IN (".join(",", $drop_ids).")");
        }
    }
}
