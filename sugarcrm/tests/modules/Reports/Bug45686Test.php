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

require_once 'modules/Reports/Report.php';
require_once 'modules/Reports/SavedReport.php';
/**
 * @group Bug45686
 */
class Bug45686Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->reportDefs = <<<DEFS
{"display_columns":[{"name":"account_type","label":"<s>Type</s>","table_key":"self"}],"module":"Accounts",
"group_defs":[{"name":"account_type","label":"<s>Type</s>","table_key":"self","type":"enum"}],
"summary_columns":[{"name":"count","label":"<s>ZZZ</s>","field_type":"","group_function":"count","table_key":"self"},
{"name":"account_type","label":"<s>Type</s>","table_key":"self"}],"report_name":"<s>test</s>","chart_type":"hBarF","do_round":1,
"chart_description":"<s>chart</s>","numerical_chart_column":"self:count","numerical_chart_column_type":"","assigned_user_id":"1",
"report_type":"summary","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"<s>Accounts</s>"}},
"filters_def":{"Filter_1":{"operator":"AND"}}}
DEFS;
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM saved_reports WHERE assigned_user_id='{$GLOBALS['current_user']->id}'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

    }

    /**
     * Test that report ctor strips HTML from labels
     */
    public function testHtmlInReports()
    {

        $rep = new Report($this->reportDefs);
        $this->assertNotContains("<s>", $rep->report_def_str);
        $this->assertNotContains("</s>", $rep->report_def_str);
    }

    /**
     * Test that SavedReport save strips HTML from labels
     */
    public function testHtmlInSavedReports()
    {
        $rep = new SavedReport();
        $rep->save_report(-1, $GLOBALS['current_user']->id, "<s>".to_html("<s>TEST</s>")."</s>", "Accounts","summary",$this->reportDefs, 0, 1);
        $id = $rep->id;
        $rep = new SavedReport();
        $rep->retrieve($id);
        $this->assertNotContains("<s>", $rep->name);
        $this->assertNotContains("</s>", $rep->name);
    }
}

