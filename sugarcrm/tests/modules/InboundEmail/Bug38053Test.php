<?php 
//FILE SUGARCRM flav=pro ONLY
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
 
require_once('include/SugarFolders/SugarFolders.php');
require_once('modules/Campaigns/ProcessBouncedEmails.php');

/**
 * @ticket 38053 
 */
class Bug38053Test extends Sugar_PHPUnit_Framework_TestCase
{
	public $folder = null;
    public $_user = null;
    public $_team = null;
    
	public function setUp()
    {
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
	}

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM user_preferences WHERE assigned_user_id='{$this->_user->id}'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        unset($GLOBALS['current_user']);
    }
    
    /**
     * The purpose of this test is to ensure that the user's default team is properly set on the attachment.
     */
    function testGetNoteBeanForAttachment()
    {
        $GLOBALS['current_user']->team_id = 1;
        $GLOBALS['current_user']->team_set_id = 2;
        
        $ie = new InboundEmail();
        $attach = $ie->getNoteBeanForAttachment('123');
        $this->assertEquals($GLOBALS['current_user']->team_id, $attach->team_id, "Checking that the attachment team_id is equal to the user's default.");
        $this->assertEquals($GLOBALS['current_user']->team_set_id, $attach->team_set_id, "Checking that the attachment team_set_id is equal to the user's default.");
    }
}
?>