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

require_once 'include/api/RestService.php';

class ApiHeaderTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->headers = array(
            'Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
            'Expires', 'pageload + 4 hours',
            'Pragma', 'nocache',
            );
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');

    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testSetHeaders() {
        $api = new RestServiceMock();

        foreach($this->headers AS $header => $info) {
            $api->setHeader($header, $info);
        }

        $this->assertEquals($this->headers, $api->getResponseHeaders(), "The Headers Do Not Match");

    }

    public function testSendHeaders() {
        $api = new RestServiceMock();

        $expected_return = '';
        foreach($this->headers AS $header => $info) {
            $api->setHeader($header, $info);
            $expected_return = "{$header}:{$info}\r\n";
        }

        $return = $api->sendHeaders();

        $this->assertEquals($expected_return, $return, "The Headers Sent were incorrect");

    }

    public function testRequestHeaders() {

        $api = new RestServiceMock();

        $headers = $api->getRequest()->request_headers;

        $this->assertNotEmpty($headers, "The Request Headers Are Empty");
    }
}

class RestServiceMock extends RestService {
    public function getResponseHeaders() {
        return $this->response_headers;
    }
    // overloading to return the headers it would send as a string to verify it working
    public function sendHeaders() {
        $return = '';
        foreach($this->getResponseHeaders() AS $header => $info) {
            $return = "{$header}:{$info}\r\n";
        }
        return $return;
    }
}
