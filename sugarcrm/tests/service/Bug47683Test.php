<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once 'tests/service/SOAPTestCase.php';
/**
 * This class tests that get_modified_entries returns xml with CDATA for <value> tags
 *
 */
class Bug47683Test extends SOAPTestCase
{
    public $_contact = null;
    public $_sessionId = '';

    /**
     * Create test user
     *
     */
    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $this->_setupTestContact();
    }

    /**
     * Remove anything that was used during this test
     *
     */
    public function tearDown()
    {
        parent::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestContactUtilities::removeCreatedContactsUsersRelationships();
        $this->_contact = null;
        SugarTestMeetingUtilities::removeMeetingContacts();
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }

    public function testGetModifiedEntries()
    {
        $this->_login();
        $ids = array($this->_contact->id);
        $result = $this->_soapClient->call('get_modified_entries', array('session' => $this->_sessionId, 'module_name' => 'Contacts', 'ids' => $ids, 'select_fields' => array()));
        $decoded = base64_decode($result['result']);

        $this->assertContains("<value>{$this->_contact->first_name}</value>", $decoded, "First name not found in data");
        $this->assertContains("<value>{$this->_contact->last_name}</value>", $decoded, "Last name not found in data");
    }


    /**********************************
     * HELPER PUBLIC FUNCTIONS
     **********************************/
    private function _setupTestContact() {
        $this->_contact = SugarTestContactUtilities::createContact();
        $this->_contact->last_name .= " Пупкин-Васильев"; // test special chars
        $this->_contact->description = "<==>";
        //$this->_contact->contacts_users_id = $this->_user->id;
        $this->_contact->save();
    }

}
