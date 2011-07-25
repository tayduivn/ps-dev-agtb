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
 
require_once 'include/JSON.php';

class JSONTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        unset($_SESSION['asychronous_key']);
    }
    
    public function testCanEncodeBasicArray() 
    {
        $array = array('foo' => 'bar', 'bar' => 'foo');
        $this->assertEquals(
            '{"foo":"bar","bar":"foo"}',
            JSON::encode($array)
        );
    }

    public function testCanEncodeBasicObjects() 
    {
        $obj = new stdClass();
        $obj->foo = 'bar';
        $obj->bar = 'foo';
        $this->assertEquals(
            '{"foo":"bar","bar":"foo"}',
            JSON::encode($obj)
        );
    }
    
    public function testCanEncodeMultibyteData() 
    {
        $array = array('foo' => '契約', 'bar' => '契約');
        $this->assertEquals(
            '{"foo":"\u5951\u7d04","bar":"\u5951\u7d04"}',
            JSON::encode($array)
        );
    }
    
    public function testCanDecodeObjectIntoArray()
    {
        $array = array('foo' => 'bar', 'bar' => 'foo');
        $this->assertEquals(
            JSON::decode('{"foo":"bar","bar":"foo"}'),
            $array
        );
    }
    
    public function testCanDecodeMultibyteData() 
    {
        $array = array('foo' => '契約', 'bar' => '契約');
        $this->assertEquals(
            JSON::decode('{"foo":"\u5951\u7d04","bar":"\u5951\u7d04"}'),
            $array
        );
    }
    
    public function testEncodeRealWorks()
    {
        $array = array('foo' => 'bar', 'bar' => 'foo');
        $this->assertEquals(
            '{"foo":"bar","bar":"foo"}',
            JSON::encodeReal($array)
        );
    }
    
    public function testDecodeRealWorks()
    {
        $array = array('foo' => 'bar', 'bar' => 'foo');
        $this->assertEquals(
            JSON::decodeReal('{"foo":"bar","bar":"foo"}'),
            $array
        );
    }

    public function testCanDecodeHomefinder(){
        $response = '{"data":{"meta":{"currentPage":1,"totalMatched":1,"totalPages":1,"executionTime":0.025315999984741},"affiliates":[{"name":"Los Angeles Times","profileName":"latimes","parentCompany":"Tribune Company","isActive":true,"hasEcommerceEnabled":true,"profileNameLong":"latimes","homePageUrl":"http:\/\/www.latimes.com\/classified\/realestate\/","createDateTime":"2008-07-25T00:00:00-05:00","updateDateTime":"2011-02-16T00:00:00-06:00","id":137}]},"status":{"code":200,"errorStack":null}}';
        $json = new JSON();
        $decode = $json->decode($response);
        $this->assertNotEmpty($decode['data']['affiliates'][0]['profileName'], "Did not decode correctly");
    }

    public function testCanDecodeHomefinderAsObject(){
        $response = '{"data":{"meta":{"currentPage":1,"totalMatched":1,"totalPages":1,"executionTime":0.025315999984741},"affiliates":[{"name":"Los Angeles Times","profileName":"latimes","parentCompany":"Tribune Company","isActive":true,"hasEcommerceEnabled":true,"profileNameLong":"latimes","homePageUrl":"http:\/\/www.latimes.com\/classified\/realestate\/","createDateTime":"2008-07-25T00:00:00-05:00","updateDateTime":"2011-02-16T00:00:00-06:00","id":137}]},"status":{"code":200,"errorStack":null}}';
        $json = new JSON();
        $decode = $json->decode($response, false, false);
        $this->assertNotEmpty($decode->data->affiliates[0]->profileName, "Did not decode correctly");
    }
}
