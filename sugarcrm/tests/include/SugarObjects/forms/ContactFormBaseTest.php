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

/**
 * ContactFormBaseTest.php
 *
 */

require_once('modules/Contacts/ContactFormBase.php');

class ContactFormBaseTest extends Sugar_PHPUnit_Framework_TestCase {

var $form;
var $contact1;

public function setup()
{
    $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name = 'Mike' AND last_name = 'TheSituationSorrentino'");
    $this->form = new ContactFormBase();
    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Contacts');

    //Create a test Contact
    $this->contact1 = SugarTestContactUtilities::createContact();
    $this->contact1->first_name = 'Collin';
    $this->contact1->last_name = 'Lee';
    $this->contact1->save();
}

public function tearDown()
{
    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    SugarTestContactUtilities::removeAllCreatedContacts();
    unset($this->form);
    unset($this->contact1);
}


/**
 * contactsProvider
 *
 */
public function contactsProvider()
{
    return array(
        array('Collin', 'Lee', true),
        array('', 'Lee', true),
        array('Mike', 'TheSituationSorrentino', false)
    );
    
}


/**
 * testCreatingDuplicateContact
 *
 * @dataProvider contactsProvider
 */
public function testCreatingDuplicateContact($first_name, $last_name, $hasDuplicate)
{
    $_POST['first_name'] = $first_name;
    $_POST['last_name'] = $last_name;
    $rows = $this->form->checkForDuplicates();

    if($hasDuplicate)
    {
        $this->assertTrue(count($rows) > 0, 'Assert that checkForDuplicates returned matches');
        $this->assertEquals($rows[0]['last_name'], $last_name, 'Assert duplicate row entry last_name is ' . $last_name);
        $output = $this->form->buildTableForm($rows);
        $this->assertRegExp('/\&action\=DetailView\&record/', $output, 'Assert we have the DetailView links to records');
    } else {
        $this->assertTrue(empty($rows), 'Assert that checkForDuplicates returned no matches');
    }
}

}