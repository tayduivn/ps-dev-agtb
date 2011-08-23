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

require_once 'tests/service/SOAPTestCase.php';


/**
 * @group bug44280
 */
class Bug44280Test extends SOAPTestCase
{
    public $accnt1;
    public $accnt2;
    public $cont1;
    public $cont2;

	public function setUp()
    {
    	$this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v2/soap.php';
        parent::setUp();
    }

    /**
     * Remove anything that was used during this test
     *
     */
    public function tearDown() {
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->cont1->id}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->cont2->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id= '{$this->cont1->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id= '{$this->cont2->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$this->accnt1->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$this->accnt2->id}'");

        unset($this->accnt1); unset($this->accnt2);
        unset($this->cont1); unset($this->cont2);
        parent::tearDown();
    }

    public function createAccount($name,$user_id) {
        $account = new Account();
		$account->id = uniqid();
        $account->name = $name;
        $account->assigned_user_id = $user_id;
        $account->new_with_id = true;
        $account->disable_custom_fields = true;
        $account->save();
        $GLOBALS['db']->commit();
        return $account;
    }

    public function createContact($first_name, $last_name, $email){
        $contact = new Contact();
		$contact->id = uniqid();
        $contact->first_name = $first_name;
        $contact->last_name = $last_name;
        $contact->email1 = $email;
        $contact->new_with_id = true;
        $contact->disable_custom_fields = true;
        $contact->save();
        $GLOBALS['db']->commit();
        return $contact;
    }

    public function testSetEntries() {
    	$this->_login();

        // first create two accounts with identical account names
        $this->accnt1 = $this->createAccount("sugar_account_name","sugarUser1");
        $this->accnt2 = $this->createAccount("sugar_account_name","sugarUser2");

        // now creating two contacts and relate them to the above accounts

        $this->cont1 = $this->createContact("first1", "last1", "adsf@asdf.com");
        $this->cont2 = $this->createContact("first2", "last2", "adsf@asdf.com");

         // this will be used in set_entries call
        $accounts_list=array( 'session'=>$this->_sessionId, 'module_name' => 'Accounts',
				   'name_value_lists' => array(
                                        array(
                                           array('name'=>'id','value'=>$this->accnt1->id),
                                           array('name'=>'first_name','value'=>$this->accnt1->name),
                                           array('name'=>'account_id','value'=>$this->accnt1->id),
                                           array('name'=>'team_id','value'=>'1'),
                                           array('name'=>'soap_dts_c','value'=>'2011-06-02 17:37:49'),
                                           array('name'=>'contactid_4d_c','value'=>'123456'),
                                           array('name'=>'phone_work','value'=>'1234567890'),
                                           array('name'=>'title','value'=>''),
                                       ),
                                        array(
                                           array('name'=>'id','value'=>$this->accnt2->id),
                                           array('name'=>'first_name','value'=>$this->accnt2->name),
                                           array('name'=>'account_id','value'=>$this->accnt2->id),
                                           array('name'=>'team_id','value'=>'1'),
                                           array('name'=>'soap_dts_c','value'=>'2011-06-02 16:37:49'),
                                           array('name'=>'contactid_4d_c','value'=>'999991'),
                                           array('name'=>'phone_work','value'=>'987654321'),
                                           array('name'=>'title','value'=>''),
                                       )
                                        )
                                       );
        // add the accounts
         $result = $this->_soapClient->call('set_entries', $accounts_list);

        // add the contacts & set the relationship to account
        $contacts_list = array( 'session'=>$this->_sessionId, 'module_name' => 'Contacts',
				   'name_value_lists' => array(
                                        array(
                                           array('name'=>'last_name','value'=>$this->cont1->last_name),
                                           array('name'=>'email','value'=>$this->cont1->email1),
                                           array('name'=>'first_name','value'=>$this->cont1->first_name),
                                           array('name'=>'id','value'=>$this->cont1->id),
                                           array('name'=>'account_name','value'=>$this->accnt1->name),
                                           array('name'=>'account_id','value'=>$this->accnt1->id),


                                       ),
                                        array(
                                            array('name'=>'last_name','value'=>$this->cont2->last_name),
                                            array('name'=>'email','value'=>$this->cont2->email1),
                                            array('name'=>'first_name','value'=>$this->cont2->first_name),
                                            array('name'=>'id','value'=>$this->cont2->id),
                                            array('name'=>'account_name','value'=>$this->accnt2->name),
                                            array('name'=>'account_id','value'=>$this->accnt2->id),

                                       )
                                        )
                                       );


        $result2 = $this->_soapClient->call('set_entries', $contacts_list);

         // lets check first relationship
        $query1 = "SELECT account_id FROM accounts_contacts WHERE contact_id='{$this->cont1->id}'";
        $cont1_account_result = $GLOBALS['db']->query($query1,true,"");
        $row1 = $GLOBALS['db']->fetchByAssoc($cont1_account_result);
        if(isset($row1) ){

            $this->assertEquals($this->accnt1->id, $row1["account_id"], "check first account-contact relationship");

          }


          // lets check second relationship
        $query2 = "SELECT account_id FROM accounts_contacts WHERE contact_id='{$this->cont2->id}'";
        $cont2_account_result = $GLOBALS['db']->query($query2,true,"");
        $row2 = $GLOBALS['db']->fetchByAssoc($cont2_account_result);
        if(isset($row2) ){

            $this->assertEquals($this->accnt2->id, $row2["account_id"], "check second account-contact relationship");

          }


    }


    /**
     * Attempt to login to the soap server
     *
     * @return $set_entry_result - this should contain an id and error.  The id corresponds
     * to the session_id.
     */
    public function _login(){
		global $current_user;
    	$result = $this->_soapClient->call('login',
            array('user_auth' =>
                array('user_name' => $current_user->user_name,
                    'password' => $current_user->user_hash,
                    'version' => '1.0'),
                'application_name' => 'SoapTest')
            );
         $this->_sessionId = $result['id'];
		return $result;
    }



}
?>