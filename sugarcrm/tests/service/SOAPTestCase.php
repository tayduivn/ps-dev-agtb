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
require_once('include/TimeDate.php');

abstract class SOAPTestCase extends Sugar_PHPUnit_Framework_TestCase
{
	public static $_user = null;
	public $_soapClient = null;
	public $_session = null;
	public $_sessionId = '';
    public $_soapURL = '';

    public static function setUpBeforeClass()
    {
        self::$_user = SugarTestUserUtilities::createAnonymousUser();
        self::$_user->status = 'Active';
        self::$_user->is_admin = 1;
        self::$_user->save();
        $GLOBALS['db']->commit();
        $GLOBALS['current_user'] = self::$_user;
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        unset($GLOBALS['current_user']);
        $GLOBALS['db']->commit();
    }

    /**
     * Create test user
     *
     */
	public function setUp()
    {
        $beanList = array();
		$beanFiles = array();
		require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;

        $this->_soapClient = new nusoapclient($this->_soapURL,false,false,false,false,false,600,600);
        parent::setUp();
        $GLOBALS['db']->commit();
    }

    /**
     * Remove anything that was used during this test
     *
     */
    public function tearDown()
    {
        $this->_sessionId = '';

		unset($GLOBALS['beanList']);
		unset($GLOBALS['beanFiles']);
        $GLOBALS['db']->commit();
    }

    protected function _login()
    {
        $GLOBALS['db']->commit();
    	$result = $this->_soapClient->call('login',
            array('user_auth' =>
                array('user_name' => self::$_user->user_name,
                    'password' => self::$_user->user_hash,
                    'version' => '.01'),
                'application_name' => 'SoapTest', "name_value_list" => array())
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
        $GLOBALS['db']->commit();
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
