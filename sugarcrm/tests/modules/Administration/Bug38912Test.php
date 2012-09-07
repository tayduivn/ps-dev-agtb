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

require_once 'modules/Administration/updater_utils.php';

class Bug38912 extends Sugar_PHPUnit_Framework_TestCase
{
	/**
	 * Test whitelist of modules and actions 
	 * @var array
	 */
	private $_whiteList;

	private $_state						= 'LICENSE_KEY';

	private $_whiteListModuleAllActions	= 'SomeWhiteListModuleAllActions';
	private $_whiteListModule			= 'SomeWhiteListModule';
	private $_whiteListAction			= 'SomeWhiteListAction';
	private $_nonWhiteListModule		= 'SomeNonWhiteListModule';
	private $_nonWhiteListAction		= 'SomeNonWhiteListAction';


	public function setUp()
	{
		// read format in function getModuleWhiteListForLicenseCheck() description
		$this->_whiteList		= array(
			$this->_whiteListModule				=> array($this->_whiteListAction),
			$this->_whiteListModuleAllActions	=> 'all'
		);
	}

    public function testUserNeedsRedirectModuleNotInWhiteListNoAction()
    {
		$this->assertTrue(
			isNeedRedirectDependingOnUserAndSystemState($this->_state, $this->_nonWhiteListModule,
					null, $this->_whiteList),
			"Assert that we need redirect for User on module not in whitelist");
	}
	
	public function testUserNeedsRedirectModuleNotInWhiteListActionNotInWhiteList()
	{
		$this->assertTrue(
				isNeedRedirectDependingOnUserAndSystemState($this->_state, $this->_nonWhiteListModule,
						$this->_nonWhiteListAction, $this->_whiteList),
				"Assert that we need redirect for User on module and action not in whitelist");
	}

	public function testUserNeedsRedirectModuleInWhiteListActionNotInWhiteList()
	{
		$this->assertTrue(
				isNeedRedirectDependingOnUserAndSystemState($this->_state, $this->_whiteListModule,
						$this->_nonWhiteListAction, $this->_whiteList),
				"Assert that we need redirect for User on module in whitelist and action not in whitelist");
	}

	public function testUserDontNeedRedirectModuleInWhiteListActionInWhiteList()
	{
		$this->assertFalse(
				isNeedRedirectDependingOnUserAndSystemState($this->_state, $this->_whiteListModule,
						$this->_whiteListAction, $this->_whiteList),
				"Assert that we dont need redirect for User on module in whitelist and action in whitelist");
	}

	public function testUserDontNeedRedirectModuleInWhiteListForAllActions()
	{
		$this->assertFalse(
				isNeedRedirectDependingOnUserAndSystemState($this->_state, $this->_whiteListModuleAllActions,
						$this->_nonWhiteListAction, $this->_whiteList),
				"Assert that we dont need redirect for User on module in whitelist for all actions");
	}


}
