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

require_once('modules/Reports/Report.php');
require_once('tests/rest/RestTestBase.php');

class ReportsExportApiTest extends RestTestBase
{
    /**
     * @var array Request for creating/deleting related field for Accounts module
     */
    private $_req = array (
        'to_pdf' => 'true',
        'sugar_body_only' => '1',
        'module' => 'ModuleBuilder',
        'new_dropdown' => '',
        'view_module' => 'Accounts',
        'is_update' => 'true',
        'type' => 'relate',
        'name' => 'relate_contacts',
        'labelValue' => 'relate contacts',
        'label' => 'LBL_RELATE_CONTACTS',
        'help' => '',
        'comments' => '',
        'ext2' => 'Contacts',
        'ext3' => '',
        'dependency' => '',
        'dependency_display' => '',
        'reportableCheckbox' => '1',
        'reportable' => '1',
        'importable' => 'true',
        'duplicate_merge' => '0',
    );

    private $_account_1;

    private $_account_2;

    private $_contact_1;

    private $_contact_2;

    private $_report;

    protected $_user;

    /**
     * @var bool
     */
    protected $origin_isCacheReset;

    public function setUp()
    {
        parent::setUp();
        $this->origin_isCacheReset = SugarCache::$isCacheReset;
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        $this->_user = SugarTestUserUtilities::createAnonymousUser(false);
        $this->_user->is_admin = 1;
        $this->_user->save();
        $GLOBALS['current_user'] = $this->_user;

        $this->_req['action'] = 'saveField';
        $_REQUEST = $this->_req;
        $_POST = $this->_req;
        $mb = new ModuleBuilderController();
        $mb->action_saveField();

        $this->_contact_1 = new Contact();
        $this->_contact_1->last_name = 'Contact #1';
        $this->_contact_1->id = create_guid();
        $this->_contact_1->new_with_id = true;
        $this->_contact_1->team_id = 1;
        $this->_contact_1->save();

        $this->_contact_2 = new Contact();
        $this->_contact_2->id = create_guid();
        $this->_contact_2->new_with_id = true;
        $this->_contact_2->last_name = 'Contact #2';
        $this->_contact_2->team_id = 1;
        $this->_contact_2->save();

        $this->_account_1 = new Account();
        $this->_account_1->id = create_guid();
        $this->_account_1->new_with_id = true;
        $this->_account_1->name = 'Account #1';
        $this->_account_1->contact_id_c = $this->_contact_1->id;
        $this->_account_1->team_id = 1;
        $this->_account_1->relate_contacts_c = $this->_contact_1->last_name;
        $this->_account_1->save();

        $this->_account_2 = new Account();
        $this->_account_2->id = create_guid();
        $this->_account_2->new_with_id = true;
        $this->_account_2->name = 'Account #2';
        $this->_account_2->contact_id_c = $this->_contact_2->id;
        $this->_account_2->relate_contacts_c = $this->_contact_2->last_name;
        $this->_account_2->parent_id = $this->_account_1->id;
        $this->_account_2->team_id = 1;
        $this->_account_2->save();


       /**
         * Report defs for generating the report
         */
        $rep_defs =array (
            'display_columns' =>
            array (
                0 =>
                array (
                    'name' => 'name',
                    'label' => 'Name',
                    'table_key' => 'self',
                ),
                1 =>
                array (
                    'name' => 'relate_contacts_c',
                    'label' => 'relate contacts',
                    'table_key' => 'self',
                ),
                2 =>
                array (
                    'name' => 'name',
                    'label' => 'Name1',
                    'table_key' => 'Accounts:member_of',
                ),
                3 =>
                array (
                    'name' => 'relate_contacts_c',
                    'label' => 'relate contacts1',
                    'table_key' => 'Accounts:member_of',
                ),
            ),
            'module' => 'Accounts',
            'group_defs' =>
            array (
            ),
            'summary_columns' =>
            array (
            ),
            'report_name' => 'report #1',
            'chart_type' => 'none',
            'do_round' => 1,
            'numerical_chart_column' => '',
            'numerical_chart_column_type' => '',
            'assigned_user_id' => '1',
            'report_type' => 'tabular',
            'full_table_list' =>
            array (
                'self' =>
                array (
                    'value' => 'Accounts',
                    'module' => 'Accounts',
                    'label' => 'Accounts',
                ),
                'Accounts:member_of' =>
                array (
                    'name' => 'Accounts  >  Member of',
                    'parent' => 'self',
                    'link_def' =>
                    array (
                        'name' => 'member_of',
                        'relationship_name' => 'member_accounts',
                        'bean_is_lhs' => false,
                        'link_type' => 'one',
                        'label' => 'Member of',
                        'module' => 'Accounts',
                        'table_key' => 'Accounts:member_of',
                    ),
                    'dependents' =>
                    array (
                        0 => 'display_cols_row_3',
                        1 => 'display_cols_row_4',
                        2 => 'display_cols_row_3',
                        3 => 'display_cols_row_4',
                    ),
                    'module' => 'Accounts',
                    'label' => 'Member of',
                    'optional' => true,
                ),
            ),
            'filters_def' =>
            array (
                'Filter_1' =>
                array (
                    'operator' => 'AND',
                    0 =>
                    array (
                        'name' => 'name',
                        'table_key' => 'self',
                        'qualifier_name' => 'is'
                    ),
                ),
            ),
        );
        $rep_defs['filters_def']['Filter_1']['0']['input_name0'] = $this->_account_2->id;
        $rep_defs['filters_def']['Filter_1']['0']['input_name1'] = $this->_account_2->name;
        $json = getJSONobj();
        $tmp = $json->encode($rep_defs);
        $this->_report = new Report($tmp);
        $this->_report->report_name = "UNIT TEST REPORT - " . create_guid();
        $this->_report->new_with_id = true;
        $this->_report->run_query();
        
    }

    public function tearDown()
    {
        $this->_req['action'] = 'DeleteField';
        $this->_req['name'] = 'relate_contacts_c';
        $_REQUEST = $this->_req;
        $_POST = $this->_req;
        $mb = new ModuleBuilderController();
        $mb->action_DeleteField();

        $this->_account_1->mark_deleted($this->_account_1->id);
        $this->_account_2->mark_deleted($this->_account_2->id);
        $this->_contact_1->mark_deleted($this->_contact_1->id);
        $this->_contact_2->mark_deleted($this->_contact_2->id);
        $this->_report->deleted = 1;
        $this->_report->save($this->_report->report_name);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarCache::$isCacheReset = $this->origin_isCacheReset;
        parent::tearDown();

    }

    /**
     * Testing related fields in the report
     * @group 51423
     */
    public function testReportsRelatedField()
    {
 
        
        // have to set a request variable for the assigned_user_id so the report will save
        $_REQUEST['assigned_user_id'] = $this->_user->id;

        $this->_report->save($this->_report->report_name);
        $report_id = $this->_report->saved_report->id;
        // call the Rest
        $restReply = $this->_restCall("Reports/{$report_id}/pdf",
                                    json_encode(array()),
                                    'GET');
        $this->assertTrue(!empty($restReply['reply']['file_contents']), 'no file received');
    }
}
