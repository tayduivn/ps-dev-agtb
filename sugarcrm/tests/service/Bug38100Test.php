<?php 
//FILE SUGARCRM flav=pro ONLY
require_once('include/nusoap/nusoap.php');

/**
 * @group bug38100
 */
class Bug38100Test extends Sugar_PHPUnit_Framework_TestCase
{
	public $_user = null;
	public $_soapClient = null;
	public $_session = null;
	public $_sessionId = '';
    public $_contactId = '';
    var $c = null;
    var $a1 = null;
	
    /**
     * Create test user
     *
     */
	public function setUp() 
    {
        $this->markTestSkipped('Skip this soap test.');
    	$this->_soapClient = new nusoapclient($GLOBALS['sugar_config']['site_url'].'/service/v2_1/soap.php',false,false,false,false,false,600,600);
        $this->_setupTestUser();
        
        $unid = uniqid();
		$time = date('Y-m-d H:i:s');

		$contact = new Contact();
		$contact->id = 'c_'.$unid;
        $contact->first_name = 'testfirst';
        $contact->last_name = 'testlast';
        $contact->new_with_id = true;
        $contact->disable_custom_fields = true;
        $contact->save();
		$this->c = $contact;
		/*
		$account = new Account();
		$account->id = 'a_'.$unid;
        $account->name = 'acctfirst';
        $account->new_with_id = true;
        $account->disable_custom_fields = true;
        $account->save();
        $this->a1 = $account;
        
        $this->c->load_relationship('accounts');
      	$this->c->accounts->add($this->a1->id);
      	*/
     
		$beanList = array();
		$beanFiles = array();
		require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;
    	
		$GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
       
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
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->c->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id= '{$this->c->id}'");
       // $GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$this->a1->id}'");
        
        //unset($this->a);
        unset($soap_version_test_accountId);
        unset($soap_version_test_opportunityId);
        unset($soap_version_test_contactId);
    }	
    
    public function testGetReportEntries() {
    	require_once('service/core/SoapHelperWebService.php');
    	require_once('modules/Reports/Report.php');
    	require_once('modules/Reports/SavedReport.php');
    	$savedReportId = $GLOBALS['db']->getOne("SELECT id FROM saved_reports");
    	$savedReport = new SavedReport();
    	$savedReport->retrieve($savedReportId);
    	$helperObject = new SoapHelperWebServices();
    	$helperResult = $helperObject->get_report_value($savedReport, array());
    	$this->_login();
		$result = $this->_soapClient->call('get_report_entries',array('session'=>$this->_sessionId,'ids' => array($savedReportId),'select_fields' => array()));
		$this->assertTrue(!empty($result['field_list']));
		$this->assertTrue(!empty($result['entry_list']));
    } // fn
    
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
    	$result = $this->_soapClient->call('login',
            array('user_auth' => 
                array('user_name' => $current_user->user_name,
                    'password' => $current_user->user_hash, 
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
        $GLOBALS['current_user'] = $this->_user;
    }
        
    /**
     * Remove user created for test
     *
     */
	public function _tearDownTestUser() {
       SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
       unset($GLOBALS['current_user']);
    }
	
}
?>