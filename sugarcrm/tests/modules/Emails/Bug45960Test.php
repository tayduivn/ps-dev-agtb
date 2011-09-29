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

 
require_once('modules/Emails/Email.php');

class Bug45960 extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->_user;
        $this->_account = SugarTestAccountUtilities::createAccount();
    }
    
    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testSaveNewEmailWithParent()
    {
        $email = new Email();
        $email->type = 'out';
        $email->status = 'sent';
        $email->from_addr_name = $email->cleanEmails("sender@domain.eu");
        $email->to_addrs_names = $email->cleanEmails("to@domain.eu");
        $email->cc_addrs_names = $email->cleanEmails("cc@domain.eu");

        // set a few parent info to test the scenario
        $email->parent_type = 'Accounts';
        $email->parent_id = $this->_account->id;
        $email->fetched_row['parent_type'] = 'Accounts';
        $email->fetched_row['parent_id'] = $this->_account->id;

        $email->save();

        // ensure record is inserted into emails_beans table
        $query = "select count(*) as CNT from emails_beans eb WHERE eb.bean_id = '{$this->_account->id}' AND eb.bean_module = 'Accounts' AND eb.email_id = '{$email->id}' AND eb.deleted=0";
        $result = $GLOBALS['db']->query($query);
        $count = $GLOBALS['db']->fetchByAssoc($result);
        $this->assertEquals(1, $count['CNT'], 'Incorrect emails_beans count');
    }
    
}


