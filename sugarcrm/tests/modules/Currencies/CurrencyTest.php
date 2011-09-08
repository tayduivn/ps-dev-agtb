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
require_once('modules/Currencies/Currency.php');

class CurrencyTest extends Sugar_PHPUnit_Framework_TestCase {
	
	var $previousCurrentUser;
	
    public function setUp() 
    {
    	global $current_user;
    	$this->previousCurrentUser = $current_user;       
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->setPreference('number_grouping_seperator', ',', 0, 'global');
        $current_user->setPreference('decimal_seperator', '.', 0, 'global');
        $current_user->save();
        //Force reset on dec_sep and num_grp_sep because the dec_sep and num_grp_sep values are stored as static variables
	    get_number_seperators(true);  
    }	

    public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        global $current_user;
        $current_user = $this->previousCurrentUser;
    }    
    
    public function testUnformatNumber()
    {
    	global $current_user;
    	$testValue = "$100,000.50";
    	
    	$unformattedValue = unformat_number($testValue);
    	$this->assertEquals($unformattedValue, 100000.50, "Assert that $100,000.50 becomes 100000.50. Formatted value is: ".$unformattedValue);
    	
    	//Switch the num_grp_sep and dec_sep values
        $current_user->setPreference('number_grouping_seperator', '.');
        $current_user->setPreference('decimal_seperator', ',');
        $current_user->save();

        //Force reset on dec_sep and num_grp_sep because the dec_sep and num_grp_sep values are stored as static variables
	    get_number_seperators(true);       
        
        $testValue = "$100.000,50";
        $unformattedValue = unformat_number($testValue);
    	$this->assertEquals($unformattedValue, 100000.50, "Assert that $100.000,50 becomes 100000.50. Formatted value is: ".$unformattedValue);
    }
    
    
    public function testFormatNumber()
    {
    	global $current_user;
    	$testValue = "100000.50";
    	
    	$formattedValue = format_number($testValue);
    	$this->assertEquals($formattedValue, "100,000.50", "Assert that 100000.50 becomes 100,000.50. Formatted value is: ".$formattedValue);
    	
    	//Switch the num_grp_sep and dec_sep values
        $current_user->setPreference('number_grouping_seperator', '.');
        $current_user->setPreference('decimal_seperator', ',');
        $current_user->save();

        //Force reset on dec_sep and num_grp_sep because the dec_sep and num_grp_sep values are stored as static variables
	    get_number_seperators(true);       
        
        $testValue = "100000.50";
        $formattedValue = format_number($testValue);
    	$this->assertEquals($formattedValue, "100.000,50", "Assert that 100000.50 becomes 100.000,50. Formatted value is: ".$formattedValue);
    }    
    
} 

?>