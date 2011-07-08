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


class RetrieveEmailFieldsTest extends SOAPTestCase
{
	public $_soapClient = null;
    var $_sessionId;
    var $acc;
	var $email_id;
	
	public function setUp() 
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->acc = SugarTestAccountUtilities::createAccount();
        parent::setUp();
    }

    public function tearDown() 
    {
        parent::tearDown();
    }

    public function testGetEmailAddressFields()
    {

        $this->_login();
       
        $result = $this->_soapClient->call('set_entry',array('session'=>$this->_sessionId,"module_name" => 'Emails', 'name_value_list' => array(array('name'=>'assigned_user_id' , 'value'=>$this->_user->id),array('name'=>'from_addr_name' , 'value'=>'test@test.com'),array('name'=>'parent_type' , 'value'=>'Accounts'),array('name'=>'parent_id' , 'value'=>$this->acc->id),array('name'=>'description' , 'value'=>"test"),array('name'=>'name' , 'value'=>"Test Subject"))));
		$this->email_id = $result['id'];
        
        $result = $this->_soapClient->call('get_entry_list',array('session'=> $this->_sessionId,'module_name'=>'Emails', 'query' => "emails.id='".$this->email_id."'", 'order_by' => '', 'offset' => 0, 'select_fields' => array('id', 'from_addr_name', 'to_addrs_names'),'max_results'=>10,'deleted'=>0));

        $this->assertEquals('from_addr_name', $result['entry_list'][0]['name_value_list'][1]['name']);
        $this->assertEquals('test@test.com', $result['entry_list'][0]['name_value_list'][1]['value']);
    }

}