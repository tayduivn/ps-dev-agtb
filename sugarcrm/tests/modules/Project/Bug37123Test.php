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


class Bug37123Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $unid = uniqid();
        $time = date('Y-m-d H:i:s');

        $contact = new Contact();
        $contact->id = 'c_'.$unid;
        $contact->first_name = 'testfirst';
        $contact->last_name = 'testlast';
        $contact->new_with_id = true;
        $contact->disable_custom_fields = true;
        $contact->save();
        $this->contact = $contact;

        $account = new Account();
        $account->id = 'a_'.$unid;
        $account->first_name = 'testfirst';
        $account->last_name = 'testlast';
        $account->assigned_user_id = 'SugarUser';
        $account->new_with_id = true;
        $account->disable_custom_fields = true;
        $account->save();
        $this->account = $account;

        $ac_id = 'ac_'.$unid;
        $this->ac_id = $ac_id;//Accounts to Contacts
        $GLOBALS['db']->query("INSERT INTO accounts_contacts (id , contact_id, account_id, date_modified, deleted) values ('{$ac_id}', '{$contact->id}', '{$account->id}', '$time', 0)");

        $_REQUEST['relate_id'] = $this->contact->id;
        $_REQUEST['relate_to'] = 'projects_contacts';
    }

    public function testRelationshipSave()
    {
        $timedate = TimeDate::getInstance();
        $unid = uniqid();
        $project = new Project();
        $project->id = 'p_' . $unid;
        $project->name = 'test project ' . $unid;
        $project->estimated_start_date = $timedate->nowDate();
        $project->estimated_end_date = $timedate->asUserDate($timedate->getNow(true)->modify("+7 days"));
        $project->new_with_id = true;
        $project->disable_custom_fields = true;
        $newProjectId = $project->save();
        $this->project = $project;
        $savedProjectId =  $GLOBALS['db']->getOne("
                                SELECT project_id FROM projects_accounts
                                WHERE project_id= '{$newProjectId}'
                                AND account_id='{$this->account->id}'"
                            );
        $this->assertEquals($newProjectId, $savedProjectId);
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->contact->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE id = '{$this->ac_id}'");
        $GLOBALS['db']->query("DELETE FROM projects_accounts
                               WHERE project_id= '{$this->project->id}'
                               AND account_id = '{$this->account->id}'");
        unset($this->account);
        unset($this->contact);
        unset($this->project);
        unset($this->ac_id);
        unset($GLOBALS['relate_id']);
        unset($GLOBALS['relate_to']);
        SugarTestHelper::tearDown();
    }



}
