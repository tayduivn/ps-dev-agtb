<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once "modules/ProspectLists/ProspectListsService.php";

class ProspectListsServiceTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        // call a commit for transactional dbs
        $GLOBALS['db']->commit();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group prospectlistsservice
     */
    public function testAddRecordsToProspectList_AllRecordsAdded_ReturnTrue(){

        $prospectList = SugarTestProspectListsUtilities::createProspectLists();
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();
        $contact3 = SugarTestContactUtilities::createContact();

        $recordIds = array(
                $contact1->id,
                $contact2->id,
                $contact3->id
            );

        $prospectListService = new ProspectListsService();
        $results = $prospectListService->addRecordsToProspectList("Contacts", $prospectList->id, $recordIds);

        $this->assertEquals(3, count($results), "Three records should have been returned");
        $this->assertEquals(true, $results[$contact1->id]);
        $this->assertEquals(true, $results[$contact2->id]);
        $this->assertEquals(true, $results[$contact3->id]);
    }

    /**
     * @group prospectlistsservice
     */
    public function testAddToList_RecordNotFound_ReturnsFalse(){

        $prospectList = SugarTestProspectListsUtilities::createProspectLists();
        $contactId = '111-9999';

        $recordIds = array(
            $contactId
        );

        $prospectListService = new ProspectListsService();
        $results = $prospectListService->addRecordsToProspectList("Contacts", $prospectList->id, $recordIds);


        $this->assertEquals(1, count($results), "Three records should have been returned");
        $this->assertEquals(false, $results[$contactId]);

    }

    /**
     * @group prospectlistsservice
     */
    public function testAddToList_ProspectListNotFound_ReturnsFalse(){

        $prospectListId = 'prospect1234';

        $prospectListService = new ProspectListsService();
        $results = $prospectListService->addRecordsToProspectList("Contacts", $prospectListId, array());

        $this->assertEquals(false, $results);
    }
}
