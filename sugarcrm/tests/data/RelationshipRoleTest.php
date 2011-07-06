<?php
// FILE SUGARCRM flav=pro ONLY
/********************************************************************************
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

require_once("data/BeanFactory.php");
class RelationshipRoleTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $createdBeans = array();
    protected $createdFiles = array();

    public function setUp()
	{

	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->setPreference('timezone', "America/Los_Angeles");
	    $GLOBALS['current_user']->setPreference('datef', "m/d/Y");
		$GLOBALS['current_user']->setPreference('timef', "h.iA");
	}

	public function tearDown()
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
	}
	

    /**
     * Create a new account and bug, then link them.
     * @return void
     */
	public function testQuoteAccountsRole()
	{
        require('include/modules.php');
	    $account = BeanFactory::newBean("Accounts");
        $account->name = "RoleTestAccount";
        $account->save();
        $this->createdBeans[] = $account;

        $quote = BeanFactory::newBean("Quotes");
        $quote->name = "RoleTestQuote";
        $quote->save();
        $this->createdBeans[] = $quote;

        $quote->load_relationship("billing_accounts");
        $quote->billing_accounts->add($account);

        //Now check the row in the database
        $quote->set_account();
        $this->assertEquals($account->name, $quote->billing_account_name);
    }
}