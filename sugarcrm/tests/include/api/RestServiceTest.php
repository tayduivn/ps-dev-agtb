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

require_once 'tests/SugarTestReflection.php';
require_once 'include/api/RestService.php';

class RestServiceTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        unset($_GET);
        if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            unset($GLOBALS['HTTP_RAW_POST_DATA']);
        }
    }


    public function testGetRequestArgs()
    {
        $request = $this->getMock('request', array('getPathVars'));
        $request->expects($this->any())
                ->method('getPathVars')
                ->will($this->returnValue(array()));

        $_GET = array('my_json'=>'{"christopher":"walken","bill":"murray"}');
        $GLOBALS['HTTP_RAW_POST_DATA'] = '';

        $service = new RestService();
        SugarTestReflection::setProtectedValue($service, 'request', $request);

        $output = SugarTestReflection::callProtectedMethod($service, 'getRequestArgs', array(array('jsonParams'=>array('my_json'))));
        $this->assertArrayHasKey('christopher', $output['my_json'], "Missing Christopher => Walken #1");
        $this->assertArrayHasKey('bill', $output['my_json'], "Missing Bill => Murray #1");

        $_GET = array('my_json'=>'{"christopher":"walken","bill":"murray"}}');
        $GLOBALS['HTTP_RAW_POST_DATA'] = '';

        $hadException = false;
        try {
            $output = SugarTestReflection::callProtectedMethod($service, 'getRequestArgs', array(array('jsonParams'=>array('my_json'))));
        } catch ( SugarApiExceptionInvalidParameter $e ) {
            $hadException = true;
        }
        
        $this->assertTrue($hadException, "Did not throw an exception on invalid JSON #1");

        $_GET = array(); 
        $GLOBALS['HTTP_RAW_POST_DATA'] = '{"my_json":{"christopher":"walken","bill":"murray"}}';

        $output = SugarTestReflection::callProtectedMethod($service, 'getRequestArgs', array(array()));
        
        $this->assertArrayHasKey('christopher', $output['my_json'], "Missing Christopher => Walken #2");
        $this->assertArrayHasKey('bill', $output['my_json'], "Missing Bill => Murray #2");

        $_GET = array(); 
        $GLOBALS['HTTP_RAW_POST_DATA'] = '{"my_json":{"christopher":"walken","bill":"murray"}}}';

        $hadException = false;
        try {
            $output = SugarTestReflection::callProtectedMethod($service, 'getRequestArgs', array(array()));
        } catch ( SugarApiExceptionInvalidParameter $e ) {
            $hadException = true;
        }
        
        $this->assertTrue($hadException, "Did not throw an exception on invalid JSON #2");

    }
}
