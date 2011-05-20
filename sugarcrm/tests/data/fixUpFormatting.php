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

class fixUpFormattingTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $myBean;

	public function setUp()
	{
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        
        $myBean = new SugarBean();
        
        $myBean->field_defs = array( 
            'id' => array('name' => 'id', 'vname' => 'LBL_ID', 'type' => 'id', 'required' => true, ),
            'name' => array('name' => 'name', 'vname' => 'LBL_NAME', 'type' => 'varchar', 'len' => '255', 'required' => true, ),
            'bool_field' => array('name' => 'bool_field', 'vname' => 'LBL_BOOL_FIELD', 'type' => 'bool', ),
            'int_field' => array('name' => 'int_field', 'vname' => 'LBL_INT_FIELD', 'type' => 'int', ),
            'float_field' => array('name' => 'float_field', 'vname' => 'LBL_FLOAT_FIELD', 'type' => 'float', 'precision' => 2, ),
            'date_field' => array('name' => 'date_field', 'vname' => 'LBL_DATE_FIELD', 'type' => 'date', ),
            'time_field' => array('name' => 'time_field', 'vname' => 'LBL_TIME_FIELD', 'type' => 'time', ),
            'datetime_field' => array('name' => 'datetime_field', 'vname' => 'LBL_DATETIME_FIELD', 'type' => 'datetime', ),
        );

        $myBean->id = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $myBean->name = 'Fake Bean';
        $myBean->bool_field = 1;
        $myBean->int_field = 2001;
        $myBean->float_field = 20.01;
        $myBean->date_field = '2001-07-28';
        $myBean->time_field = '21:19:37';
        $myBean->datetime_field = '2001-07-28 21:19:37';

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
            array(true,1),
            array(false,0),
            array('',0),
            array(1,1),
            array(0,0),
            array('1',1),
            array('0',0),
            array('true',1),
            array('false',0),
            array('on',1),
            array('off',0),
            array('yes',1),
            array('no',0),
	        );
	}

	/**
     * @group bug34562
     * @dataProvider providerBoolFixups
     */
	public function testBoolFixups($from, $to)
	{
        $bean = new SugarBean();

        $bean->bool_field = $from;
        $bean->fixUpFormatting();
        $this->assertEquals($to,$bean->bool_field,'fixUpFormatting did not adjust from ('.gettype($from).') "'.$from.'"');
    }

    /**
     * @group bug43321
     */
	public function testStringNULLFixups()
	{
        $bean = new SugarBean();

        $bean->field_defs = array('date_field'=>array('type'=>'date'),
                                 'datetime_field'=>array('type'=>'datetime'),
                                 'time_field'=>array('type'=>'time'),
                                 'datetimecombo_field'=>array('type'=>'datetimecombo')
        );
        $bean->date_field = 'NULL';
        $bean->datetime_field = 'NULL';
        $bean->time_field = 'NULL';
        $bean->datetimecombo_field = 'NULL';
        $bean->fixUpFormatting();
        $this->assertEquals('', $bean->date_field,'fixUpFormatting did not reset string NULL for date');
        $this->assertEquals('', $bean->datetime_field,'fixUpFormatting did not reset string NULL for time');
        $this->assertEquals('', $bean->time_field,'fixUpFormatting did not reset string NULL for datetime');
        $this->assertEquals('', $bean->datetimecombo_field,'fixUpFormatting did not reset string NULL for datetimecombo');
	}
}