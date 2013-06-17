<?php
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

require_once 'include/api/SugarApi.php';
require_once 'include/api/RestService.php';
require_once 'clients/portal/api/CurrentUserPortalApi.php';

/**
 * @group ApiTests
 */
class CurrentUserPortalApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $currentUserApi;
    public $contact;

    public function setUp()
    {

        SugarTestHelper::setUp("app_strings");
        SugarTestHelper::setUp("app_list_strings");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("beanList");
        SugarTestHelper::setUp('current_user');

        $contact = new Contact();
        $contact->id = uniqid('c_');
        $contact->first_name = 'testfirst';
        $contact->last_name = 'testlast';
        $contact->picture = 'testpicture';
        $contact->portal_active = 1;
        $contact->portal_name = 'testportal';
        $contact->new_with_id = true;
        $contact->disable_custom_fields = true;
        $contact->save();

        $this->contact = $contact;

        $this->currentUserApi= new CurrentUserPortalApi();
        $this->currentUserApi->portal_contact = $contact;
    }

    public function tearDown()
    {
        $this->contact->db->query("DELETE FROM contacts WHERE id = '" . $this->contact->id . "'");

        SugarTestHelper::tearDown();

        parent::tearDown();
    }

    /**
     * Tests current user for using picture from contact
     */
    public function testContactPicture()
    {
        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $result = $this->currentUserApi->retrieveCurrentUser($api, array());

        $this->assertArrayHasKey('picture', $result['current_user']);
        $this->assertNotEmpty($result['current_user']['picture']);
        $this->assertEquals('testpicture', $result['current_user']['picture']);
    }
}