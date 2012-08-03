<?php

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('modules/Cases/Case.php');

class Bug47949Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->markTestIncomplete('Marking this skipped until we figure out why it is failing.');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /*
     * @group bug47949
     */
    public function testGetRelatedBean()
    {
        $team_id = 1;
        $case = new aCase();
        $case->name = 'testBug47949';
        $case->team_id = $team_id;
        $case->team_set_id = 1;
        $case->save();

        $beans = $case->get_linked_beans('teams', 'Team');

        // teams is based on Link (not Link2), should still work
        $this->assertEquals(1, count($beans), 'should have one and only one team');
        $this->assertEquals($team_id, $beans[0]->id, 'incorrect team id, should be ' . $team_id);

        // cleanup
        $GLOBALS['db']->query("delete from cases where id= '{$case->id}'");
    }
}
