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
require_once('modules/Contacts/Contact.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/ContactFormBase.php');
require_once('include/api/ServiceBase.php');
require_once('clients/base/api/ModuleApi.php');
require_once('modules/Contacts/ContactsApiHelper.php');

class ContactsBugFixesTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp() {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        $this->fields = array('first_name' => 'contact', 'last_name' => 'unitTester', 'sync_contact' => '1');
        $this->prefix = 'unittest_contacts_bugfixes';
        $this->contacts = array();
    }

    public function tearDown() {
        foreach($this->fields AS $fieldName => $fieldValue) {
            unset($_POST[$fieldName]);
        }
        foreach($this->contacts AS $contact) {
            $contact->mark_deleted($contact->id);
        }
        SugarTestHelper::tearDown();
    }

	public function testBug59675ContactFormBaseRefactor() {
        $formBase = new ContactFormBase();
        foreach ($this->fields as $fieldName => $fieldValue) {
            $_POST[$this->prefix . $fieldName] = $fieldValue;
        }
        $_POST['record'] = 'asdf';
        $_REQUEST['action'] = 'save';

        $bean = $formBase->handleSave($this->prefix, false);
        $this->contacts[] = $bean;

        $this->assertTrue($bean->sync_contact, "Sync Contact was not set to true");

        unset($bean);
        $_POST[$this->prefix . 'sync_contact'] = '0';        

        $bean = $formBase->handleSave($this->prefix, false);
        $this->contacts[] = $bean;

        $this->assertFalse($bean->sync_contact, "Sync Contact was not set to false");


    }

    public function testPopulateFromApiSyncContactTrue() {
        $capih = new ContactsApiHelper(new ContactsBugFixesServiceMockup);
        $contact = BeanFactory::newBean('Contacts');
        $submittedData = array('sync_contact' => true);
        $data = $capih->populateFromApi($contact, $submittedData);
        $contact->save();
        $contact->retrieve($contact->id);
        $this->assertTrue($contact->sync_contact);
        $contact->mark_deleted($contact->id);
    }

    public function testPopulateFromApiSyncContactFalse() {
        $capih = new ContactsApiHelper(new ContactsBugFixesServiceMockup);
        $contact = BeanFactory::newBean('Contacts');
        $submittedData = array('sync_contact' => false);
        $data = $capih->populateFromApi($contact, $submittedData);
        $contact->save();
        $contact->retrieve($contact->id);
        $this->assertEmpty($contact->sync_contact);
        $contact->mark_deleted($contact->id);
    }    
}

class ContactsBugFixesServiceMockup extends ServiceBase {
    public function execute() {}
    protected function handleException(Exception $exception) {}
}
?>