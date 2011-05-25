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
 
class Bug43143Test extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

	public static function tearDownAfterClass()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}

	public function setUp()
	{
	    $this->bean = new Opportunity();
	    $this->defs = $this->bean->field_defs;
	    $this->timedate = $GLOBALS['timedate'];
	}

	public function tearDown()
	{
	    $this->bean->field_defs = $this->defs;
        $GLOBALS['timedate']->clearCache();
	}

	public function defaultDates()
	{
	    return array(
	        array('-1 day', '2010-12-31'),
	        array('now', '2011-01-01'),
	        array('+1 day', '2011-01-02'),
	        array('+1 week', '2011-01-08'),
	        array('next monday', '2011-01-03'),
	        array('next friday', '2011-01-07'),
	        array('+2 weeks', '2011-01-15'),
	        array('+1 month', '2011-02-01'),
	        array('first day of next month', '2011-02-01'),
	        array('+3 months', '2011-04-01'),
	        array('+6 months', '2011-07-01'),
	        array('+1 year', '2012-01-01'),
	        );
	}

	/**
	 * @dataProvider defaultDates
	 * @param string $default
	 * @param string $value
	 */
	public function testDefaults($default, $value)
	{
        $this->timedate->allow_cache = true;
        $this->timedate->setNow($this->timedate->fromDb('2011-01-01 00:00:00'));
	    $this->bean->field_defs['date_closed']['display_default'] = $default;
	    $this->bean->populateDefaultValues(true);
	    $this->assertEquals($value, $this->timedate->to_db_date($this->bean->date_closed));
	}

    /*
     * @group bug43143
     */
    public function testUnpopulateData()
    {
        $this->bean->field_defs['date_closed']['display_default'] = 'next friday';
	    $this->bean->populateDefaultValues(true);
        $this->assertNotNull($this->bean->date_closed);
        $this->bean->unPopulateDefaultValues();
        $this->assertNull($this->bean->name);
        $this->assertNull($this->bean->date_closed);
    }
}
