<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
require_once 'modules/Administration/UpgradeHistory.php';

class UpgradeHistoryTest extends Sugar_PHPUnit_Framework_TestCase
{
	public function testCheckForExistingSQL()
    {
        $patchToCheck = new stdClass();
        $patchToCheck->name = 'abc';
        $patchToCheck->id = '';
		//BEGIN SUGARCRM flav=ent ONLY
        if ($GLOBALS['db']->dbType == 'oci8') {
            $GLOBALS['db']->query("INSERT INTO upgrade_history (id, name, md5sum, date_entered) VALUES
('444','abc','444',to_date('2008-12-20 08:08:20','YYYY-MM-DD hh24:mi:ss'))");
            $GLOBALS['db']->query("INSERT INTO upgrade_history (id, name, md5sum, date_entered) VALUES
('555','abc','555',to_date('2008-12-20 08:08:20','YYYY-MM-DD hh24:mi:ss'))");	
		}
		else {
        //END SUGARCRM flav=ent ONLY
            $GLOBALS['db']->query("INSERT INTO upgrade_history (id, name, md5sum, date_entered) VALUES
('444','abc','444','2008-12-20 08:08:20') ");
            $GLOBALS['db']->query("INSERT INTO upgrade_history (id, name, md5sum, date_entered) VALUES
('555','abc','555','2008-12-20 08:08:20')");
        //BEGIN SUGARCRM flav=ent ONLY    
		}
		//END SUGARCRM flav=ent ONLY
		$uh = new UpgradeHistory();
    	$return = $uh->checkForExisting($patchToCheck);
		$this->assertContains($return->id, array('444','555'));
    	
    	$patchToCheck->id = '555';
    	$return = $uh->checkForExisting($patchToCheck);
    	$this->assertEquals($return->id, '444');
    	
    	$GLOBALS['db']->query("delete from upgrade_history where id='444'");
   		$GLOBALS['db']->query("delete from upgrade_history where id='555'");
    }
    
    /**
     * @ticket 44075
     */
    public function testTrackerVisibilityBug44075()
    {
        $uh = new UpgradeHistory();
        $this->assertFalse($uh->tracker_visibility);
    }
}