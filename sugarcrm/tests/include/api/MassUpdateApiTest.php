<?php
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




require_once 'include/api/RestService.php';
require_once 'clients/base/api/MassUpdateApi.php';

/*
 * Tests mass update Rest api.
 */
class MassUpdateApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp(){
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
    }

    /*
     * This function simulates job queue to call MassUpdateJob::run().
     * @return Boolean false when error occurs, otherwise true
     */
    protected function runJob($id) {
        $schedulerJob = new SchedulersJob();
        $schedulerJob->retrieve($id);

        $job = new MassUpdateJob();
        $job->run($schedulerJob, $schedulerJob->data);

        return true;
    }

    /*
     * This function tests mass delete with given ids.
     * This function creates 2 contacts.
     * When doing mass delete, both contacts should be deleted.
     */
    public function testMassDeleteSelectedIds()
    {
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'delete' => true,
                'uid' => array($contact1->id, $contact2->id),
            ),
        );

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        global $db;
        $rec = $db->query("select deleted from contacts where id='{$contact1->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['deleted'], 'deleted should be set to 1');
        }

        $rec = $db->query("select deleted from contacts where id='{$contact2->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['deleted'], 'deleted should be set to 1');
        }
    }

    /*
     * This function tests mass delete with given ids using asynchronous mode.
     * This function creates 2 contacts.
     * When doing mass delete, both contacts should be deleted.
     */
    public function testMassDeleteSelectedIdsAsync()
    {
        global $sugar_config;
        if (isset($sugar_config['max_mass_update'])) {
            $cur_val = $sugar_config['max_mass_update'];
        }
        $sugar_config['max_mass_update'] = 1; // to trigger the async mode

        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'delete' => true,
                'uid' => array($contact1->id, $contact2->id),
            ),
        );

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        $this->runJob($apiClass->getJobId());

        // restore old value
        if (isset($cur_val)) {
            $sugar_config['max_mass_update'] = $cur_val;
        } else {
            unset($sugar_config['max_mass_update']);
        }

        global $db;
        $rec = $db->query("select deleted from contacts where id='{$contact1->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['deleted'], 'deleted should be set to 1');
        }

        $rec = $db->query("select deleted from contacts where id='{$contact2->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['deleted'], 'deleted should be set to 1');
        }
    }

    /*
     * This function tests mass delete entire list without any search filter.
     * This function creates 2 contacts.
     * When doing mass delete, both contacts should be deleted.
     */
    public function testMassDeleteEntireListWithoutSearch()
    {
        if (isset($_REQUEST)) {
            unset($_REQUEST);
        }
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'delete' => true,
                'entire' => true, // entire selected list
            ),
        );

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        $this->runJob($apiClass->getJobId());

        global $db;
        $rec = $db->query("select deleted from contacts where id='{$contact1->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['deleted'], 'deleted should be set to 1');
        }

        $rec = $db->query("select deleted from contacts where id='{$contact2->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['deleted'], 'deleted should be set to 1');
        }
    }

    /*
     * This function tests mass delete entire list with basic search filter.
     * This function creates 3 contacts, with two of them have first name "Airline*".
     * Then we create a basic search filter to search for the contacts that have first_name start with "airline".
     * When doing mass delete, only the contacts with first_name starting with "airline" should be deleted.
     */
    public function testMassDeleteEntireListWithBasicSearch()
    {
        $contact1 = SugarTestContactUtilities::createContact();
        $contact1->first_name = 'Airline1';
        $contact1->save();

        $contact2 = SugarTestContactUtilities::createContact();
        $contact2->first_name = 'Airline2';
        $contact2->save();

        $contact3 = SugarTestContactUtilities::createContact();
        $contact3->first_name = 'SomethingElse';
        $contact3->save();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'entire' => true, // entire selected list
                'delete' => true,
                'current_search' => array( // this is the search filter
                    'search_type' => 'basic', // to indicate this is for base search
                    'name' => 'airline', // search for first_name
                ),
            ),
        );

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        $this->runJob($apiClass->getJobId());

        global $db;
        // this should be deleted since the contact's first_name matches
        $rec = $db->query("select deleted from contacts where id='{$contact1->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['deleted'], 'deleted should be set to 1');
        }

        // this should be deleted since the contact's first_name matches
        $rec = $db->query("select deleted from contacts where id='{$contact2->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['deleted'], 'deleted should be set to 1');
        }

        // this should not be deleted since the contact's first_name does not match
        $rec = $db->query("select deleted from contacts where id='{$contact3->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(0, $row['deleted'], 'deleted should remain 0');
        }
    }

    /*
     * This function tests mass delete entire list with advanced search filter.
     * This function creates 5 contacts.
     * Then we create a advanced search filter to search for the contacts that have particular first_name
     * and are assigned to a particular user.
     * When doing mass delete, only the contacts matching both first_name and assigned user should be deleted.
     */
    public function testMassDeleteEntireListWithAdvancedSearch()
    {
        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();
        $user3 = SugarTestUserUtilities::createAnonymousUser();

        // both first_name and assigned_user_id match, will be deleted
        $contact1 = SugarTestContactUtilities::createContact();
        $contact1->first_name = 'Airline1';
        $contact1->assigned_user_id = $user1->id;
        $contact1->save();

        // both first_name and assigned_user_id match, will be deleted
        $contact2 = SugarTestContactUtilities::createContact();
        $contact2->first_name = 'Airline2';
        $contact2->assigned_user_id = $user2->id;
        $contact2->save();

        // only first_name matches, will not be deleted
        $contact3 = SugarTestContactUtilities::createContact();
        $contact3->first_name = 'Airline3';
        $contact3->assigned_user_id = $user3->id;
        $contact3->save();

        // only assigned_user_id matches, will not be deleted
        $contact4 = SugarTestContactUtilities::createContact();
        $contact4->first_name = 'SomethingElse';
        $contact4->assigned_user_id = $user1->id;
        $contact4->save();

        // neither first_name nor assigned_user_id matches, will not be deleted
        $contact5 = SugarTestContactUtilities::createContact();
        $contact5->first_name = 'SomethingElse';
        $contact5->assigned_user_id = $user3->id;
        $contact5->save();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'entire' => true, // entire selected list
                'delete' =>  true,
                'current_search' => array( // this is the search filter for first_name AND assigned_user_id
                    'search_type' => 'advanced', // to indicate this is for advanced search
                    'first_name' => 'airline', // search for first_name
                    'assigned_user_id' => array( // search for assigned_user_id for either user1 OR user2
                        0 => $user1->id,
                        1 => $user2->id,
                    ),
                ),
            ),
        );

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        $this->runJob($apiClass->getJobId());

        global $db;
        // contact1 and contact2 should be updated
        $rec = $db->query("select deleted from contacts where id='{$contact1->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['deleted'], 'deleted should be set to 1');
        }

        $rec = $db->query("select deleted from contacts where id='{$contact2->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['deleted'], 'deleted should be set to 1');
        }

        // contact3, contact4 and contact5 should remain the same
        $rec = $db->query("select deleted from contacts where id='{$contact3->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(0, $row['deleted'], 'deleted should remain 0');
        }

        $rec = $db->query("select deleted from contacts where id='{$contact4->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(0, $row['deleted'], 'deleted should remain 0');
        }

        $rec = $db->query("select deleted from contacts where id='{$contact5->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(0, $row['deleted'], 'deleted should remain 0');
        }
    }

    /*
     * This function tests mass update do_not_call field with given ids.
     * This function creates 2 contacts.
     * When doing mass update, both contacts should be updated.
     */
    public function testMassUpdateSelectedIds()
    {
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'uid' => array($contact1->id, $contact2->id),
                'do_not_call' => 1,
            ),
        );

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        global $db;
        $rec = $db->query("select do_not_call from contacts where id='{$contact1->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['do_not_call'], 'do_not_call should be set to 1');
        }

        $rec = $db->query("select do_not_call from contacts where id='{$contact2->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['do_not_call'], 'do_not_call should be set to 1');
        }
    }

    /*
     * This function tests mass update do_not_call field with given ids using asynchronous mode.
     * This function creates 2 contacts.
     * When doing mass update, both contacts should be updated.
     */
    public function testMassUpdateSelectedIdsAsync()
    {
        global $sugar_config;
        if (isset($sugar_config['max_mass_update'])) {
            $cur_val = $sugar_config['max_mass_update'];
        }
        $sugar_config['max_mass_update'] = 1; // to trigger the async mode

        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'uid' => array($contact1->id, $contact2->id),
                'do_not_call' => 1,
            ),
        );

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        $this->runJob($apiClass->getJobId());

        // restore old value
        if (isset($cur_val)) {
            $sugar_config['max_mass_update'] = $cur_val;
        } else {
            unset($sugar_config['max_mass_update']);
        }

        global $db;
        $rec = $db->query("select do_not_call from contacts where id='{$contact1->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['do_not_call'], 'do_not_call should be set to 1');
        }

        $rec = $db->query("select do_not_call from contacts where id='{$contact2->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['do_not_call'], 'do_not_call should be set to 1');
        }
    }

    /*
     * This function tests mass update do_not_call field with given ids using asynchronous mode in 2 separate jobs.
     * This function creates 2 contacts.
     * When doing mass update, both contacts should be updated.
     */
    public function testMassUpdateSelectedIdsAsync2()
    {
        global $sugar_config;
        if (isset($sugar_config['max_mass_update'])) {
            $cur_val = $sugar_config['max_mass_update'];
        }
        $sugar_config['max_mass_update'] = 0; // to trigger the async mode

        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];
        $args = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'uid' => array($contact1->id),
                'do_not_call' => 1,
            ),
        );

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        $api2 = new RestService();
        $api2->user = $GLOBALS['current_user'];
        $args2 = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'uid' => array($contact2->id),
                'do_not_call' => 1,
            ),
        );

        $apiClass2 = new MassUpdateApi();
        $apiClass2->massUpdate($api2, $args2);

        $this->runJob($apiClass->getJobId());
        $this->runJob($apiClass2->getJobId());

        // restore old value
        if (isset($cur_val)) {
            $sugar_config['max_mass_update'] = $cur_val;
        } else {
            unset($sugar_config['max_mass_update']);
        }

        global $db;
        $rec = $db->query("select do_not_call from contacts where id='{$contact1->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['do_not_call'], 'do_not_call should be set to 1');
        }

        $rec = $db->query("select do_not_call from contacts where id='{$contact2->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['do_not_call'], 'do_not_call should be set to 1');
        }
    }

    /*
     * This function tests mass update team_id field with given ids.
     * This function creates 2 contacts and one team.
     * When doing mass update, both contacts should be updated.
     */
    public function testMassUpdateContactTeams()
    {
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();
        $team = SugarTestTeamUtilities::createAnonymousTeam();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'uid' => array($contact1->id, $contact2->id),
                'team_name' => array(
                    0 => array('id' => $team->id, 'primary' => true),
                ),
                'team_name_type' => 'replace',
            ),
        );

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        global $db;
        $rec = $db->query("select team_id from contacts where id='{$contact1->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals($team->id, $row['team_id'], 'team_id not updated properly for contact1');
        }

        $rec = $db->query("select team_id from contacts where id='{$contact2->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals($team->id, $row['team_id'], 'team_id not updated properly for contact2');
        }
    }

    /*
     * This function tests mass update team_id field with given ids using asynchronous mode.
     * This function creates 2 contacts and one team.
     * When doing mass update, both contacts should be updated.
     */
    public function testMassUpdateContactTeamsAsync()
    {
        global $sugar_config;
        if (isset($sugar_config['max_mass_update'])) {
            $cur_val = $sugar_config['max_mass_update'];
        }
        $sugar_config['max_mass_update'] = 1; // to trigger the async mode

        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();
        $team = SugarTestTeamUtilities::createAnonymousTeam();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'uid' => array($contact1->id, $contact2->id),
                'team_name' => array(
                    0 => array('id' => $team->id, 'primary' => true),
                ),
                'team_name_type' => 'replace',
            ),
        );

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        $this->runJob($apiClass->getJobId());

        // restore old value
        if (isset($cur_val)) {
            $sugar_config['max_mass_update'] = $cur_val;
        } else {
            unset($sugar_config['max_mass_update']);
        }

        global $db;
        $rec = $db->query("select team_id from contacts where id='{$contact1->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals($team->id, $row['team_id'], 'team_id not updated properly for contact1');
        }

        $rec = $db->query("select team_id from contacts where id='{$contact2->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals($team->id, $row['team_id'], 'team_id not updated properly for contact2');
        }
    }

    /*
     * This function tests mass update team_set_id field with given one contact id.
     * This function creates 1 contact and two teams.
     * When doing mass update, a team_set should be automatically created and should contain those two teams.
     * team_set_id of the contact should be updated.
     */
    public function testMassUpdateContactTeamSet()
    {
        $contact1 = SugarTestContactUtilities::createContact();
        $team1 = SugarTestTeamUtilities::createAnonymousTeam();
        $team2 = SugarTestTeamUtilities::createAnonymousTeam();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'uid' => array($contact1->id),
                'team_name' => array(
                    0 => array('id' => $team1->id, 'primary' => true),
                    1 => array('id' => $team2->id, 'primary' => false),
                ),
                'team_name_type' => 'replace',
            ),
        );

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        global $db;
        $rec = $db->query("select team_set_id from contacts where id='{$contact1->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $team_set_id = $row['team_set_id'];
            $expectedTeamIDs = array($team1->id, $team2->id);
            $actualTeamIDs = array();
            $rec = $db->query("select team_id from team_sets_teams where team_set_id='{$team_set_id}'");
            while ($row = $db->fetchByAssoc($rec))
            {
                $actualTeamIDs[] = $row['team_id'];
            }
            $this->assertEmpty(array_diff($expectedTeamIDs, $actualTeamIDs), 'team_set_id not updated properly for contact1');
        } else {
            $this->assertTrue(false, 'could not get team_set_id');
        }
    }

    /*
     * This function tests mass update team_set_id field with given one contact id using asynchronous mode.
     * This function creates 1 contact and two teams.
     * When doing mass update, a team_set should be automatically created and should contain those two teams.
     * team_set_id of the contact should be updated.
     */
    public function testMassUpdateContactTeamSetAsync()
    {
        global $sugar_config;
        if (isset($sugar_config['max_mass_update'])) {
            $cur_val = $sugar_config['max_mass_update'];
        }
        $sugar_config['max_mass_update'] = 1; // to trigger the async mode

        $contact1 = SugarTestContactUtilities::createContact();
        $team1 = SugarTestTeamUtilities::createAnonymousTeam();
        $team2 = SugarTestTeamUtilities::createAnonymousTeam();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'uid' => array($contact1->id),
                'team_name' => array(
                    0 => array('id' => $team1->id, 'primary' => true),
                    1 => array('id' => $team2->id, 'primary' => false),
                ),
                'team_name_type' => 'replace',
            ),
        );

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        $this->runJob($apiClass->getJobId());

        // restore old value
        if (isset($cur_val)) {
            $sugar_config['max_mass_update'] = $cur_val;
        } else {
            unset($sugar_config['max_mass_update']);
        }

        global $db;
        $rec = $db->query("select team_set_id from contacts where id='{$contact1->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $team_set_id = $row['team_set_id'];
            $expectedTeamIDs = array($team1->id, $team2->id);
            $actualTeamIDs = array();
            $rec = $db->query("select team_id from team_sets_teams where team_set_id='{$team_set_id}'");
            while ($row = $db->fetchByAssoc($rec))
            {
                $actualTeamIDs[] = $row['team_id'];
            }
            $this->assertEmpty(array_diff($expectedTeamIDs, $actualTeamIDs), 'team_set_id not updated properly for contact1');
        } else {
            $this->assertTrue(false, 'could not get team_set_id');
        }
    }

    /*
     * This function tests mass update entire list without any search filter.
     * This function creates 2 contacts.
     * When doing mass update, both contacts should be updated.
     */
    public function testMassUpdateEntireListWithoutSearch()
    {
        if (isset($_REQUEST)) {
            unset($_REQUEST);
        }
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'do_not_call' => 1, // the field to update
                'entire' => true, // entire selected list
            ),
        );

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        $this->runJob($apiClass->getJobId());

        global $db;
        $rec = $db->query("select do_not_call from contacts where id='{$contact1->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['do_not_call'], 'do_not_call should be set to 1');
        }

        $rec = $db->query("select do_not_call from contacts where id='{$contact2->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['do_not_call'], 'do_not_call should be set to 1');
        }
    }

    /*
     * This function tests mass update entire list with basic search filter.
     * This function creates 3 contacts, with two of them have first name "Airline*".
     * Then we create a basic search filter to search for the contacts that have first_name start with "airline".
     * When doing mass update, only the contacts with first_name starting with "airline" should be updated.
     */
    public function testMassUpdateEntireListWithBasicSearch()
    {
        $contact1 = SugarTestContactUtilities::createContact();
        $contact1->first_name = 'Airline1';
        $contact1->save();

        $contact2 = SugarTestContactUtilities::createContact();
        $contact2->first_name = 'Airline2';
        $contact2->save();

        $contact3 = SugarTestContactUtilities::createContact();
        $contact3->first_name = 'SomethingElse';
        $contact3->save();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'do_not_call' => 1, // the field to update
                'entire' => true, // entire selected list
                'current_search' => array( // this is the search filter
                    'search_type' => 'basic', // to indicate this is for base search
                    'name' => 'airline', // search for first_name
                ),
            ),
        );

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        $this->runJob($apiClass->getJobId());

        global $db;
        // this should be updated since the contact's first_name matches
        $rec = $db->query("select do_not_call from contacts where id='{$contact1->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['do_not_call'], 'do_not_call should be set to 1');
        }

        // this should be updated since the contact's first_name matches
        $rec = $db->query("select do_not_call from contacts where id='{$contact2->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['do_not_call'], 'do_not_call should be set to 1');
        }

        // this should not be updated since the contact's first_name does not match
        $rec = $db->query("select do_not_call from contacts where id='{$contact3->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(0, $row['do_not_call'], 'do_not_call should remain 0');
        }
    }

    /*
     * This function tests mass update entire list with advanced search filter.
     * This function creates 5 contacts.
     * Then we create a advanced search filter to search for the contacts that have particular first_name
     * and are assigned to a particular user.
     * When doing mass update, only the contacts matching both first_name and assigned user should be updated.
     */
    public function testMassUpdateEntireListWithAdvancedSearch()
    {
        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();
        $user3 = SugarTestUserUtilities::createAnonymousUser();

        // both first_name and assigned_user_id match, will be updated
        $contact1 = SugarTestContactUtilities::createContact();
        $contact1->first_name = 'Airline1';
        $contact1->assigned_user_id = $user1->id;
        $contact1->save();

        // both first_name and assigned_user_id match, will be updated
        $contact2 = SugarTestContactUtilities::createContact();
        $contact2->first_name = 'Airline2';
        $contact2->assigned_user_id = $user2->id;
        $contact2->save();

        // only first_name matches, will not be updated
        $contact3 = SugarTestContactUtilities::createContact();
        $contact3->first_name = 'Airline3';
        $contact3->assigned_user_id = $user3->id;
        $contact3->save();

        // only assigned_user_id matches, will not be updated
        $contact4 = SugarTestContactUtilities::createContact();
        $contact4->first_name = 'SomethingElse';
        $contact4->assigned_user_id = $user1->id;
        $contact4->save();

        // neither first_name nor assigned_user_id matches, will not be updated
        $contact5 = SugarTestContactUtilities::createContact();
        $contact5->first_name = 'SomethingElse';
        $contact5->assigned_user_id = $user3->id;
        $contact5->save();

        $api = new RestService();
        $api->user = $GLOBALS['current_user'];

        $args = array(
            'massupdate_params' => array(
                'module' => 'Contacts',
                'do_not_call' => 1, // the field to update
                'entire' => true, // entire selected list
                'current_search' => array( // this is the search filter for first_name AND assigned_user_id
                    'search_type' => 'advanced', // to indicate this is for advanced search
                    'first_name' => 'airline', // search for first_name
                    'assigned_user_id' => array( // search for assigned_user_id for either user1 OR user2
                        0 => $user1->id,
                        1 => $user2->id,
                    ),
                ),
            ),
        );

        $apiClass = new MassUpdateApi();
        $apiClass->massUpdate($api, $args);

        $this->runJob($apiClass->getJobId());

        global $db;
        // contact1 and contact2 should be updated
        $rec = $db->query("select do_not_call from contacts where id='{$contact1->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['do_not_call'], 'do_not_call should be set to 1');
        }

        $rec = $db->query("select do_not_call from contacts where id='{$contact2->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(1, $row['do_not_call'], 'do_not_call should be set to 1');
        }

        // contact3, contact4 and contact5 should remain the same
        $rec = $db->query("select do_not_call from contacts where id='{$contact3->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(0, $row['do_not_call'], 'do_not_call should remain 0');
        }

        $rec = $db->query("select do_not_call from contacts where id='{$contact4->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(0, $row['do_not_call'], 'do_not_call should remain 0');
        }

        $rec = $db->query("select do_not_call from contacts where id='{$contact5->id}'");
        if ($row = $db->fetchByAssoc($rec))
        {
            $this->assertEquals(0, $row['do_not_call'], 'do_not_call should remain 0');
        }
    }
}
