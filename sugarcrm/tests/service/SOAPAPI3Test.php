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

require_once('service/v3/SugarWebServiceUtilv3.php');
require_once('tests/service/APIv3Helper.php');
require_once 'tests/service/SOAPTestCase.php';
/**
 * This class is meant to test everything SOAP
 *
 */
class SOAPAPI3Test extends SOAPTestCase
{
    public $_contactId = '';
    private static $helperObject;

    /**
     * Create test user
     *
     */
	public function setUp()
    {
    	$this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v3/soap.php';
    	parent::setUp();
    	$this->_login();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        self::$helperObject = new APIv3Helper();
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM accounts WHERE name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE name like 'UNIT TEST%' ");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name like 'UNIT TEST%' ");
        unset($GLOBALS['reload_vardefs']);
        parent::tearDown();
    }

	/**
	 * Ensure we can create a session on the server.
	 *
	 */
    public function testCanLogin(){
		$result = $this->_login();
    	$this->assertTrue(!empty($result['id']) && $result['id'] != -1,
            'SOAP Session not created. Error ('.$this->_soapClient->faultcode.'): '.$this->_soapClient->faultstring.': '.$this->_soapClient->faultdetail);
    }

    public function testSearchByModule()
    {
        $seedData = self::$helperObject->populateSeedDataForSearchTest($GLOBALS['current_user']->id);

        $searchModules = array('Accounts','Contacts','Opportunities');
        $searchString = "UNIT TEST";
        $offSet = 0;
        $maxResults = 10;

        $results = $this->_soapClient->call('search_by_module',
                        array(
                            'session' => $this->_sessionId,
                            'search'  => $searchString,
                            'modules' => $searchModules,
                            'offset'  => $offSet,
                            'max'     => $maxResults,
                            'user'    => $GLOBALS['current_user']->id)
                        );

        $this->assertTrue( self::$helperObject->findBeanIdFromEntryList($results['entry_list'],$seedData[0]['id'],'Accounts') );
        $this->assertFalse( self::$helperObject->findBeanIdFromEntryList($results['entry_list'],$seedData[1]['id'],'Accounts') );
        $this->assertTrue( self::$helperObject->findBeanIdFromEntryList($results['entry_list'],$seedData[2]['id'],'Contacts') );
        $this->assertTrue( self::$helperObject->findBeanIdFromEntryList($results['entry_list'],$seedData[3]['id'],'Opportunities') );
        $this->assertFalse( self::$helperObject->findBeanIdFromEntryList($results['entry_list'],$seedData[4]['id'],'Opportunities') );
    }

    public function testSearchByModuleWithReturnFields()
    {
        $seedData = self::$helperObject->populateSeedDataForSearchTest($GLOBALS['current_user']->id);

        $returnFields = array('name','id','deleted');
        $searchModules = array('Accounts','Contacts','Opportunities');
        $searchString = "UNIT TEST";
        $offSet = 0;
        $maxResults = 10;

        $results = $this->_soapClient->call('search_by_module',
                        array(
                            'session' => $this->_sessionId,
                            'search'  => $searchString,
                            'modules' => $searchModules,
                            'offset'  => $offSet,
                            'max'     => $maxResults,
                            'user'    => $GLOBALS['current_user']->id,
                            'fields'  => $returnFields)
                        );

        $this->assertEquals($seedData[0]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[0]['id'],'Accounts', $seedData[0]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[1]['id'],'Accounts', $seedData[1]['fieldName']));
        $this->assertEquals($seedData[2]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[2]['id'],'Contacts', $seedData[2]['fieldName']));
        $this->assertEquals($seedData[3]['fieldValue'], self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[3]['id'],'Opportunities', $seedData[3]['fieldName']));
        $this->assertFalse(self::$helperObject->findFieldByNameFromEntryList($results['entry_list'],$seedData[4]['id'],'Opportunities', $seedData[4]['fieldName']));
    }

    public function testGetVardefsMD5()
    {
        $GLOBALS['reload_vardefs'] = TRUE;
        //Test a regular module
        $result = $this->_getVardefsMD5('Accounts');
        $a = new Account();
        $soapHelper = new SugarWebServiceUtilv3();
        $actualVardef = $soapHelper->get_return_module_fields($a,'Accounts','');
        $actualMD5 = md5(serialize($actualVardef));
        $this->assertEquals($actualMD5, $result[0], "Unable to retrieve vardef md5.");

        //Test a fake module
        $result = $this->_getVardefsMD5('BadModule');
        $this->assertTrue($result['faultstring'] == 'Module Does Not Exist');
    }

    public function testGetUpcomingActivities()
    {
         $expected = $this->_createUpcomingActivities(); //Seed the data.
         $results = $this->_soapClient->call('get_upcoming_activities',array('session'=>$this->_sessionId));
         $this->_removeUpcomingActivities();

         $this->assertEquals($expected[0] ,$results[0]['id'] , 'Unable to get upcoming activities Error ('.$this->_soapClient->faultcode.'): '.$this->_soapClient->faultstring.': '.$this->_soapClient->faultdetail);
         $this->assertEquals($expected[1] ,$results[1]['id'] , 'Unable to get upcoming activities Error ('.$this->_soapClient->faultcode.'): '.$this->_soapClient->faultstring.': '.$this->_soapClient->faultdetail);

    }

    public function testSetEntriesForAccount()
    {
    	$result = $this->_setEntriesForAccount();
    	$this->assertTrue(!empty($result['ids']) && $result['ids'][0] != -1,
            'Can not create new account using testSetEntriesForAccount. Error ('.$this->_soapClient->faultcode.'): '.$this->_soapClient->faultstring.': '.$this->_soapClient->faultdetail);
    } // fn

    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * @depends testSetEntriesForAccount
     */
    public function testGetLastViewed()
    {
         $testModule = 'Accounts';
         $testModuleID = create_guid();

         $this->_createTrackerEntry($testModule,$testModuleID);

		 $results = $this->_soapClient->call('get_last_viewed',array('session'=>$this->_sessionId,'module_names'=> array($testModule) ));

		 $found = FALSE;
         foreach ($results as $entry)
         {
             if($entry['item_id'] == $testModuleID)
             {
                 $found = TRUE;
                 break;
             }
         }

         $this->assertTrue($found, "Unable to get last viewed modules");
     }

     private function _createTrackerEntry($module, $id,$summaryText = "UNIT TEST SUMMARY")
     {
        $trackerManager = TrackerManager::getInstance();
        $timeStamp = TimeDate::getInstance()->nowDb();
        $monitor = $trackerManager->getMonitor('tracker');
        //BEGIN SUGARCRM flav=pro ONLY
        $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
        //END SUGARCRM flav=pro ONLY
        $monitor->setValue('action', 'detail');
        $monitor->setValue('user_id', $GLOBALS['current_user']->id);
        $monitor->setValue('module_name', $module);
        $monitor->setValue('date_modified', $timeStamp);
        $monitor->setValue('visible', true);
        $monitor->setValue('item_id', $id);
        $monitor->setValue('item_summary', $summaryText);
        $trackerManager->saveMonitor($monitor, true, true);
        $GLOBALS['db']->commit();
     }
     //END SUGARCRM flav=pro ONLY

    /**
     * Get Module Layout functions not exposed to soap service, make sure they are not available.
     *
     */
    public function testGetModuleLayoutMD5()
    {
        $result = $this->_getModuleLayoutMD5();
        $this->assertContains('Client',$result['faultcode']);
    }


    /**********************************
     * HELPER PUBLIC FUNCTIONS
     **********************************/
    private function _removeUpcomingActivities()
    {
        $GLOBALS['db']->query("DELETE FROM calls where name = 'UNIT TEST'");
        $GLOBALS['db']->query("DELETE FROM tasks where name = 'UNIT TEST'");
    }

    private function _createUpcomingActivities()
    {
        $GLOBALS['current_user']->setPreference('datef','Y-m-d') ;
        $GLOBALS['current_user']->setPreference('timef','H:i') ;
        global $timedate;
        $date1 = $timedate->asUser($timedate->getNow()->modify("+2 days"));
        $date2 = $timedate->asUser($timedate->getNow()->modify("+4 days"));

        $callID = uniqid();
        $c = new Call();
        $c->id = $callID;
        $c->new_with_id = TRUE;
        $c->status = 'Not Planned';
        $c->date_start = $date1;
        $c->name = "UNIT TEST";
        $c->assigned_user_id = $GLOBALS['current_user']->id;
        $c->save(FALSE);

        $callID = uniqid();
        $c = new Call();
        $c->id = $callID;
        $c->new_with_id = TRUE;
        $c->status = 'Planned';
        $c->date_start = $date1;
        $c->name = "UNIT TEST";
        $c->assigned_user_id = $GLOBALS['current_user']->id;
        $c->save(FALSE);

        $taskID = uniqid();
        $t = new Task();
        $t->id = $taskID;
        $t->new_with_id = TRUE;
        $t->status = 'Not Started';
        $t->date_due = $date2;
        $t->name = "UNIT TEST";
        $t->assigned_user_id = $GLOBALS['current_user']->id;
        $t->save(FALSE);
        $GLOBALS['db']->commit();

        return array($callID, $taskID);
    }

    public function _getVardefsMD5($module)
    {
		$result = $this->_soapClient->call('get_module_fields_md5',array('session'=>$this->_sessionId,'module'=> $module ));
		return $result;
    }

    public function _getModuleLayoutMD5()
    {
		$result = $this->_soapClient->call('get_module_layout_md5',
		              array('session'=>$this->_sessionId,'module_names'=> array('Accounts'),'types' => array('default'),'views' => array('list')));
		return $result;
    }

    public function _setEntriesForAccount() {
		global $timedate;
		$current_date = $timedate->nowDb();
        $time = mt_rand();
    	$name = 'SugarAccount' . $time;
        $email1 = 'account@'. $time. 'sugar.com';
		$result = $this->_soapClient->call('set_entries',array('session'=>$this->_sessionId,'module_name'=>'Accounts', 'name_value_lists'=>array(array(array('name'=>'name' , 'value'=>"$name"), array('name'=>'email1' , 'value'=>"$email1")))));
		$soap_version_test_accountId = $result['ids'][0];
		SugarTestAccountUtilities::setCreatedAccount(array($soap_version_test_accountId));
		return $result;
    } // fn

}
