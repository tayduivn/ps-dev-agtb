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

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('Reports'));
		parent::setUp();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testGetReportEntries()
    {
    	require_once('service/core/SoapHelperWebService.php');
    	require_once('modules/Reports/Report.php');
    	require_once('modules/Reports/SavedReport.php');
    	//$savedReportId = $GLOBALS['db']->getOne("SELECT id FROM saved_reports WHERE deleted=0");

        $results = $GLOBALS['db']->query("SELECT id FROM saved_reports WHERE deleted=0");
        while(($row = $GLOBALS['db']->fetchByAssoc($results)) != null)
        {
            $savedReportId = $row['id'];
            $savedReport = new SavedReport();
            $savedReport->retrieve($savedReportId);
            $helperObject = new SoapHelperWebServices();
            $helperResult = $helperObject->get_report_value($savedReport, array());
            $this->_login();
            $result = $this->_soapClient->call('get_report_entries',array('session'=>$this->_sessionId,'ids' => array($savedReportId),'select_fields' => array()));

            $this->assertTrue(!empty($result['field_list']), "Bad result: ".var_export($result, true));
            $this->assertTrue(!empty($result['entry_list']), "Bad result: ".var_export($result, true));
        }
    } // fn
}
