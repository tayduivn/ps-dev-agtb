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

require_once 'clients/base/api/MetadataApi.php';

class MetadataApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var MetadataApi
     */
    protected $api;

    /**
     * @var RestService
     */
    protected $serviceMock;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("current_user");
    }

    public function setUp()
    {
        $this->api = new MetadataApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function testGetModuleTabMap()
    {
        // This used to live in the MetadataApi, hence the reason for this test
        $mm  = MetaDataManager::getManager();
        $data = $mm->getModuleTabMap();

        // Test see that the map is not empty and an array
        $this->assertInternalType('array', $data, "module_tab_map is not an array");
        $this->assertNotEmpty($data, "Module Tab Map is empty");

        // Test that a known value is in the data
        $this->assertEquals('Emails', $data['EmailTemplates'], "EmailTemplates not translated properly");
    }

    /**
     * Test asserts behavior of getAllMetadata
     */
    public function testGetAllMetadata()
    {
        $result = $this->api->getAllMetadata($this->serviceMock, array());

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('full_module_list', $result);
        $this->assertArrayHasKey('modules', $result);
        $this->assertArrayHasKey('hidden_subpanels', $result);
        $this->assertArrayHasKey('currencies', $result);
        $this->assertArrayHasKey('module_tab_map', $result);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('views', $result);
        $this->assertArrayHasKey('layouts', $result);
        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('config', $result);
        $this->assertArrayHasKey('relationships', $result);
        $this->assertArrayHasKey('jssource', $result);
        $this->assertArrayHasKey('server_info', $result);
        $this->assertArrayHasKey('logo_url', $result);
        $this->assertArrayHasKey('languages', $result);
        $this->assertArrayHasKey('_override_values', $result);
        $this->assertArrayHasKey('_hash', $result);


        $this->assertInternalType('array', $result['full_module_list']);
        $this->assertInternalType('array', $result['modules']);
        $this->assertInternalType('array', $result['hidden_subpanels']);
        $this->assertInternalType('array', $result['currencies']);
        $this->assertInternalType('array', $result['module_tab_map']);
        $this->assertInternalType('array', $result['fields']);
        $this->assertInternalType('array', $result['views']);
        $this->assertInternalType('array', $result['layouts']);
        $this->assertInternalType('array', $result['labels']);
        $this->assertInternalType('array', $result['config']);
        $this->assertInternalType('array', $result['relationships']);
        $this->assertInternalType('string', $result['jssource']);
        $this->assertInternalType('array', $result['server_info']);
        $this->assertInternalType('string', $result['logo_url']);
        $this->assertInternalType('array', $result['languages']);
        $this->assertInternalType('array', $result['_override_values']);
        $this->assertInternalType('string', $result['_hash']);
    }

    /**
     * Test asserts behavior of getPublicMetadata
     */
    public function testGetPublicMetadata()
    {
        $result = $this->api->getPublicMetadata($this->serviceMock, array());

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('modules', $result);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('views', $result);
        $this->assertArrayHasKey('layouts', $result);
        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('config', $result);
        $this->assertArrayHasKey('jssource', $result);
        $this->assertArrayHasKey('logo_url', $result);
        $this->assertArrayHasKey('_override_values', $result);
        $this->assertArrayHasKey('_hash', $result);


        $this->assertInternalType('array', $result['modules']);
        $this->assertInternalType('array', $result['fields']);
        $this->assertInternalType('array', $result['views']);
        $this->assertInternalType('array', $result['layouts']);
        $this->assertInternalType('array', $result['labels']);
        $this->assertInternalType('array', $result['config']);
        $this->assertInternalType('string', $result['jssource']);
        $this->assertInternalType('string', $result['logo_url']);
        $this->assertInternalType('array', $result['_override_values']);
        $this->assertInternalType('string', $result['_hash']);
    }

    /**
     * Test asserts behavior of getLanguage
     */
    public function testGetLanguage()
    {
        $result = $this->api->getLanguage($this->serviceMock, array('lang' => 'en'));

        $this->assertNotEmpty($result);
        $this->assertJson($result);
        $result = json_decode($result, true);

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('app_list_strings', $result);
        $this->assertArrayHasKey('app_strings', $result);
        $this->assertArrayHasKey('mod_strings', $result);
        $this->assertArrayHasKey('_hash', $result);


        $this->assertInternalType('array', $result['app_list_strings']);
        $this->assertInternalType('array', $result['app_strings']);
        $this->assertInternalType('array', $result['mod_strings']);
        $this->assertInternalType('string', $result['_hash']);
    }

    /**
     * Test asserts behavior of getPublicLanguage
     */
    public function testGetPublicLanguage()
    {
        $result = $this->api->getPublicLanguage($this->serviceMock, array('lang' => 'en'));

        $this->assertNotEmpty($result);
        $this->assertJson($result);
        $result = json_decode($result, true);

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('app_list_strings', $result);
        $this->assertArrayHasKey('app_strings', $result);
        $this->assertArrayHasKey('_hash', $result);

        $this->assertInternalType('array', $result['app_list_strings']);
        $this->assertInternalType('array', $result['app_strings']);
        $this->assertInternalType('string', $result['_hash']);
    }
}
