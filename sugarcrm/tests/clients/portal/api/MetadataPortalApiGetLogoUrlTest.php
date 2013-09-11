<?php
//FILE SUGARCRM flav=ent ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'clients/portal/api/MetadataPortalApi.php';

/**
 * @group ApiTests
 */
class MetadataPortalGetLogoUrlApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $db;
    public $serviceMock;
    public $metadataApi;
    public $previousLogoUrl;
    public $previousSettings;
    public $testId = 0;

    public function setUp()
    {
        SugarTestHelper::setUp("app_strings");
        SugarTestHelper::setUp("app_list_strings");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp('current_user');

        //save current settings
        $this->previousSettings = sugar_cache_retrieve('admin_settings_cache');
        sugar_cache_clear('admin_settings_cache');

        //save current portal logo url
        $this->db = DBManagerFactory::getInstance();
        $query = $this->db->query("SELECT value FROM config where category = 'portal' and name = 'logoURL'");
        $row = $this->db->fetchByAssoc($query);
        $this->previousLogoUrl = html_entity_decode($row['value']);

        //fake portal logo url for tests
        $this->testId++;
        $logoUrlTest = json_encode("my/path/to/portal/logo{$this->testId}.png");
        if (empty($this->previousLogoUrl)) {
            //Insert a fake logo url
            $this->db->query(
                "INSERT INTO config
                (category, platform, name, value) VALUES ('portal', 'support', 'logoURL', '{$logoUrlTest}')"
            );
        } else {
            //or update existing one
            $this->db->query(
                "UPDATE config SET value = '{$logoUrlTest}' WHERE category = 'portal' and name = 'logoURL'"
            );
        }

        //let's go
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
        $this->metadataApi = new MetadataPortalApi();
    }

    public function tearDown()
    {
        if (!empty($this->previousLogoUrl)) {
            //Revert to previous portal logo url
            $this->db->query(
                "INSERT INTO config
                (category, platform, name, value) VALUES ('portal', 'support', 'logoURL', '{$this->previousLogoUrl}')"
            );
        }

        sugar_cache_put('admin_settings_cache', $this->previousSettings);
        SugarTestHelper::tearDown();

        parent::tearDown();
    }

    /**
     * Tests finding Portal Logo URL
     */
    public function testGetPublicMetadata()
    {
        $result = $this->metadataApi->getPublicMetadata($this->serviceMock, array());
        $this->assertNotEmpty($result['logo_url']);
        $this->assertEquals("my/path/to/portal/logo{$this->testId}.png", $result['logo_url']);

        //clear config and cache
        $this->db->query("DELETE FROM config WHERE category = 'portal' and name = 'logoURL'");
        sugar_cache_clear('admin_settings_cache');

        //Make sure that if no portal logo url defined, it falls back to the company logo url of base app
        $result2 = $this->metadataApi->getPublicMetadata($this->serviceMock, array());
        $this->assertNotEmpty($result2['logo_url']);
        $themeObject = SugarThemeRegistry::current();
        $this->assertContains($themeObject->getImageURL('company_logo.png'), $result2['logo_url']);
    }


    /**
     * Tests finding Portal Logo URL
     */
    public function testGetAllMetadata()
    {
        $result = $this->metadataApi->getAllMetadata($this->serviceMock, array());
        $this->assertNotEmpty($result['logo_url']);
        $this->assertEquals("my/path/to/portal/logo{$this->testId}.png", $result['logo_url']);

        //clear config and cache
        $this->db->query("DELETE FROM config WHERE category = 'portal' and name = 'logoURL'");
        sugar_cache_clear('admin_settings_cache');

        //Make sure that if no portal logo url defined, it falls back to the company logo url of base app
        $result = $this->metadataApi->getAllMetadata($this->serviceMock, array());
        $this->assertNotEmpty($result['logo_url']);
        $themeObject = SugarThemeRegistry::current();
        $this->assertContains($themeObject->getImageURL('company_logo.png'), $result['logo_url']);
    }
}
