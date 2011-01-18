<?php
//FILE SUGARCRM flav=pro ONLY
require_once('include/nusoap/nusoap.php');
require_once 'tests/service/SOAPTestCase.php';

/**
 * @ticket 38100
 */
class Bug38100Test extends SOAPTestCase
{
    public $_contactId = '';

    /**
     * Create test user
     *
     */
	public function setUp()
    {
    	$this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/service/v2_1/soap.php';

		$beanList = array();
		$beanFiles = array();
		require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;

		$GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

		parent::setUp();
    }

    public function tearDown()
    {
		unset($GLOBALS['beanList']);
		unset($GLOBALS['beanFiles']);
		unset($GLOBALS['app_list_strings']);
    }

    public function testGetReportEntries()
    {
    	require_once('service/core/SoapHelperWebService.php');
    	require_once('modules/Reports/Report.php');
    	require_once('modules/Reports/SavedReport.php');
    	$savedReportId = $GLOBALS['db']->getOne("SELECT id FROM saved_reports WHERE deleted=0");
    	if(!$savedReportId) {
    	    $this->markTestSkipped("No live reports!");
    	}
    	$savedReport = new SavedReport();
    	$savedReport->retrieve($savedReportId);
    	$helperObject = new SoapHelperWebServices();
    	$helperResult = $helperObject->get_report_value($savedReport, array());
    	$this->_login();
		$result = $this->_soapClient->call('get_report_entries',array('session'=>$this->_sessionId,'ids' => array($savedReportId),'select_fields' => array()));

		$this->assertTrue(!empty($result['field_list']));
		$this->assertTrue(!empty($result['entry_list']));
    } // fn
}
