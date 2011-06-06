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
 
require_once 'include/vCard.php';

class vCardBug40629Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $account;
    
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->account->name = "SDizzle Inc";
        $this->account->save();
    }
    
    public function tearDown()
    {
        unset($GLOBALS['current_user']);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }
    
    /**
     * @group bug40629
     */
	public function testImportedVcardAccountLink()
    {
        $filename  = dirname(__FILE__)."/SimpleVCard.vcf";
        
        $vcard = new vCard();
        $contact_id = $vcard->importVCard($filename,'Contacts');
        $contact_record = new Contact();
        $contact_record->retrieve($contact_id);
        
        $this->assertFalse(empty($contact_record->account_id), "Contact should have an account record associated");
        $GLOBALS['db']->query("delete from contacts where id = '{$contact_id}'");
        
        $vcard = new vCard();
        $lead_id = $vcard->importVCard($filename,'Leads');
        $lead_record = new Lead();
        $lead_record->retrieve($lead_id);
        
        $this->assertTrue(empty($lead_record->account_id), "Lead should not have an account record associated");
        $GLOBALS['db']->query("delete from leads where id = '{$lead_id}'");
    }
}