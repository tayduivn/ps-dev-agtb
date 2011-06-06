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
require_once("modules/Accounts/Account.php");

/**
 * @ticket 24095
 */
class Bug24095Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_tablename;
    private $_old_installing;
    
    public function setUp()
    {
        $this->accountMockBean = $this->getMock('Account' , array('hasCustomFields'));
        $this->_tablename = 'test' . date("YmdHis");
        if ( isset($GLOBALS['installing']) )
            $this->_old_installing = $GLOBALS['installing'];
        $GLOBALS['installing'] = true;
        
        $GLOBALS['db']->createTableParams($this->_tablename . '_cstm',
            array(
                'id_c' => array (
                    'name' => 'id_c',
                    'type' => 'id',
                    ),
                'foo_c' => array (
                    'name' => 'foo_c',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $GLOBALS['db']->query("INSERT INTO {$this->_tablename}_cstm (id_c,foo_c) VALUES ('12345','67890')");
    }
    
    public function tearDown()
    {
        $GLOBALS['db']->dropTableName($this->_tablename . '_cstm');
        if ( isset($this->_old_installing) ) {
            $GLOBALS['installing'] = $this->_old_installing;
        }
        else {
            unset($GLOBALS['installing']);
        }
    }
    
    public function testDynamicFieldsRetrieveWorks()
    {
        $bean = $this->accountMockBean;
        $bean->custom_fields = new DynamicField($bean->module_dir);
        $bean->custom_fields->setup($bean);
        $bean->expects($this->any())
             ->method('hasCustomFields')
             ->will($this->returnValue(true));
        $bean->table_name = $this->_tablename;
        $bean->id = '12345';
        $bean->custom_fields->retrieve();
        $this->assertEquals($bean->id_c, '12345');
        $this->assertEquals($bean->foo_c, '67890');
    }
}
