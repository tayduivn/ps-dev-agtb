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
 
require_once('include/OutboundEmail/OutboundEmail.php');

/**
 * @ticket 23140
 */
class Bug36329Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
	var $save_query;
	var $current_language;

	public function setUp()
	{
		global $sugar_config;
		$this->save_query = isset($sugar_config['save_query']) ? true : false;
		$this->current_language = $GLOBALS['current_language'];

		global $current_user;
		$current_user = new User();
		$current_user->retrieve('1');

		global $mod_strings, $app_strings;
		$mod_strings = return_module_language('en_us', 'Accounts');
		$app_strings = return_application_language('en_us');

		$beanList = array();
		$beanFiles = array();
		require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;

		require('sugar_version.php');
		$GLOBALS['sugar_version'] = $sugar_version;
	}

	public function tearDown()
	{
	    global $sugar_config;
		if(!$this->save_query) {
		   unset($sugar_config['save_query']);
		}

		$GLOBALS['current_language'] = $this->current_language;
		unset($GLOBALS['mod_strings']);
		unset($GLOBALS['app_strings']);
		unset($GLOBALS['beanList']);
		unset($GLOBALS['beanFiles']);
	}

    public function test_populate_only_no_query()
    {
    	$GLOBALS['sugar_config']['save_query'] = 'populate_only';
    	$_REQUEST['module'] = 'Accounts';
    	$_REQUEST['action'] = 'Popup';
    	$_REQUEST['mode'] = 'single';
    	$_REQUEST['create'] = 'true';
    	$_REQUEST['metadata'] = 'undefined';
    	require_once('include/MVC/View/SugarView.php');
    	require_once('include/MVC/View/views/view.popup.php');
    	require_once('include/utils/layout_utils.php');
    	$popup = new ViewPopup();
    	$popup->module = 'Accounts';
    	require_once('modules/Accounts/Account.php');
    	$popup->bean = new account();
    	$this->expectOutputRegex("/Perform a search using the search form above/");
    	$popup->display();
    }


    public function test_populate_only_with_query()
    {
    	$GLOBALS['sugar_config']['save_query'] = 'populate_only';
    	global $app_strings;
    	$_REQUEST['module'] = 'Accounts';
    	$_REQUEST['action'] = 'Popup';
    	$_REQUEST['mode'] = 'single';
    	$_REQUEST['create'] = 'true';
    	$_REQUEST['metadata'] = 'undefined';
    	$_REQUEST['name_advanced'] = 'Test';
    	$_REQUEST['query'] = 'true';
    	require_once('include/MVC/View/SugarView.php');
    	require_once('include/MVC/View/views/view.popup.php');
    	require_once('include/utils/layout_utils.php');
    	$popup = new ViewPopup();
    	$popup->module = 'Accounts';
    	require_once('modules/Accounts/Account.php');
    	$popup->bean = new account();
    	// Negative regexp
    	$this->expectOutputNotRegex('/Perform a search using the search form above/');
    	$popup->display();
    }
}
