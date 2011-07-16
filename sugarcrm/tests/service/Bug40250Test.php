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
 
require_once('include/nusoap/nusoap.php');
require_once('modules/Users/User.php');


/**
 * @group bug40250
 */
class Bug40250Test extends Sugar_PHPUnit_Framework_TestCase
{
	public $_user = null;
	public $_soapClient = null;
	public $_session = null;
	public $_sessionId = '';
    /**
     * Create test user
     *
     */
	public function setUp() 
    {
    	
        $this->_setupTestUser();
		$this->_soapClient = new nusoapclient($GLOBALS['sugar_config']['site_url'].'/soap.php',false,false,false,false,false,60,60);
    	$this->_login();
    }

    /**
     * Remove anything that was used during this test
     *
     */
    public function tearDown() {
    	global $soap_version_test_accountId, $soap_version_test_opportunityId, $soap_version_test_contactId;
        $this->_tearDownTestUser();
        $this->_user = null;
        $this->_sessionId = '';
    }	
    
    public function testRetrieveUsersList() {
    	//First retrieve the users count (should be at least 1)
        // 20110707 Frank Steegmans: DB2 by default is case sensitive. Note http://www.db2ude.com/?q=node/79
		$countArr  = $this->_soapClient->call('get_entries_count',array('session'=>$this->_sessionId,'module_name'=>'Users','query'=>" users.status = 'Active' ",0));
    	$count = $countArr['result_count'];
    	$this->assertGreaterThanOrEqual(1, $count, 'no users were retrieved so the test user was not set up correctly');
    	
		//now retrieve the list of users
    	$usersArr =   $this->_soapClient->call('get_entry_list',array('session'=>$this->_sessionId,'module_name'=>'Users','query'=>" users.status = 'Active' ", 'user_name','0'  ,'select_field'=>array('user_name'),10000,0));
    	$usersCount = $usersArr['result_count'];
    	
    	//the count from both functions should be the same
    	$this->assertEquals($count, $usersCount ,'count is not the same which means that the 2 calls are generating different results.');
    	
		//logout
    	$result =  $this->_soapClient->call('logout',array('session' => $this->_sessionId));
    }

	/**********************************
     * HELPER PUBLIC FUNCTIONS
     **********************************/
    
    /**
     * Attempt to login to the soap server
     *
     * @return $set_entry_result - this should contain an id and error.  The id corresponds
     * to the session_id.
     */
    public function _login(){
		global $current_user;  	
        $GLOBALS['db']->commit(); // Making sure we commit any changes before logging in
    	$result = $this->_soapClient->call('login',
            array('user_auth' => 
                array('user_name' => $this->_user->user_name,
                    'password' => $this->_user->user_hash, 
                    'version' => '.01'), 
                'application_name' => 'SoapTest')
            );
        $this->_sessionId = $result['id'];
		return $result;
		
    }
    
 /**
     * Create a test user
     *
     */
	public function _setupTestUser() {
        $this->_user = SugarTestUserUtilities::createAnonymousUser();
        $this->_user->status = 'Active';
        $this->_user->is_admin = 1;
        $this->_user->save();
    }
    

        
    /**
     * Remove user created for test
     *
     */
	public function _tearDownTestUser() {
       SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
	
}
?>