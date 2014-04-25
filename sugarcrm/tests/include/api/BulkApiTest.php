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
require_once 'clients/base/api/BulkApi.php';

class BulkApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        unset($_GET);
        if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            unset($GLOBALS['HTTP_RAW_POST_DATA']);
        }
    }

    public function tearDown()
    {
        while ( ob_get_level() > 1 ) {
            ob_end_flush();
        }
    }

    public function testBulkApi()
    {
        $api = new RestService();
        $api->user = $GLOBALS['current_user'];
        $requests = array(
          array('url' => '/v10/me', 'method' => 'GET'),
          array('url' => '/v10/lang/en_us', 'method' => 'GET'),
          array('url' => '/v10/404', 'method' => 'GET'), // no such route
          array('url' => '/v10/Accounts/x123-456x', 'method' => 'PUT'), // no such record
          array('url' => "/v10/Users/{$GLOBALS['current_user']->id}?test=1"), // implied GET
          array('url' => "/v10/Users/{$GLOBALS['current_user']->id}/link"), // missing param
          array('url' => "/v10/Users", "method" => 'POST', 'data' => "b;ah"), // bad JSON
          array('url' => "/v10/Users", "method" => 'POST', 'data' => json_encode(array("id" => $GLOBALS['current_user']->id, "name" => "test"))), // unauthorized
          // queries
          array('url' => "/v10/Users?fields=name,date_modified,id&filter=[{\"id\":\"{$GLOBALS['current_user']->id}\"}]", "method" => "GET"),
          array('url' => "/v07/me"), // bad version
        );
        $apiClass = new BulkApi();
        $args = array("requests" => $requests);
        $result = $apiClass->bulkCall($api, $args);

        $this->assertEquals(count($requests), count($result), "Response is missing elements");
        foreach($result as $i => $item) {
            $this->assertArrayHasKey("contents", $result[$i], "Missing contents for response $i");
            $this->assertArrayHasKey("status", $result[$i], "Missing status for response $i");
        }

        // test plain req
        $this->assertEquals("200", $result[0]['status'], "Bad status for request 0");
        $this->assertEquals($GLOBALS['current_user']->id, $result[0]['contents']['current_user']['id'], "Bad data for request 0");
        $this->assertArrayHasKey('ETag', $result[0]['headers']);
        // test headers
        $this->assertEquals("200", $result[1]['status'], "Bad status for request 1");
        $this->assertContains("app_list_strings", $result[1]['contents']);
        $this->assertArrayHasKey('Content-Type', $result[1]['headers']);
        $this->assertEquals('application/json', $result[1]['headers']['Content-Type']);
        // test 404 from unknown endpoint
        $this->assertEquals("404", $result[2]['status'], "Bad status for request 2");
        // test 404 from unknown record
        $this->assertEquals("404", $result[3]['status'], "Bad status for request 3");
        // implied GET
        $this->assertEquals("200", $result[4]['status'], "Bad status for request 4");
        $this->assertEquals($GLOBALS['current_user']->id, $result[4]['contents']['id'], "Bad data for request 4");
        // missing param
        $this->assertEquals("404", $result[5]['status'], "Bad status for request 5");
        // bad JSON
        $this->assertEquals("422", $result[6]['status'], "Bad status for request 6");
        // unauthorized
        $this->assertEquals("403", $result[7]['status'], "Bad status for request 7");
        // with parameters
        $this->assertEquals("200", $result[8]['status'], "Bad status for request 8");
        $this->assertEquals(-1, $result[8]['contents']['next_offset'], "Bad next offset for request 8");
        $this->assertEquals($GLOBALS['current_user']->id, $result[8]['contents']['records'][0]['id'], "Bad fetched ID for request 8");
        // bad version
        $this->assertEquals("301", $result[9]['status'], "Bad status for request 9");
    }

    /**
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testBulkApiError()
    {
        $api = new RestService();
        $api->user = $GLOBALS['current_user'];
        $requests = array(
                array('Xurl' => '/v10/me', 'method' => 'GET'),
        );
        $apiClass = new BulkApi();
        $args = array("requests" => $requests);
        $result = $apiClass->bulkCall($api, $args);

    }
}
