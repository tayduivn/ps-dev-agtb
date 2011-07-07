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
 
require_once('modules/Teams/Team.php');

class Bug33745 extends Sugar_PHPUnit_Framework_TestCase
{
    var $set_silent_upgrade = false;
    var $created_anonymous_user = false;
    
    public function setUp() 
    {
       if(!isset($_SESSION['silent_upgrade'])) {
       	  $_SESSION['silent_upgrade'] = true;
       	  $this->set_silent_upgrade = true;
       }
       
       if(!isset($GLOBALS['current_user'])) {
       	  $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
       	  $this->created_anonymous_user = true;
       }
    }    
    
    public function tearDown() 
    {
       if($this->set_silent_upgrade) {
       	  unset($_SESSION['silent_upgrade']);
       }
       
       if($this->created_anonymous_user) {
       	  SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
          unset($GLOBALS['current_user']);
       }
    }     
    
    
    public function test_team_get_display_name_function() {
    	require_once('include/utils.php');
    	$first_name = $GLOBALS['current_user']->first_name;
    	$last_name = $GLOBALS['current_user']->last_name;
	    global $locale;
	    $localeFormat = $locale->getLocaleFormatMacro($GLOBALS['current_user']);
	    $show_last_name_first = strpos($localeFormat,'l') < strpos($localeFormat,'f');
    	
    	$display_name = Team::getDisplayName($GLOBALS['current_user']->first_name, $GLOBALS['current_user']->last_name);
    	
    	if($show_last_name_first) {
    	   $this->assertEquals(trim($last_name . ' ' . $first_name), trim($display_name), "Assert that last name first format is correct");
    	} else {
    	   $this->assertEquals(trim($first_name . ' ' . $last_name), trim($display_name), "Assert that first name first format is correct");
    	}

    }

}