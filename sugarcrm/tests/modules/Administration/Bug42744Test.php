<?php
//FILE SUGARCRM flav=pro ONLY
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
 * Bug 42744:
 *  if team 1 is deleted=1 then upgrade from 5.2.0k > 5.5.1 fails
 * @ticket 42744
 * @author arymarchik@sugarcrm.com
 */
class Bug42744Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        parent::setUp();
    }

    /**
     * Testing repairing of Global Team
     * @group 42744
     * @outputBuffering enabled
     */
    public function testRepairGLobalTeam()
    {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        global $mod_strings;
        $mteam = new Team();
        $mteam->retrieve($mteam->global_team);
        // Dont use Team::mark_deleted because it stops script's execution
        $mteam->deleted = 1;
        $mteam->save();
        include 'modules/Administration/upgradeTeams.php';
        $mteam->retrieve($mteam->global_team);
        $this->assertEquals($mteam->id, $mteam->global_team);
        $this->assertEquals($mteam->deleted, 0);
    }

}
