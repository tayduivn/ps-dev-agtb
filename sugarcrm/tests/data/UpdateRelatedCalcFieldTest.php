<?php
// FILE SUGARCRM flav=pro ONLY

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('data/SugarBean.php');
require_once("modules/Administration/QuickRepairAndRebuild.php");

class UpdateRelatedCalcFieldTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $testAccount;
    protected $createdBeans = array();
    protected $createdFiles = array();

    public function setUp()
	{
	    $this->markTestIncomplete('Disabled by John Mertic');
	    require('include/modules.php');
	    $GLOBALS['beanList'] = $beanList;
	    $GLOBALS['beanFiles'] = $beanFiles;
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = 1;
	    $GLOBALS['current_user']->setPreference('timezone', "America/Los_Angeles");
	    $GLOBALS['current_user']->setPreference('datef', "m/d/Y");
		$GLOBALS['current_user']->setPreference('timef', "h.iA");

        //Create a CF on the description field.
        $extensionContent = <<<EOQ
<?php
\$dictionary['Account']['fields']['description']['calculated'] = true;
\$dictionary['Account']['fields']['description']['formula']    = 'count(\$contacts)';
\$dictionary['Account']['fields']['description']['enforced']   = true;

EOQ;
        create_custom_directory("Extension/modules/Accounts/Ext/Vardefs/description_calc_field.php");
        $fileLoc = "custom/Extension/modules/Accounts/Ext/Vardefs/description_calc_field.php";
        $this->createdFiles[] = $fileLoc;
        file_put_contents($fileLoc, $extensionContent);
        $_REQUEST['repair_silent']=1;
        $rc = new RepairAndClear();
        $rc->repairAndClearAll(array("rebuildExtensions", "clearVardefs"), array("Accounts", "Contacts"),  false, false);
	}

	/*public function tearDown()
	{
	    foreach($this->createdBeans as $bean)
        {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }
        foreach($this->createdFiles as $file)
        {
            if (is_file($file))
                unlink($file);
        }
        $rc = new RepairAndClear();
        $rc->repairAndClearAll(array("rebuildExtensions", "clearVardefs"), array("Accounts", "Contacts"), false, false);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	    unset($GLOBALS['current_user']);
	    unset($GLOBALS['beanList']);
	    unset($GLOBALS['beanFiles']);
	}*/
	

	public function testUpdateAccountCFWhenContactSave()
	{
        $account = new Account();
        $account->name = "CalcFieldTestAccount";
        $account->save();
        $this->createdBeans[] = $account;
        $this->assertEmpty($account->description);

        //First try a simple new Contact
        $contact1 = new Contact();
        $contact1->name = "CalcFieldTestContact1";
        $contact1->account_id = $account->id;
        $contact1->save();
        $this->createdBeans[] = $contact1;

        //refresh the account
        $account->retrieve($account->id);
        $this->assertEquals("1", $account->description);

        //Try creating a contact and add it from the account side
        $contact2 = new Contact();
        $contact2->name = "CalcFieldTestContact2";
        $contact2->save();
        $this->createdBeans[] = $contact2;

        $account->load_relationship("contacts");
        $account->contacts->add($contact2->id);
        $account->save();

        $this->assertEquals("2", $account->description);

        //Try creating a contact and add it from the contact side
        $contact3 = new Contact();
        $contact3->name = "CalcFieldTestContact3";
        $contact3->save();
        $this->createdBeans[] = $contact3;

        $contact3->load_relationship("accounts");
        $contact3->accounts->add($account->id);

        $contact3->save();

        $account->retrieve($account->id);
        $this->assertEquals("3", $account->description);


        //Try removing a contact from the contact side
        $contact3->accounts->delete($contact3->id, $account->id);
        $contact3->save();

        $account->retrieve($account->id);
        $this->assertEquals("2", $account->description);

        //Try removing a contact from the account side
        $account->load_relationship("contacts");
        $account->contacts->delete($account->id, $contact2->id);
        $account->retrieve($account->id);
        $this->assertEquals("1", $account->description);
    }
}