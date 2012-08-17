<?php

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * Bug #34880 : Non-reportable fields unavailable to workflow
 *
 * @author myarotsky@sugarcrm.com
 * @ticket 34880
 */
require_once('include/VarDefHandler/VarDefHandler.php');
class Bug34880Test extends Sugar_PHPUnit_Framework_TestCase
{
    public static function provider()
    {
        return array(
            array('standard_display'),
            array('normal_trigger'),
            array('normal_date_trigger'),
            array('action_filter'),
            array('template_filter'),
            array('alert_trigger')
        );
    }
    /**
     * Reportable fields must be available in workflow
     * @dataProvider provider
     * @group 34880
     */
    public function testReportableFieldsMustBeAvailableInWorkflow($action)
    {
        $def = array(
            'reportable' => ''
        );
        $obj = new VarDefHandler('', $action);
        $this->assertTrue($obj->compare_type($def), "reportable fields should be available in workflow");
    }
}
