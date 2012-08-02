<?php
//FILE SUGARCRM flav=ent ONLY
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
require_once('modules/Contacts/ContactFormBase.php');

class Bug53053Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        if ( count($this->contactsToClean) > 0) {
            foreach($this->contactsToClean as $contactId) {
                $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$contactId}'");
                $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id = '{$contactId}'");
            }
        }

        foreach ($this->fields as $fieldName => $fieldValue) {
            unset($_POST[$this->prefix . $fieldName]);
        }
        unset($_POST['record']);
        unset($_POST[$this->prefix . 'id']);
        unset($_REQUEST['action']);

        parent::tearDown();
    }

    public function testPortalPasswordSave()
    {

                //Create an anonymous user for login purposes/
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;

        $this->contactsToClean = array();
        $this->prefix = 'unitTest';
        $this->fields = array('first_name' => 'contact', 'last_name' => 'unitTester');

        // Create seed contact
        $contact = new Contact();
        $contact->first_name = "unit 53053";
        $contact->last_name = "tester";
        $contact->save();
        $this->contact_id = $contact->id;
        $this->contactsToClean[] = $contact->id;

        $formBase = new ContactFormBase();

        //seed $_ vars
        foreach ($this->fields as $fieldName => $fieldValue) {
            $_POST[$this->prefix . $fieldName] = $fieldValue;
        }
        $_POST['record'] = 'asdf';
        $_REQUEST['action'] = 'save';

        // test case of new contact without portal password
        $bean = $formBase->handleSave($this->prefix, false);
        if ($bean->id) {
            $this->contactsToClean[] = $bean->id;
        }
        $contact = BeanFactory::getBean('Contacts', $bean->id);
        $this->assertNotEmpty($contact->id);
        $this->assertNull($contact->portal_password);

        // test case of new contact with portal password
        $_POST[$this->prefix . 'portal_password'] = 'asdf';

        $bean = $formBase->handleSave($this->prefix, false);
        if ($bean->id) {
            $this->contactsToClean[] = $bean->id;
        }
        $contact = BeanFactory::getBean('Contacts', $bean->id);
        $this->assertNotEmpty($contact->id);
        $this->assertNotNull($contact->portal_password);

        // test case set an existing records password
        $_POST[$this->prefix . 'id'] = $this->contact_id;
        $bean = $formBase->handleSave($this->prefix, false);
        $oldPass = $bean->portal_password;
        $contact = BeanFactory::getBean('Contacts', $bean->id);
        $this->assertNotNull($contact->portal_password);

        // test case set update existing records password
        $_POST[$this->prefix . 'portal_password'] = 'zxcv';
        $bean = $formBase->handleSave($this->prefix, false);
        $contact = BeanFactory::getBean('Contacts', $bean->id);
        $this->assertNotEquals($contact->portal_password, $oldPass);
        $oldPass = $contact->portal_password;

        // test case don't update password
        $_POST[$this->prefix . 'portal_password'] = 'value_setvalue_setvalue_set';
        $bean = $formBase->handleSave($this->prefix, false);
        $contact = BeanFactory::getBean('Contacts', $bean->id);
        $this->assertEquals($contact->portal_password, $oldPass);

        // test clear password
        $_POST[$this->prefix . 'portal_password'] = '';
        $bean = $formBase->handleSave($this->prefix, false);
        $contact = BeanFactory::getBean('Contacts', $bean->id);
        $this->assertEmpty($contact->portal_password);


        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
}