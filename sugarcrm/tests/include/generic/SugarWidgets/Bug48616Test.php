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

require_once "include/generic/LayoutManager.php";
require_once "include/generic/SugarWidgets/SugarWidgetFielddatetime.php";

class Bug48616Test extends PHPUnit_Framework_TestCase
{
    var $sugarWidgetField;

    public function setUp()
    {
        $this->sugarWidgetField = new SugarWidgetFieldDateTime48616Mock(new LayoutManager());
        global $current_user, $timedate;
        $timedate = TimeDate::getInstance();
        $current_user = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testQueryFilterBefore()
    {
        $layout_def =  array ('name' => 'donotinvoiceuntil_c', 'table_key' => 'self', 'qualifier_name' => 'before', 'input_name0' => 'Today', 'input_name1' => '01:00am', 'input_name2' => 'on', 'table_alias' => 'pordr_purchaseorders_cstm', 'column_key' => 'self:donotinvoiceuntil_c', 'type' => 'datetimecombo');
        $filter = $this->sugarWidgetField->queryFilterBefore($layout_def);
        if($GLOBALS['db']->getScriptName() == 'mysql')
        {
            $this->assertRegExp("/pordr_purchaseorders_cstm\.donotinvoiceuntil_c < \'\d{4}\-\d{1,2}-\d{1,2} \d{2}:\d{2}:\d{2}\'/", $filter);
        }
        /*
        else if($GLOBALS['db']->getScriptName() == 'db2') {

        }
        */
    }

    public function testQueryFilterAfter()
    {
        $layout_def =  array ('name' => 'donotinvoiceuntil_c', 'table_key' => 'self', 'qualifier_name' => 'after', 'input_name0' => 'Today', 'input_name1' => '01:00am', 'input_name2' => 'on', 'table_alias' => 'pordr_purchaseorders_cstm', 'column_key' => 'self:donotinvoiceuntil_c', 'type' => 'datetimecombo');
        $filter = $this->sugarWidgetField->queryFilterAfter($layout_def);
        if($GLOBALS['db']->getScriptName() == 'mysql')
        {
            $this->assertRegExp("/pordr_purchaseorders_cstm\.donotinvoiceuntil_c > \'\d{4}\-\d{1,2}-\d{1,2} \d{2}:\d{2}:\d{2}\'/", $filter);
        }
    }

    public function testQueryFilterNotEqualsStr()
    {
        $layout_def =  array ('name' => 'donotinvoiceuntil_c', 'table_key' => 'self', 'qualifier_name' => 'not_equals', 'input_name0' => 'Today', 'input_name1' => '01:00am', 'input_name2' => 'on', 'table_alias' => 'pordr_purchaseorders_cstm', 'column_key' => 'self:donotinvoiceuntil_c', 'type' => 'datetimecombo');
        $filter = $this->sugarWidgetField->queryFilterNot_Equals_str($layout_def);
        $filter = preg_replace('/\s{2,}/', ' ', $filter);
        $filter = str_replace("\n", '', $filter);
        $filter = str_replace("\r", '', $filter);
        if($GLOBALS['db']->getScriptName() == 'mysql')
        {
            $this->assertRegExp("/\(pordr_purchaseorders_cstm\.donotinvoiceuntil_c IS NULL OR pordr_purchaseorders_cstm\.donotinvoiceuntil_c < \'\d{4}\-\d{1,2}-\d{1,2} \d{2}:\d{2}:\d{2}\' OR pordr_purchaseorders_cstm\.donotinvoiceuntil_c > \'\d{4}\-\d{1,2}-\d{1,2} \d{2}:\d{2}:\d{2}\'\)/", $filter);
        }
        /*
        else if($GLOBALS['db']->getScriptName() == 'db2') {
            $this->assertRegExp("/\(pordr_purchaseorders_cstm\.donotinvoiceuntil_c IS NULL OR pordr_purchaseorders_cstm\.donotinvoiceuntil_c < CONVERT\(datetime\,'\d{4}\-\d{1,2}-\d{1,2} \d{2}:\d{2}:\d{2}\',\d+?\) OR pordr_purchaseorders_cstm\.donotinvoiceuntil_c > CONVERT\(datetime\,'\d{4}\-\d{1,2}-\d{1,2} \d{2}:\d{2}:\d{2}\',120\)\)/", $filter);
        }
        */
    }


}

class SugarWidgetFieldDateTime48616Mock extends SugarWidgetFieldDateTime
{
    protected function queryDateOp($arg1, $arg2, $op, $type)
    {
        global $timedate;
        if($arg2 instanceof DateTime) {
            $arg2 = $timedate->asDbType($arg2, $type);
        }
        return "$arg1 $op ".$GLOBALS['db']->convert($GLOBALS['db']->quoted($arg2), $type)."\n";
    }
}

?>