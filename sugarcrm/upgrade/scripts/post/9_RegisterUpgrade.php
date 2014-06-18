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
 * Register upgrade with the system
 */
class SugarUpgradeRegisterUpgrade extends UpgradeScript
{
    public $order = 9900;
    public $type = self::UPGRADE_DB;

    public function run()
    {
	    // if error was encountered, script should have died before now
		$new_upgrade = new UpgradeHistory();
		$new_upgrade->filename = $this->context['zip'];
		if(file_exists($this->context['zip'])) {
		  $new_upgrade->md5sum = md5_file($this->context['zip']);
		} else {
		    // if file is not there, just md5 the filename
		  $new_upgrade->md5sum = md5($this->context['zip']);
		}
        $dup = $this->db->getOne("SELECT id FROM upgrade_history WHERE md5sum='{$new_upgrade->md5sum}'");
        if($dup) {
            $this->error("Duplicate install for package, md5: {$new_upgrade->md5sum}");
            // Not failing it - by now there's no point, we're at the end anyway
            return;
        }
		$new_upgrade->name = pathinfo($this->context['zip'], PATHINFO_FILENAME);
		$new_upgrade->description = $this->manifest['description'];
		$new_upgrade->type = 'patch';
		$new_upgrade->version = $this->to_version;
		$new_upgrade->status = "installed";
		$new_upgrade->manifest = base64_encode(serialize($this->manifest));
		$new_upgrade->save();
    }
}
