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
 
require_once 'include/MassUpdate.php';
require_once 'SugarTestAccountUtilities.php';

/**
 * Bug40597
 * This unit tests will check to see the results of the MassUpdate getMassUpdateForm function when there are no fields
 * availble for the Mass Update operation.  To simulate this we just set the bean's field_defs variable to an empty Array.
 * We are checking to see that HTML is returned where a div element contains the "massupdate_form" id.
 * 
 * @author clee
 *
 */
class Bug40597 extends Sugar_PHPUnit_Framework_TestCase
{
	var $testAccount;
	
	public function setUp()
	{
		global $app_strings, $app_list_strings;
		global $current_language;
		$current_language = 'en_us';
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
		$app_strings = return_application_language($GLOBALS['current_language']);
		$app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
		$this->testAccount = SugarTestAccountUtilities::createAccount();
		$this->testAccount->field_defs = array();
		$this->useOutputBuffering = false;

	}
	
	public function tearDown()
	{
        SugarTestAccountUtilities::removeAllCreatedAccounts();		
	}
	
	public function testDisplayMassUpdateFormWithNoMassUpdateFields()
	{
		$mass = new MassUpdate();
		$mass->sugarbean = $this->testAccount;
		$result = $mass->getMassUpdateForm(false);
		$this->assertRegExp('/\s+id\s*?=\s*?[\"|\']massupdate_form[\"\']/', $result, "Assert we have a div element with massupdate_form id set");

		//We still get a form element even if  the delete operation was allowed
		$result = $mass->getMassUpdateForm(true);
		$this->assertRegExp('/\s+id\s*?=\s*?[\"|\']massupdate_form[\"\']/', $result, "Assert we have a div element with massupdate_form id set");
	}
	
}