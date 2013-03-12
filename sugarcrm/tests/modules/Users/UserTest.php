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
require_once 'modules/Users/User.php';

class UserTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_user = null;

    public function setUp()
    {
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testSettingAUserPreference()
    {
        $this->_user->setPreference('test_pref', 'dog');

        $this->assertEquals('dog', $this->_user->getPreference('test_pref'));
    }

    public function testGettingSystemPreferenceWhenNoUserPreferenceExists()
    {
        $GLOBALS['sugar_config']['somewhackypreference'] = 'somewhackyvalue';

        $result = $this->_user->getPreference('somewhackypreference');

        unset($GLOBALS['sugar_config']['somewhackypreference']);

        $this->assertEquals('somewhackyvalue', $result);
    }

    /**
     * @ticket 42667
     */
    public function testGettingSystemPreferenceWhenNoUserPreferenceExistsForEmailDefaultClient()
    {
        if (isset($GLOBALS['sugar_config']['email_default_client'])) {
            $oldvalue = $GLOBALS['sugar_config']['email_default_client'];
        }
        $GLOBALS['sugar_config']['email_default_client'] = 'somewhackyvalue';

        $result = $this->_user->getPreference('email_link_type');

        if (isset($oldvalue)) {
            $GLOBALS['sugar_config']['email_default_client'] = $oldvalue;
        } else {
            unset($GLOBALS['sugar_config']['email_default_client']);
        }

        $this->assertEquals('somewhackyvalue', $result);
    }

    public function testResetingUserPreferences()
    {
        $this->_user->setPreference('test_pref', 'dog');

        $this->_user->resetPreferences();

        $this->assertNull($this->_user->getPreference('test_pref'));
    }

    /**
     * @ticket 36657
     */
    public function testCertainPrefsAreNotResetWhenResetingUserPreferences()
    {
        $this->_user->setPreference('ut', '1');
        $this->_user->setPreference('timezone', 'GMT');

        $this->_user->resetPreferences();

        $this->assertEquals('1', $this->_user->getPreference('ut'));
        $this->assertEquals('GMT', $this->_user->getPreference('timezone'));
    }

    public function testDeprecatedUserPreferenceInterface()
    {
        User::setPreference('deprecated_pref', 'dog', 0, 'global', $this->_user);

        $this->assertEquals('dog', User::getPreference('deprecated_pref', 'global', $this->_user));
    }

    public function testSavingToMultipleUserPreferenceCategories()
    {
        $this->_user->setPreference('test_pref1', 'dog', 0, 'cat1');
        $this->_user->setPreference('test_pref2', 'dog', 0, 'cat2');

        $this->_user->savePreferencesToDB();

        $this->assertEquals(
            'cat1',
            $GLOBALS['db']->getOne(
                "SELECT category FROM user_preferences WHERE assigned_user_id = '{$this->_user->id}' AND category = 'cat1'"
            )
        );

        $this->assertEquals(
            'cat2',
            $GLOBALS['db']->getOne(
                "SELECT category FROM user_preferences WHERE assigned_user_id = '{$this->_user->id}' AND category = 'cat2'"
            )
        );
    }

    public function testGetReporteesWithLeafCount()
    {
        $manager = SugarTestUserUtilities::createAnonymousUser();

        //set up users
        $subManager1 = SugarTestUserUtilities::createAnonymousUser();
        $subManager2 = SugarTestUserUtilities::createAnonymousUser();
        $rep1 = SugarTestUserUtilities::createAnonymousUser();
        $rep2 = SugarTestUserUtilities::createAnonymousUser();
        $rep3 = SugarTestUserUtilities::createAnonymousUser();

        //set up relationships
        $subManager1->reports_to_id = $manager->id;
        $subManager1->save();
        $subManager2->reports_to_id = $manager->id;
        $subManager2->save();
        $rep1->reports_to_id = $subManager1->id;
        $rep1->save();
        $rep2->reports_to_id = $subManager2->id;
        $rep2->save();

        $rep3->status = 'Inactive';
        $rep3->reports_to_id = $subManager2->id;
        $rep3->save();

        //get leaf arrays
        $managerReportees = User::getReporteesWithLeafCount($manager->id);
        $subManager1Reportees = User::getReporteesWithLeafCount($subManager1->id);

        //check normal scenario
        $this->assertEquals("1", $managerReportees[$subManager1->id], "SubManager leaf count did not match");
        $this->assertEquals("0", $subManager1Reportees[$rep1->id], "Rep leaf count did not match");

        //now delete one so we can check the delete code.
        $rep1->mark_deleted($rep1->id);
        $rep1->save();

        //first w/o deleted rows
        $managerReportees = User::getReporteesWithLeafCount($manager->id);
        $this->assertEquals(
            "0",
            $managerReportees[$subManager1->id],
            "SubManager leaf count did not match after delete"
        );

        //now with deleted rows
        $managerReportees = User::getReporteesWithLeafCount($manager->id, true);
        $this->assertEquals("1", $managerReportees[$subManager1->id], "SubManager leaf count did not match");

    }

    /**
     * @group user
     */
    public function testGetReporteesWithLeafCountWithAdditionalFields()
    {
        $manager = SugarTestUserUtilities::createAnonymousUser();

        //set up users
        $subManager1 = SugarTestUserUtilities::createAnonymousUser();
        $subManager2 = SugarTestUserUtilities::createAnonymousUser();
        $rep1 = SugarTestUserUtilities::createAnonymousUser();
        $rep2 = SugarTestUserUtilities::createAnonymousUser();
        $rep3 = SugarTestUserUtilities::createAnonymousUser();

        //set up relationships
        $subManager1->reports_to_id = $manager->id;
        $subManager1->save();
        $subManager2->reports_to_id = $manager->id;
        $subManager2->save();
        $rep1->reports_to_id = $subManager1->id;
        $rep1->save();
        $rep2->reports_to_id = $subManager2->id;
        $rep2->save();

        $rep3->status = 'Inactive';
        $rep3->reports_to_id = $subManager2->id;
        $rep3->save();

        //get leaf arrays
        $managerReportees = User::getReporteesWithLeafCount($manager->id, false, array('first_name'));

        //check normal scenario
        $this->assertEquals("1", $managerReportees[$subManager1->id]['total'], "SubManager leaf count did not match");
        $this->assertEquals($subManager1->first_name, $managerReportees[$subManager1->id]['first_name']);
    }

    public function testGetReporteeManagers()
    {
        $manager = SugarTestUserUtilities::createAnonymousUser();

        //set up users
        $subManager1 = SugarTestUserUtilities::createAnonymousUser();
        $subManager2 = SugarTestUserUtilities::createAnonymousUser();
        $rep1 = SugarTestUserUtilities::createAnonymousUser();
        $rep2 = SugarTestUserUtilities::createAnonymousUser();
        $rep3 = SugarTestUserUtilities::createAnonymousUser();

        //set up relationships
        $subManager1->reports_to_id = $manager->id;
        $subManager1->save();
        $subManager2->reports_to_id = $manager->id;
        $subManager2->save();
        $rep1->reports_to_id = $subManager1->id;
        $rep1->save();
        $rep2->reports_to_id = $subManager2->id;
        $rep2->save();

        $rep3->status = 'Inactive';
        $rep3->reports_to_id = $manager->id;
        $rep3->save();

        $managers = User::getReporteeManagers($manager->id);
        $this->assertEquals("2", count($managers), "Submanager count did not match");
    }

    public function testGetReporteeReps()
    {
        $manager = SugarTestUserUtilities::createAnonymousUser();

        //set up users
        $subManager1 = SugarTestUserUtilities::createAnonymousUser();
        $rep1 = SugarTestUserUtilities::createAnonymousUser();
        $rep2 = SugarTestUserUtilities::createAnonymousUser();
        $rep3 = SugarTestUserUtilities::createAnonymousUser();
        $rep4 = SugarTestUserUtilities::createAnonymousUser();

        //set up relationships
        $subManager1->reports_to_id = $manager->id;
        $subManager1->save();
        $rep1->reports_to_id = $subManager1->id;
        $rep1->save();
        $rep2->reports_to_id = $manager->id;
        $rep2->save();
        $rep3->reports_to_id = $manager->id;
        $rep3->save();

        $rep4->status = 'Inactive';
        $rep4->reports_to_id = $manager->id;
        $rep4->save();

        $reps = User::getReporteeReps($manager->id);
        $this->assertEquals("2", count($reps), "Rep count did not match");
    }
}

