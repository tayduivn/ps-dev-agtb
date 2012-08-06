<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
* The contents of this file are subject to the SugarCRM Master Subscription
* Agreement ("License") which can be viewed at
* http://www.sugarcrm.com/crm/master-subscription-agreement
* By installing or using this file, You have unconditionally agreed to the
* terms and conditions of the License, and You may not use this file except in
* compliance with the License. Under the terms of the license, You shall not,
* among other things: 1) sublicense, resell, rent, lease, redistribute, assign
* or otherwise transfer Your rights to the Software, and 2) use the Software
* for timesharing or service bureau purposes such as hosting the Software for
* commercial gain and/or for the benefit of a third party. Use of the Software
* may be subject to applicable fees and any use of the Software without first
* paying applicable fees is strictly prohibited. You do not have the right to
* remove SugarCRM copyrights from the source code or user interface.
*
* All copies of the Covered Code must include on each user interface screen:
* (i) the "Powered by SugarCRM" logo and
* (ii) the SugarCRM copyright notice
* in the same form as they appear in the distribution. See full license for
* requirements.
*
* Your Warranty, Limitations of liability and Indemnity are expressly stated
* in the License. Please refer to the License for the specific language
* governing these rights and limitations under the License. Portions created
* by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
********************************************************************************/

/**
* Bug #46246
* Relation to the document didn't created when workflow action is a document creating.
* Test creating of the document-case relation
*/

require_once('include/workflow/action_utils.php');
require_once('modules/WorkFlow/WorkFlow.php');

class Bug46246Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $accepted_flav='PRO';
    private $case_id='52c1cd24-22e8-adb6-ac88-4f5471d6019';
    private $test_team;
    private $test_team_set_id;
    private $test_team_sets_teams_id;
    private $doc_id;
    
    public function setUp()
    {
        global $beanList, $beanFiles;
        require('include/modules.php');

        if($GLOBALS['sugar_flavor']==$this->accepted_flav){
            $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
            $this->test_team = SugarTestTeamUtilities::createAnonymousTeam();
            $this->test_team->add_user_to_team($GLOBALS['current_user']->id,$GLOBALS['current_user']);

            // insert test team set
            $this->test_team_set_id=create_guid();
            $GLOBALS['db']->query("INSERT INTO `team_sets` SET id='{$this->test_team_set_id}',name='test team set',team_count=1",true);

            // insert test team set relation to team
            $this->test_team_sets_teams_id=create_guid();
            $GLOBALS['db']->query("INSERT INTO `team_sets_teams` SET id='{$this->test_team_sets_teams_id}',team_set_id='{$this->test_team_set_id}'
,team_id='{$this->test_team->id}'",true);

            // create test "Case"
            $GLOBALS['db']->query("DELETE FROM `cases` WHERE id='{$this->case_id}'",true);
            $GLOBALS['db']->query("INSERT INTO `cases` SET id='{$this->case_id}',name='test case',team_id='{$this->test_team->id}',team_set_id='{$this->test_team_set_id}'",true);
        }
    }
    
    public function tearDown()
    {
        if($GLOBALS['sugar_flavor']==$this->accepted_flav){
            // delete all created records
            $GLOBALS['db']->query("DELETE FROM `cases` WHERE id='{$this->case_id}'",true);
            if($this->doc_id){
                    $GLOBALS['db']->query("DELETE FROM `documents_cases` WHERE case_id='{$this->case_id}'",true);
                    $GLOBALS['db']->query("DELETE FROM `documents` WHERE id='{$this->doc_id}'",true);
            }

            $GLOBALS['db']->query("DELETE FROM `team_sets_teams` WHERE id='{$this->test_team_sets_teams_id}'",true);
            $GLOBALS['db']->query("DELETE FROM `team_sets` WHERE id='{$this->test_team_set_id}'",true);

            SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
            SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        }
    }
    
    public function testRelationCreating()
    {
        if($GLOBALS['sugar_flavor']==$this->accepted_flav){
            include_once('modules/Cases/Case.php');
            $focus=new aCase();
            $focus->id=$this->case_id;

            $action_array=array(
                'action_type' => 'new',
                'action_module' => 'documents',
                'rel_module' => '',
                'rel_module_type' => 'all',
                'basic' => array(
                                'document_name' => 'TEST ALERT',
                                'active_date' => 14440,
                ),
                'basic_ext' => array(
                                'active_date' => 'Triggered Date',
                ),
                'advanced' => array(),
            );
            process_action_new($focus, $action_array);


            $this->doc_id=$GLOBALS['db']->getOne("SELECT document_id FROM `documents_cases` WHERE case_id='{$this->case_id}'",true);

            // check for relation existing
                $this->assertTrue($this->doc_id ? true : false, true);
        }
    }
}