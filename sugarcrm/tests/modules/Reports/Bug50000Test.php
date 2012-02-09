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


class Bug50000Test extends Sugar_PHPUnit_Framework_TestCase {

    var $reporter;

    public function setUp() {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['mod_strings'] = return_module_language('en_us', 'Reports');
        require_once('modules/Reports/templates/templates_reports.php');
        $this->reporter = new Bug50000MockReporter();
    }

    public function tearDown() {
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['mod_strings']);
        unset($this->reporter);
    }

    /**
     * @dataProvider bug50000DataProvider
     */
    public function testColumnLabelsAreCorrectForMatrixReport($report_def, $header_row, $expected) {
        $this->reporter->report_def = $report_def;

        $this->assertSame($expected, getHeaderColumnNamesForMatrix($this->reporter, $header_row, ''));
    }

    /**
     * Data provider for testColumnLabelsAreCorrectForMatrixReport()
     * @return array report_def, header_row, expected
     */
    public function bug50000DataProvider() {
        $strings = return_module_language('en_us', 'Reports');
        return array(
            array(
                array('group_defs' => array(
                    array('label'=> 'User Name', 'name' => 'user_name', 'table_key' => 'Opportunities:assigned_user_link', 'type'=>'user_name'),
                    array('label'=> 'Name', 'name' => 'name', 'table_key' => 'Opportunities:accounts', 'type'=>'name'),
                )),
                array('User Name', 'Account Name', 'Count'),
                array('User Name', 'Account Name', $strings['LBL_REPORT_GRAND_TOTAL']),
            ),
        );
    }
}


class Bug50000MockReporter {
    var $report_def;
    var $group_defs_Info;
}

?>