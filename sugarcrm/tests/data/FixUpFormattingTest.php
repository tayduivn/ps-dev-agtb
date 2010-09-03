<?php
/********************************************************************************
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

require_once('data/SugarBean.php');
require_once('modules/Accounts/Account.php');

class FixUpFormattingTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $myBean;

	public function setUp()
	{
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        
        $this->myBean = new SugarBean();
        
        $this->myBean->field_defs = array( 
            'id' => array('name' => 'id', 'vname' => 'LBL_ID', 'type' => 'id', 'required' => true, ),
            'name' => array('name' => 'name', 'vname' => 'LBL_NAME', 'type' => 'varchar', 'len' => '255', 'required' => true, ),
            'bool_field' => array('name' => 'bool_field', 'vname' => 'LBL_BOOL_FIELD', 'type' => 'bool', ),
            'int_field' => array('name' => 'int_field', 'vname' => 'LBL_INT_FIELD', 'type' => 'int', ),
            'float_field' => array('name' => 'float_field', 'vname' => 'LBL_FLOAT_FIELD', 'type' => 'float', 'precision' => 2, ),
            'date_field' => array('name' => 'date_field', 'vname' => 'LBL_DATE_FIELD', 'type' => 'date', ),
            'time_field' => array('name' => 'time_field', 'vname' => 'LBL_TIME_FIELD', 'type' => 'time', ),
            'datetime_field' => array('name' => 'datetime_field', 'vname' => 'LBL_DATETIME_FIELD', 'type' => 'datetime', ),
        );

        $this->myBean->id = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $this->myBean->name = 'Fake Bean';
        $this->myBean->bool_field = 1;
        $this->myBean->int_field = 2001;
        $this->myBean->float_field = 20.01;
        $this->myBean->date_field = '2001-07-28';
        $this->myBean->time_field = '21:19:37';
        $this->myBean->datetime_field = '2001-07-28 21:19:37';

	}

	public function tearDown()
	{
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($this->time_date);
	}

	public function providerBoolFixups()
	{
	    return array(
            array(true,true),
            array(false,false),
            array('',false),
            array(1,true),
            array(0,false),
            array('1',true),
            array('0',false),
            array('true',true),
            array('false',false),
            array('on',true),
            array('off',false),
            array('yes',true),
            array('no',false),
	        );
	}

	/**
     * @ticket 34562
     * @dataProvider providerBoolFixups
     */
	public function testBoolFixups($from, $to)
	{
        $this->myBean->bool_field = $from;
        $this->myBean->fixUpFormatting();
        $this->assertEquals($to,$this->myBean->bool_field,'fixUpFormatting did not adjust from ('.gettype($from).') "'.$from.'"');
    }
}