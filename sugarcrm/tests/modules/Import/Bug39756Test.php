<?php
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

require_once('modules/Accounts/Account.php');

class Bug39756Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $_account = null;

    public function setUp() 
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->_account = new Account();
        $this->_account->name = 'Account_'.create_guid();
        $this->_account->save();

    }
    
    public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        $sql = "DELETE FROM accounts where id = '{$this->_account->id}'";
        $GLOBALS['db']->query($sql);
    }
    
    public function testUpdateDateEnteredWithValue()
    {
        global $disable_date_format;
        $disable_date_format = true;

       $newDateEntered = '2011-01-28 11:05:10';
       $oldDateEntered = $this->_account->date_entered;

       $this->_account->update_date_entered = true;
       $this->_account->date_entered = $newDateEntered;
       $this->_account->save();

       $acct = new Account();
       $acct->retrieve($this->_account->id);
       
       $this->assertNotEquals($acct->date_entered, $oldDateEntered, "Account date_entered should not be equal to old date_entered");
       $this->assertEquals($acct->date_entered, $newDateEntered, "Account date_entered should be equal to old date_entered");
    }

    public function testNoUpdateDateEnteredWithValue()
    {
        global $disable_date_format;
        $disable_date_format = true;

       $newDateEntered = '2011-01-28 11:05:10';
       $oldDateEntered = $this->_account->date_entered;

       $this->_account->date_entered = $newDateEntered;
       $this->_account->save();

       $acct = new Account();
       $acct->retrieve($this->_account->id);
       
       $this->assertEquals($acct->date_entered, $oldDateEntered, "Account date_entered should be equal to old date_entered");
       $this->assertNotEquals($acct->date_entered, $newDateEntered, "Account date_entered should not be equal to old date_entered");
    }
}
