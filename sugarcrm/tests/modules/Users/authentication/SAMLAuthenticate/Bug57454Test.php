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
require_once('modules/Users/authentication/AuthenticationController.php');

/**
 * @ticket 57454
*/
class Bug57454Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if(!function_exists('gzinflate')) {
            $this->markTestSkipped("No gzip - skipping");
        }
            if(!function_exists('simplexml_load_string')) {
            $this->markTestSkipped("No SimpleXML - skipping");
        }
        parent::setUp();
    }

    public function testSAMLEncoding()
    {
        require_once('modules/Users/authentication/SAMLAuthenticate/lib/onelogin/saml.php');
        require('modules/Users/authentication/SAMLAuthenticate/settings.php');
        $authrequest = new SamlAuthRequest($settings);
        $url = $authrequest->create();
        $query = parse_url($url, PHP_URL_QUERY);
        $this->assertNotEmpty($query, 'No query part');
        parse_str($query, $components);
        $this->assertArrayHasKey('SAMLRequest', $components);
        $data = gzinflate(base64_decode(rawurldecode($components['SAMLRequest'])));
        $this->assertNotEmpty($data, "Data did not decode");
        $xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NONET);
        $this->assertNotEmpty($xml, 'XML did not parse');
        $myurl = $xml['AssertionConsumerServiceURL'];
        $this->assertNotEmpty($myurl, 'URL not found');
        $this->assertEquals(parse_url($GLOBALS['sugar_config']['site_url']. "/index.php", PHP_URL_PATH), parse_url($myurl, PHP_URL_PATH), "Bad URL");
    }
}