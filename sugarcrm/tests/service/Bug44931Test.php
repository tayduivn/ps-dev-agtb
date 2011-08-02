<?php
//FILE SUGARCRM flav=ent ONLY 
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

/**
 * @group bug44931
 */
class Bug44931Test extends Sugar_PHPUnit_Framework_TestCase
{
	public $_soapClient = null;
	
	public function setUp() 
    {
        $this->_soapClient = new nusoapclient($GLOBALS['sugar_config']['site_url'].'/soap.php',false,false,false,false,false,600,600);
        
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->status = 'Active';
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->save();
    }

    public function tearDown() 
    {
        
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }	
    
    public function testGetEntryListForKBDocumentKBTagModule() 
    {
        
        $this->_login();
        
        $parameters = array(
            'session' => $this->_sessionId,
            'module_name' => 'KBDocumentKBTags',
            'query' => "kbdocuments_kbtags.deleted=0",
            'order_by' => '',
            'offset' => 0,
            'select_fields' => array('id'),
            'max_results' => 250,
            'deleted' => 0,
            );
            
        $result = $this->_soapClient->call('get_entry_list',$parameters);

        $this->assertNotEmpty($result['field_list'][0]['name']);
       
    }
    
    /**
     * Attempt to login to the soap server
     *
     * @return $set_entry_result - this should contain an id and error.  The id corresponds
     * to the session_id.
     */
    public function _login()
    {
		global $current_user;  	
    	
		$result = $this->_soapClient->call(
		    'login',
            array('user_auth' => 
                array('user_name' => $current_user->user_name,
                    'password' => $current_user->user_hash, 
                    'version' => '.01'), 
                'application_name' => 'SoapTest')
            );
        $this->_sessionId = $result['id'];
		
        return $result;
    }
}