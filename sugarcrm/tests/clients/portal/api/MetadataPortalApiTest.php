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
class MetadataPortalApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $metadataApi;

    public function setUp()
    {

        SugarTestHelper::setUp("app_strings");
        SugarTestHelper::setUp("app_list_strings");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp('current_user');

        $this->metadataApi= new MetadataPortalApi();
    }

    public function tearDown()
    {

        SugarTestHelper::tearDown();

        parent::tearDown();
    }

    /**
     * Tests finding Portal modules
     */
    public function testFindPortalModules()
    {
        $result = $this->metadataApi->findPortalModules();
        $this->assertTrue(in_array("Notes", $result), "Notes should be included in Portal metadata");
        $this->assertTrue(in_array("Home", $result), "Home should be included in Portal metadata");
        $this->assertTrue(in_array("Contacts", $result));
        $this->assertTrue(in_array("Cases", $result));
        $this->assertTrue(in_array("Bugs", $result));
        $this->assertTrue(in_array("KBDocuments", $result));
        $this->assertFalse(
            in_array("Accounts", $result),
            "Portal metadata should not contain non-portal modules like Accounts"
        );
    }
}
