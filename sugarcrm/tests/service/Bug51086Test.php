<?php
//FILE SUGARCRM flav=pro ONLY
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


require_once('include/nusoap/nusoap.php');
require_once 'tests/service/SOAPTestCase.php';

/*
 * This test makes sure that field level acl's are honored in soap v4
 * @ticket 51086
 */

class Bug51086Test extends SOAPTestCase
{
    private $_aclRole;
    private $_aclField;
    private $_contact;
    private $_blockedfield = 'description';
    private $_nonAdminUser;

    /**
     * Create test user
     *
     */
	public function setUp()
    {

        //set up non admin user, do not use default user created by class which is an admin
        $this->_nonAdminUser = SugarTestUserUtilities::createAnonymousUser();
        $this->_nonAdminUser->status = 'Active';
        $this->_nonAdminUser->is_admin = 0;
        $this->_nonAdminUser->save();
        $GLOBALS['db']->commit();
        $GLOBALS['current_user'] = $this->_nonAdminUser;



    	$this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v4/soap.php';
		parent::setUp();
        $this->_login($this->_nonAdminUser);


        $beanList = array();
		$beanFiles = array();
		require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;

        //Reload langauge strings
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Contacts');

        //create Contact
   	    $this->_contact = new Contact();
        $this->_contact->first_name = 'Joe ';
        $this->_contact->last_name = 'UT51086 ';
        $this->_contact->email1 = 'ut51086Contact@example.com';
        $this->_contact->save();
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing

        //Create new ACL role
        $this->_aclRole = new ACLRole();
        $this->_aclRole->name = "Unit Test";
        $this->_aclRole->save();
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing

        //relate the acl role to the new user
        $this->_aclRole->set_relationship('acl_roles_users', array('role_id'=>$this->_aclRole->id ,'user_id'=> $GLOBALS['current_user']->id), false);
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing

        //Disable access to the blocked field on contacts bean.
        $this->_aclField = new ACLField();
        $this->_aclField->setAccessControl('Contacts', $this->_aclRole->id, $this->_blockedfield, -99);
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing
        ACLField::loadUserFields('Contacts', 'Contact', $GLOBALS['current_user']->id, true );
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing
    }

    public function tearDown()
    {

        $GLOBALS['db']->query("DELETE FROM acl_roles WHERE id = '".$this->_aclRole->id."' ");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '".$this->_contact->id."' ");
        $GLOBALS['db']->commit();

        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['current_user']);
        unset($this->_aclField);
        unset($this->_aclRole);
        unset($this->_contact);
        unset($this->_blockedfield);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        parent::tearDown();
    }

    //overwrite class login function because we need the user to be non admin for this test to work, otherwise acls will be ignored
    function _login($thisUser)
   {
       $GLOBALS['db']->commit();
       $result = $this->_soapClient->call('login',
           array('user_auth' =>
               array('user_name' => $thisUser->user_name,
                   'password' => $thisUser->user_hash,
                   'version' => '.01'),
               'application_name' => 'SoapTest', "name_value_list" => array())
           );
       $this->_sessionId = $result['id'];
       return $result;
   }


    //test that the soap call will honor field level acl with a passed in list of selected fields
    public function testFieldLevelACLWithDefinedSelect()
    {

        //make the soap call that will return the contact record with the selected fields.
        //Assert that blocked field is declared to be returned
        $selFields =  array('last_name'=>'last_name', 'first_name'=>'first_name', 'email1'=>'email1','id'=>'id',$this->_blockedfield=>$this->_blockedfield);
        $this->assertContains($this->_blockedfield, $selFields[$this->_blockedfield], 'array of selected fields does not contain blocked field ('.$this->_blockedfield.'), this test is no longer valid');

        //make soap call
        $result = $this->_soapClient->call(
            'get_entry_list',
            array(
                'session' => $this->_sessionId,
                'module_name' => 'Contacts',
                'query' => "contacts.id = '{$this->_contact->id}'",
                'order_by' => '',
                'offset' => 0,
                'select_fields' =>$selFields,
                'link_name_to_fields_array' => array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address'))),
                'max_results' => 20,
                'deleted' => 0,
                'favorites' => false,
                )
            );

        //assert that results were returned and grab the list of returned fields
        $this->assertNotEmpty($result['entry_list'], 'get_entry_list soap call failed, results were not returned as expected');
        $fields_returned = $result['entry_list'][0]['name_value_list'];

        //iterate through array and make sure description is not a returned field
        $foundDescription = false;
        foreach( $fields_returned as $name_val_array){
            if($this->_blockedfield == $name_val_array['name']){
                $foundDescription = true;
            }

        }
        //assert returned field is not description
        $this->assertFalse($foundDescription, 'the blocked field ('.$this->_blockedfield.') was returned with select fields specified, despite being off limits through ACLs and user not being admin.');
    }

    //Same as previous test, only without a passed in list of selected fields, meaning that all fields are returned
    public function testFieldLevelACLWithOutDefinedSelect()
    {

        //make the soap call that will return the contact record with all fields (no selected fields defined).
        $result = $this->_soapClient->call(
            'get_entry_list',
            array(
                'session' => $this->_sessionId,
                'module_name' => 'Contacts',
                'query' => "contacts.id = '{$this->_contact->id}'",
                'order_by' => '',
                'offset' => 0,
                'select_fields' =>array(),
                'link_name_to_fields_array' => array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address'))),
                'max_results' => 20,
                'deleted' => 0,
                'favorites' => false,
                )
            );

        //assert that results were returned and grab the list of returned fields
        $this->assertNotEmpty($result['entry_list'], 'get_entry_list soap call failed, results were not returned as expected');
        $fields_returned = $result['entry_list'][0]['name_value_list'];

        //iterate through array and make sure description is not a returned field
        $foundDescription = false;
        foreach( $fields_returned as $name_val_array){
            if($this->_blockedfield == $name_val_array['name']){
                $foundDescription = true;
            }

        }
        //assert returned field is not description
        $this->assertFalse($foundDescription, 'the blocked field ('.$this->_blockedfield.') was returned when no select fields were specified despite being off limits through ACLs and user not being admin.');
    }


}
