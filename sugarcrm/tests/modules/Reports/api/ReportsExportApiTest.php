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

require_once('tests/rest/RestTestBase.php');
require_once('include/api/SugarApi.php');
require_once('include/api/RestService.php');
require_once('modules/Reports/clients/base/api/ReportsExportApi.php');

class ReportsExportApiTest extends RestTestBase
{

    public $reportsExportApi;
    public $serviceMock;

    public function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp("current_user");
        


        $this->reportDefs = '{"display_columns":[{"name":"name","label":"Name","table_key":"self"}],"module":"Accounts","group_defs":[],"summary_columns":[],"report_name":"test report 2","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND"}},"chart_type":"none"}';

        $this->serviceMock = new ReportsExportApiServiceMockUp;
        $this->reportsExportApi = new ReportsExportApi();


    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM saved_reports WHERE assigned_user_id='{$GLOBALS['current_user']->id}'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
        unset($_SESSION['ACL']);
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testReportExportBase64Api()
    {
        $rep = new SavedReport();
        $rep->save_report(-1, $GLOBALS['current_user']->id, "Test Account Report", "Accounts","tabular",$this->reportDefs, 0, 1);
        
        $GLOBALS['db']->commit();

        $id = $rep->id;
        // call the Rest
        $restReply = $this->_restCall("Reports/{$id}/base64",
                                    json_encode(array()),
                                    'GET');

        $this->assertTrue(!empty($restReply), 'no file received');
    }

    /**
     * @group rest
     */
    public function testReportExportPdfApi()
    {
        $rep = new SavedReport();
        $rep->save_report(-1, $GLOBALS['current_user']->id, "Test Account Report", "Accounts","tabular",$this->reportDefs, 0, 1);
        
        $GLOBALS['db']->commit();

        $id = $rep->id;
        // call the Rest
        $restReply = $this->_restCall("Reports/{$id}/pdf",
                                    json_encode(array()),
                                    'GET');

        $this->assertTrue(!empty($restReply), 'no file received');
    }    

    /**
     * @group rest
     */
    public function testReportExportSummaryPdfApi()
    {
        // The summary report goes through a different file and was having issues

        $summaryReportDef = '{"report_type":"summary","display_columns":[],"summary_columns":[{"name":"count","label":"Count","group_function":"count","table_key":"self"},{"name":"name","label":"Team: Team Name","table_key":"self_link_0","is_group_by":"visible"},{"name":"user_name","label":"Assigned to User: User Name","table_key":"self_link_1","is_group_by":"visible"}],"filters_def":[],"filters_combiner":"AND","group_defs":[{"name":"name","label":"Team Name","table_key":"self_link_0"},{"name":"user_name","label":"User Name","table_key":"self_link_1"}],"full_table_list":{"self":{"parent":"","value":"Meetings","module":"Meetings","label":"Meetings","children":{"self_link_0":"self_link_0","self_link_1":"self_link_1"}},"self_link_0":{"parent":"self","children":[],"value":"team_link","label":"Team","link_def":{"name":"team_link","relationship_name":"meetings_team","bean_is_lhs":"","link_type":"one","label":"Team","table_key":"self_link_0"},"module":"Teams"},"self_link_1":{"parent":"self","children":[],"value":"assigned_user_link","label":"Assigned to User","link_def":{"name":"assigned_user_link","relationship_name":"meetings_assigned_user","bean_is_lhs":"","link_type":"one","label":"Assigned to User","table_key":"self_link_1"},"module":"Users"}},"module":"Meetings","report_name":"Meetings By Team By User","chart_type":"hBarF","chart_description":"","numerical_chart_column":"count","assigned_user_id":"1"}';

        $rep = new SavedReport();
        $rep->save_report(-1, $GLOBALS['current_user']->id, "Test Account Report", "Accounts","tabular",$summaryReportDef, 0, 1);
        
        $GLOBALS['db']->commit();

        $id = $rep->id;
        // call the Rest
        $restReply = $this->_restCall("Reports/{$id}/pdf",
                                    json_encode(array()),
                                    'GET');

        $this->assertTrue(!empty($restReply), 'no file received');
    }
    /**
     * @group rest
     */
    public function testNoAccessReportExport()
    {

        $this->setExpectedException(
          'SugarApiExceptionNotAuthorized', "----Users"
        );        
        // take away access
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Accounts']['module']['list']['aclaccess'] = ACL_ALLOW_NONE;
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Meetings']['module']['list']['aclaccess'] = ACL_ALLOW_NONE;
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Users']['module']['list']['aclaccess'] = ACL_ALLOW_NONE;
        // The summary report goes through a different file and was having issues

        $summaryReportDef = '{"report_type":"summary","display_columns":[],"summary_columns":[{"name":"count","label":"Count","group_function":"count","table_key":"self"},{"name":"name","label":"Team: Team Name","table_key":"self_link_0","is_group_by":"visible"},{"name":"user_name","label":"Assigned to User: User Name","table_key":"self_link_1","is_group_by":"visible"}],"filters_def":[],"filters_combiner":"AND","group_defs":[{"name":"name","label":"Team Name","table_key":"self_link_0"},{"name":"user_name","label":"User Name","table_key":"self_link_1"}],"full_table_list":{"self":{"parent":"","value":"Meetings","module":"Meetings","label":"Meetings","children":{"self_link_0":"self_link_0","self_link_1":"self_link_1"}},"self_link_0":{"parent":"self","children":[],"value":"team_link","label":"Team","link_def":{"name":"team_link","relationship_name":"meetings_team","bean_is_lhs":"","link_type":"one","label":"Team","table_key":"self_link_0"},"module":"Teams"},"self_link_1":{"parent":"self","children":[],"value":"assigned_user_link","label":"Assigned to User","link_def":{"name":"assigned_user_link","relationship_name":"meetings_assigned_user","bean_is_lhs":"","link_type":"one","label":"Assigned to User","table_key":"self_link_1"},"module":"Users"}},"module":"Meetings","report_name":"Meetings By Team By User","chart_type":"hBarF","chart_description":"","numerical_chart_column":"count","assigned_user_id":"1"}';

        $rep = new SavedReport();
        $rep->save_report(-1, $GLOBALS['current_user']->id, "Test Account Report", "Accounts","tabular",$summaryReportDef, 0, 1);
        
        $GLOBALS['db']->commit();

        $id = $rep->id;
        // call the Rest
        $val = $this->reportsExportApi->exportRecord($this->serviceMock, array('record'=> $id, 'export_type' => 'pdf'));

        $this->assertTrue(!empty($restReply), 'no file received');
    }

}

class ReportsExportApiServiceMockUp extends RestService
{
    public function execute() {}
    protected function handleException(Exception $exception) {}
}
