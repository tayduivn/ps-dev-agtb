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

require_once 'include/database/DBManagerFactory.php';

class Bug34547Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $_has_mysqli_disabled;
    private $_db;

    public function setUp()
    {
        $this->_db = DBManagerFactory::getInstance();
        if(get_class($this->_db) != 'MysqlManager' && get_class($this->_db) != 'MysqliManager') {
            $this->markTestSkipped("Skipping test if not mysql or mysqli configuration");
        }

        unset($GLOBALS['dbinstances']);

        $this->_has_mysqli_disabled = (!empty($GLOBALS['sugar_config']['mysqli_disabled']) && $GLOBALS['sugar_config']['mysqli_disabled'] === TRUE);
        if(!$this->_has_mysqli_disabled) {
            $GLOBALS['sugar_config']['mysqli_disabled'] = TRUE;
        }
        DBManagerFactory::disconnectAll();
    }

    public function tearDown()
    {
        if(!$this->_has_mysqli_disabled) {
           unset($GLOBALS['sugar_config']['mysqli_disabled']);
        }
        unset($GLOBALS['dbinstances']);
        DBManagerFactory::disconnectAll();
    }

    public function testMysqliDisabledInGetInstance()
    {
        $this->_db = DBManagerFactory::getInstance();
        $this->assertEquals('MysqlManager', get_class($this->_db), "Assert that MysqliManager is not disabled");
    }

}