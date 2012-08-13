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
 
require_once 'include/MassUpdate.php';
require_once 'modules/Opportunities/Opportunity.php';


class Bug46276Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $opportunities;

	public function setUp()
	{

		global $current_user, $timedate, $app_strings, $app_list_strings, $current_language;
        $app_strings = return_application_language($current_language);
        $app_list_strings = return_app_list_strings_language($current_language);
		// Create Anon User setted on GMT+1 TimeZone
		$current_user = SugarTestUserUtilities::createAnonymousUser();
		$current_user->setPreference('datef', "Y-m-d");
		$current_user->setPreference('timef', "H:i:s");
		$current_user->setPreference('timezone', "Europe/London");

		// new object to avoid TZ caching
		$timedate = new TimeDate();

		$this->opportunities = new Opportunity();
		$this->opportunities->name = 'Bug46276 Opportunity';
		$this->opportunities->amount = 1234;
		$this->opportunities->sales_stage = "Prospecting";
		$this->opportunities->account_name = "A.G. Parr PLC";
		$this->opportunities->date_closed = '2011-08-12';
		$this->opportunities->save();
	}

	public function tearDown()
	{
		 
		$GLOBALS['db']->query('DELETE FROM opportunities WHERE id = \'' . $this->opportunities->id . '\' ');
		unset($this->opportunities);
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	}

	//testing handleMassUpdate() for date fields when time zone of the current user is GMT+

	public function testhandleMassUpdateForDateFieldsInGMTPlusTimeZone()
	{
		global $current_user, $timedate;
		$_REQUEST = $_POST = array("module" => "Opportunities",
                                   "action" => "MassUpdate",
                                   "return_action" => "index",
                                   "delete" => "false",
    							   "massupdate" => "true",
    							   "lvso" => "asc",
    							   "uid" => $this->opportunities->id,
    							   "date_closed" => "2011-08-09",		
		);



		$mass = new MassUpdate();
		$mass->setSugarBean($this->opportunities);
		$mass->handleMassUpdate();
		$expected_date = $_REQUEST['date_closed'];
		$actual_date = $this->opportunities->date_closed;
		$this->assertEquals($expected_date, $actual_date);
	}
	 

}
