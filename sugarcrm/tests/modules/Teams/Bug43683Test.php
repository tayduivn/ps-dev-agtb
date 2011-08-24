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

require_once('modules/Teams/Team.php');
require_once('modules/Teams/TeamMembership.php');

class Bug43683Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    private $user1;
    /**
     * @var User
     */
    private $user2;

    public function setUp() 
    {
        $this->user1 = SugarTestUserUtilities::createAnonymousUser();
        $this->user2 = SugarTestUserUtilities::createAnonymousUser(false);
        $this->user2->reports_to_id = $this->user1->id;
        $this->user2->save();
    }

    public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group	bug43683
     */
    public function testAddUserToTeamWithManagers()
    {
        $team = new Team();
        $team->retrieve($this->user2->getPrivateTeamID());
        $team->add_user_to_team($this->user2->id);
        $team_membership = new TeamMembership();
        $this->assertTrue($team_membership->retrieve_by_user_and_team($this->user1->id, $team->id), 'Implicit membership exists');
    }
}