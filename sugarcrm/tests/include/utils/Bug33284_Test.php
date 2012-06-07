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
 
class Bug33284_Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $max_display_set = false;
    var $max_display_length;
    
    public function setUp() {
    	if(isset($sugar_config['tracker_max_display_length'])) {
    	   $this->max_display_set = true;
    	   $this->max_display_length = $sugar_config['tracker_max_display_length'];
    	}
    }
    
    public function tearDown() {
        if($this->max_display_set) {
           global $sugar_config; 
           $sugar_config['tracker_max_display_length'] = $this->max_display_length;
        }
    }

    public function test_get_tracker_substring1()
    {
        global $sugar_config;       
        
        //BEGIN SUGARCRM flav=com ONLY
        $default_length = 15;
        //END SUGARCRM flav=com ONLY
        //BEGIN SUGARCRM flav!=com ONLY
        $default_length = 30;
        //END SUGARCRM flav!=com ONLY    	
    	
        $sugar_config['tracker_max_display_length'] = $default_length;
        
        $test_string = 'The quick brown fox jumps over lazy dogs';
        $display_string = getTrackerSubstring($test_string);
        $this->assertEquals(strlen(from_html($display_string)), $default_length, 'Assert that the string length is equal to ' . $default_length . ' characters');
    }
    
    
    public function test_get_tracker_substring2()
    {
    	global $sugar_config;       
        $test_string = '"Hello There How Are You? " This has quotes too';
        
        //BEGIN SUGARCRM flav=com ONLY
        $default_length = 15;
        //END SUGARCRM flav=com ONLY
        //BEGIN SUGARCRM flav!=com ONLY
        $default_length = 30;
        //END SUGARCRM flav!=com ONLY
 
        $sugar_config['tracker_max_display_length'] = $default_length;
        
        $display_string = getTrackerSubstring($test_string);  
        $this->assertEquals(strlen(from_html($display_string)), $default_length, 'Assert that the string length is equal to ' . $default_length . ' characters (default)');

		$test_string = '早前於美國完成民族音樂學博士學位回港後在大專院校的音樂系任教123456789';
        $display_string = getTrackerSubstring($test_string);

        $this->assertEquals(mb_strlen(from_html($display_string), 'UTF-8'), $default_length, 'Assert that the string length is equal to ' . $default_length . ' characters (default)');    
    }  
}

?>