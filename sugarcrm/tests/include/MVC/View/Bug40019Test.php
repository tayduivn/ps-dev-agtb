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
 
require_once('include/MVC/View/SugarView.php');

class Bug40019Test extends Sugar_PHPUnit_Framework_TestCase
{   
    public function setUp() 
	{
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
	    global $sugar_config;
	    $max = $sugar_config['history_max_viewed'];
	    
	    $contacts = array();
	    for($i = 0; $i < $max + 1; $i++){
	        $contacts[$i] = SugarTestContactUtilities::createContact();
	        SugarTestTrackerUtility::insertTrackerEntry($contacts[$i], 'detailview');
	    }
        
	    for($i = 0; $i < $max + 1; $i++){
	        $account[$i] = SugarTestAccountUtilities::createAccount();
            SugarTestTrackerUtility::insertTrackerEntry($account[$i], 'detailview');
	    }
	    
	    $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
	}
	
	public function tearDown() 
	{

		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestTrackerUtility::removeAllTrackerEntries();

        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
	}
	
	// Currently, getBreadCrumbList in BreadCrumbStack.php limits you to 10
	// Also, the Constructor in BreadCrumbStack.php limits it to 10 too.
    /*
     * @group bug40019
     */
	public function testModuleMenuLastViewedForModule()
	{
	    global $sugar_config;
	    $max = $sugar_config['history_max_viewed'];
	    
	    $tracker = new Tracker();
	    $history = $tracker->get_recently_viewed($GLOBALS['current_user']->id, 'Contacts');
	    
	    $expected = $max > 10 ? 10 : $max;
        
        $this->assertTrue(count($history) == $expected);
	}
    
	// Currently, getBreadCrumbList in BreadCrumbStack.php limits you to 10
    /*
     * @group bug40019
     */
	public function testModuleMenuLastViewedForAll()
	{
	    global $sugar_config;
	    $max = $sugar_config['history_max_viewed'];
	    
	    $tracker = new Tracker();
	    $history = $tracker->get_recently_viewed($GLOBALS['current_user']->id, '');
	    
	    $expected = $max > 10 ? 10 : $max;
	    
        $this->assertTrue(count($history) == $expected);
	}
}