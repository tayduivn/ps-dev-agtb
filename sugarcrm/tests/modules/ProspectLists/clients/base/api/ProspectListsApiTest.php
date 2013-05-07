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

require_once('tests/rest/RestTestBase.php');

/***
 * Used to test Lead Convert in Leads Module endpoints from LeadConvertApi.php
 *
 * @group prospectlistsapi
 */
class ProspectListsApiTest extends RestTestBase
{
    public function setup()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        SugarTestProspectListsUtilities::removeAllCreatedProspectLists();
        parent::tearDown();
    }

    /**
     * @group prospectlistsapi
     */
    public function testAddToList_Contacts_AllRecordsAdded()
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
        $prospectList = SugarTestProspectListsUtilities::createProspectLists();
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();
        $contact3 = SugarTestContactUtilities::createContact();

        $postData = array(
            "module" => "Contacts",
            "prospectListId" => $prospectList->id,
            "recordIds" => array(
                $contact1->id,
                $contact2->id,
                $contact3->id
            )
        );

        $response = $this->_restCall("ProspectLists/addToList", json_encode($postData), "POST");
        $results = $response['reply'];

        $this->assertEquals(3, count($results), "Three records should have been returned");
        $this->assertEquals(true, $results[$contact1->id]);
        $this->assertEquals(true, $results[$contact2->id]);
        $this->assertEquals(true, $results[$contact3->id]);
    }

    /**
     * @group prospectlistsapi
     */
    public function testAddToList_RecordNotFound_ReturnsFalse()
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
        $prospectList = SugarTestProspectListsUtilities::createProspectLists();
        $contactId = '111-9999';

        $postData = array(
            "module" => "Contacts",
            "prospectListId" => $prospectList->id,
            "recordIds" => array(
                $contactId
            )
        );

        $response = $this->_restCall("ProspectLists/addToList", json_encode($postData), "POST");
        $results = $response['reply'];

        $this->assertEquals(1, count($results), "Three records should have been returned");
        $this->assertEquals(false, $results[$contactId]);
    }

    /**
     * @group prospectlistsapi
     */
    public function testAddToList_ProspectListNotFound_ThrowsException()
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
        $prospectListId = 'prospect1234';
        $contact1 = SugarTestContactUtilities::createContact();

        $postData = array(
            "module" => "Contacts",
            "prospectListId" => $prospectListId,
            "recordIds" => array(
                $contact1->id
            )
        );

        $response = $this->_restCall("ProspectLists/addToList", json_encode($postData), "POST");

        $this->assertEquals(404, $response['info']['http_code'], "Expected Request Failure Http Status Code");
        $this->assertEquals("not_found", $response['reply']['error'], "Expected Request Failure Response");
    }

    /**
     * @group prospectlistsapi
     * @dataProvider prospectlistDataProvider
     */
    public function testAddToList_MissingParameters_ThrowsException($moduleName, $prospectListId, $recordIds)
    {
        $this->markTestIncomplete('Migrate this to SOAP UI');
         $postData = array(
            "module" => $moduleName,
            "prospectListId" => $prospectListId,
            "recordIds" => $recordIds
         );

        $response = $this->_restCall("ProspectLists/addToList", json_encode($postData), "POST");
        $this->assertEquals(412, $response['info']['http_code'], "Expected Request Failure Http Status Code");
        $this->assertEquals("missing_parameter", $response['reply']['error'], "Expected Request Failure Response");
    }

    public function prospectlistDataProvider()
    {
        return array(
            //no module name
            array(null, '123', array('123')),
            array('', '123', array('123')),
            //no prospectlistsid
            array('Contacts', null, array('123')),
            array('Contacts', '', array('123')),
            //no record ids
            array('Contacts', '123', null),
            array('Contacts', '123', array())
        );
    }
}
