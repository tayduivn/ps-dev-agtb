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


require_once('include/generic/SugarWidgets/SugarWidgetSubPanelTopButtonQuickCreate.php');
require_once('include/SubPanel/SubPanel.php');
require_once('include/SubPanel/SubPanelDefinitions.php');

class Bug44272Test extends PHPUnit_Framework_TestCase
{

var $account;
	
public function setUp()
{
    $beanList = array();
    $beanFiles = array();
    require('include/modules.php');
    $GLOBALS['beanList'] = $beanList;
    $GLOBALS['beanFiles'] = $beanFiles;
    
	$this->account = SugarTestAccountUtilities::createAccount();
}	

public function tearDown()
{
	SugarTestAccountUtilities::removeAllCreatedAccounts();
}
	
public function testSugarWidgetSubpanelTopButtonQuickCreate()
{
	$defines = array();
	$defines['focus'] = $this->account;
	$defines['module'] = 'Accounts';
	$defines['action'] = 'DetailView';

	$subpanel_definitions = new SubPanelDefinitions(new Contact());
	$contactSubpanelDef = $subpanel_definitions->load_subpanel('contacts');

	$subpanel = new SubPanel('Accounts', $this->account->id, 'contacts', $contactSubpanelDef, 'Accounts');
	$defines['subpanel_definition'] = $subpanel->subpanel_defs;
	
	$button = new SugarWidgetSubPanelTopButtonQuickCreate();
	$code = $button->_get_form($defines);
	$this->assertRegExp('/\<input[^\>]*?name=\"return_name\"/', $code, "Assert that the hidden input field return_name was created");
}
	
}
