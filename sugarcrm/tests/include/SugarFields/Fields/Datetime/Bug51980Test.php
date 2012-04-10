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
require_once('include/MVC/View/views/view.detail.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Opportunities/views/view.detail.php');

class Bug51980Test extends Sugar_PHPUnit_Framework_OutputTestCase {
// class Bug51980Test extends  Sugar_PHPUnit_Framework_TestCase{
    private $user;
    private $opp;

	public function setUp()
    {
        //create user
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->user->default_team_name = 'global';
        $this->user->is_admin = 1;
        $this->user->save();
        $this->user->retrieve($this->user->id);
        $GLOBALS['current_user'] = $this->user;
        //set some global values that will help with the view
        $_REQUEST['action'] = $GLOBALS['action'] = 'DetailView';
        $_REQUEST['module'] = $GLOBALS['module'] = 'Opportunities';

        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], "Opportunities");


        //create opportunity
        $name = 'Test_51980_'.time();
        $this->opp = new Opportunity();
        $this->opp->name = $name;
        $this->opp->amount = '1000000';
        $this->opp->account_id = '1';
        $this->opp->team_id = '1';
        $this->opp->currency_id = -99;
        $this->opp->save();

	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE name like 'Test_51980_%'");
        unset($this->user);
        unset($this->opp);
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['app_strings']);
        unset($_REQUEST['module']);
        unset($_REQUEST['action']);
        unset($GLOBALS['current_user']);
    }

     /**

     */
	public function testDateProperUserFormat()
	{
        //manipulate the date on the bean AFTER it's been created, making sure it is
        //a non standard date format.  We are NOT saving, we just want to mess up the UI presentation.
        $closedate = '2014/12/23';
        $this->opp->date_closed = $closedate;

        //create the view and display opportunity
		$ovd = new OpportunitiesViewDetail();
        $ovd->bean = $this->opp;
        $ovd->action = 'DetailView';
        $ovd->module = 'Opportunities';
        $ovd->type = 'detail';
        $ovd->init($this->opp);
        $ovd->preDisplay();
        $ovd->display();

        //grab the value of what the properly formatted date of the string we injected should be.  Note that this calls the
        //timedate function twice, once to grab the user format, and once to create the string
        $formatted_date = $GLOBALS['timedate']->asUserDate($GLOBALS['timedate']->fromString($closedate, $this->user), $this->user);
        //escape the characters so we can use as a regex.  turn '/' into '\/'
        $formatted_date = str_replace('/','\\/',$formatted_date);
        // lets make sure the date shows up properly formatted in the detail view output.
        $this->expectOutputRegex("/>$formatted_date</");

    }
}