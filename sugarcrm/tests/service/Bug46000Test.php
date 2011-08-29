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


class Bug46000Test extends SOAPTestCase
{
	public $_soapClient = null;
    var $_sessionId;
    var $c = null;
    var $c2 = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$_user->is_admin = 0;
        self::$_user->save();
    }

    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
        parent::setUp();
        $this->_login();
    }

    public function testCreateUser()
    {
        $nv = array(
                     array('name' => 'user_name', 'value' => 'test@test.com'),
                     array('name' => 'user_hash', 'value' => '12345'),
                     array('name' => 'first_name', 'value' => 'TestFirst'),
                     array('name' => 'last_name', 'value' => 'Test Last'),
                     array('name' => 'title', 'value' => 'Test Admin'),
                    array('name' => 'is_admin' , 'value' =>  '1'),
                );
        $result = $this->_soapClient->call('set_entry',array('session'=>$this->_sessionId,"module_name" => 'Users', 'name_value_list' => $nv));
        $this->assertEquals($result['error']['number'], '40');
    }

    public function testMakeUserAdmin()
    {
        $nv = array(
                    array('name' => 'id', 'value' => self::$_user->id),
                    array('name' => 'is_admin' , 'value' =>  '1'),
                );
        $result = $this->_soapClient->call('set_entry',array('session'=>$this->_sessionId,"module_name" => 'Users', 'name_value_list' => $nv));
        $this->assertEquals($result['error']['number'], '40');

    }

    public function testGetEntry()
    {
        $result = $this->_soapClient->call('get_entry',array('session'=>$this->_sessionId,"module_name" => 'Users', 'id' => self::$_user->id, 'select_fields' => array('first_name')));
        $this->assertArrayHasKey('entry_list', $result);
        $this->assertEquals($result['entry_list'][0]['name_value_list'][0]['value'], self::$_user->first_name);
    }

}