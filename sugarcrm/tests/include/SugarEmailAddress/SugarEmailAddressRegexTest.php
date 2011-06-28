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
 
require_once 'include/SugarEmailAddress/SugarEmailAddress.php';

class SugarEmailAddressRegexTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function providerEmailAddressRegex()
	{
	    return array(
	        array('john@john.com',true),
	        array('----!john.com',false),
	        // For Bug 13765
	        array('st.-annen-stift@t-online.de',true),
	        // For Bug 39186
	        array('qfflats-@uol.com.br',true),
	        array('atendimento-hd.@uol.com.br',true),
	        // For Bug 44338
	        array('jo&hn@john.com',true),
	        array('joh#n@john.com.br',true),
	        array('&#john@john.com', true),
	        array('atendimento-hd.?uol.com.br',false),
	        array('atendimento-hd.?uol.com.br;aaa@com.it',false),
	        array('f.grande@pokerspa.it',true),
	        array('fabio.grande@softwareontheroad.it',true),
	        array('fabio$grande@softwareontheroad.it',true),
	        // For Bug 44473
	        array('ettingshallprimaryschool@wolverhampton.gov.u',false),
	        // For Bug 42403
	        array('test@t--est.com',true),
	        // For Bug 42404
	        array('t.-est@test.com',true),
	        );
	}
    
    /**
     * @ticket 13765
     * @ticket 39186
     * @ticket 44338
     * @ticket 44473
     * @ticket 42403
     * @ticket 42404
     * @dataProvider providerEmailAddressRegex
     */
	public function testEmailAddressRegex($email, $valid) 
    {
        $startTime = microtime(true);
        $sea = new SugarEmailAddress;
        
        if ( $valid ) {
            $this->assertRegExp($sea->regex,$email);
        }
        else {
            $this->assertNotRegExp($sea->regex,$email);
        }
        
        // Checking for elapsed time. I expect that evaluation takes less than a second.
        $timeElapsed = microtime(true) - $startTime;
        $this->assertLessThan(1.0, $timeElapsed);
    }
}
