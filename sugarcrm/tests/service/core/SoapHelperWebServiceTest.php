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
 * SoapHelperWebServiceTest.php
 *
 * This test may be used to write tests against the SoapHelperWebService.php file and the utility functions found there.
 *
 * @author Collin Lee
 */

require_once('service/core/SoapHelperWebService.php');
require_once('soap/SoapError.php');

class SoapHelperWebServiceTest extends Sugar_PHPUnit_Framework_TestCase {

static $original_service_object;

public static function setUpBeforeClass()
{
    global $service_object;
    if(!empty($service_object))
    {
        self::$original_service_object = $service_object;
    }
}

public static function tearDownAfterClass()
{
    if(!empty(self::$original_service_object))
    {
        global $service_object;
        $service_object = self::$original_service_object;
    }
}

/**
 * retrieveCheckQueryProvider
 *
 */
public function retrieveCheckQueryProvider()
{
    global $service_object;
    $service_object = new ServiceMockObject();
    $error = new SoapError();
    return array(
        array($error, "id = 'abc'", true),
        array($error, "user.id = prospects.id", true),
        array($error, "id $% 'abc'", false),
    );
}

/**
 * testCheckQuery
 * This function tests the checkQuery function in the SoapHelperWebService class
 *
 * @dataProvider retrieveCheckQueryProvider();
 */
public function testCheckQuery($errorObject, $query, $expected)
{
     $helper = new SoapHelperWebServices();
     if(!method_exists($helper, 'checkQuery'))
     {
         $this->markTestSkipped('Method checkQuery does not exist');
     }

     $result = $helper->checkQuery($errorObject, $query);
     $this->assertEquals($expected, $result, 'SoapHelperWebService->checkQuery functions as expected');
}

}

/**
 * ServiceMockObject
 *
 * Used to override global service_object
 */
class ServiceMockObject {
    public function error()
    {

    }
}