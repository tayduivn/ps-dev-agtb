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
 
/**
 * 
 * Check if getTrackerSubstring() utils function returns a html decoded value
 * which is also chopped to the tracker_max_display_length parameter
 * 
 * @ticket 55650
 * @author avucinic@sugarcrm.com
 *
 */
class Bug55650Test extends Sugar_PHPUnit_Framework_TestCase
{
	
    /**
     * @dataProvider providerTestGetTrackerSubstring
     */
    public function testGetTrackerSubstring($value, $expected)
    {
    	// Setup some helper values
    	$add = "";
    	$cut = $GLOBALS['sugar_config']['tracker_max_display_length'];
    	// If the length is longer then the set tracker_max_display_length, the substring length for asserting equal will be
    	// -3 the length of the tracker_max_display_length, and we should add ...
    	if (strlen($expected) > $GLOBALS['sugar_config']['tracker_max_display_length'])
    	{
    		$add = "...";
    		$cut = $cut - 3;
    	}
    	
    	// Test if the values got converted, and if the original string was chopped to the expected string
        $this->assertEquals(getTrackerSubstring($value), substr($expected, 0, $cut) . $add, '');
    }
    
    function providerTestGetTrackerSubstring()
    {
        return array(
        	0 => array("A lot of quotes &#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;&#039;", "A lot of quotes '''''''''''''''"),
        	1 => array("A lot of quotes <>'\" &#34; &#62; &#60; &#8364; &euro;&euro;&euro;&euro;&euro;&euro;&euro;&euro;", "A lot of quotes <>'\" \" > < € €€€€€€€€"),
        	2 => array("A lot of quotes &amp;", "A lot of quotes &"),
        );
    }
    
}

?>