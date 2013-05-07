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

require_once 'include/MetaDataManager/MetaDataManager.php';
require_once 'tests/SugarTestACLUtilities.php';
/**
 * ACL's
 */
class GetAclForModuleTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $roles = array();
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        $this->accounts = array();
        SugarACL::$acls = array();
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();
        foreach($this->accounts AS $account_id) {
            $db->query("DELETE FROM accounts WHERE id = '{$account_id}'");
        }
        $db->commit();
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    // test view only

    public function testViewOnly()
    {
        $modules = array('Accounts', );
        // user can view, list, delete, and export
        $expected_result = array(
            'admin' => 'no',
            'create' => 'no',
            'list' => 'no',
            'edit' => 'no',
            'delete' => 'no',
            'import' => 'no',
            'export' => 'no',
            'massupdate' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'view'));

        SugarTestACLUtilities::setupUser($role);

        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }


    // test view + list only

    public function testListOnly()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
            'admin' => 'no',
            'create' => 'no',
            'edit' => 'no',
            'delete' => 'no',
            'import' => 'no',
            'export' => 'no',
            'massupdate' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array('access', 'view', 'list'));

        SugarTestACLUtilities::setupUser($role);

        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }

    // test view + list owner
    public function testViewListOwner()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
            'admin' => 'no',
            'create' => 'no',
            'edit' => 'no',
            'delete' => 'no',
            'import' => 'no',
            'export' => 'no',
            'massupdate' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'list', 'view'), array('list', 'view'));

        SugarTestACLUtilities::setupUser($role);

        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }


 
    // test view owner + edit owner + create
    public function testViewEditOwnerCreate()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
            'admin' => 'no',
            'list' => 'no',
            'delete' => 'no',
            'import' => 'no',
            'export' => 'no',
            'massupdate' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'create', 'edit', 'view'), array('edit', 'view'));

        SugarTestACLUtilities::setupUser($role);

        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }

    // test all access, but admin
    public function testAllButAdmin()
    {
        $modules = array('Accounts', );
        // user can view, list, delete, and export
        $expected_result = array(
            'admin' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'create', 'view', 'list', 'edit', 'delete', 'import', 'export', 'massupdate'));

        SugarTestACLUtilities::setupUser($role);

        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            unset($_SESSION['ACL']);
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls);
        }
    }

    // test field level
    // test read only all fields
    // test read only 1 field
    public function testReadOnlyOneField()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
            'fields' =>
            array(
                'website' => array(
                    'write' => 'no',
                    'create' => 'no',
                ),
            ),
            'admin' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'create', 'view', 'list', 'edit','delete','import', 'export', 'massupdate'));

        SugarTestACLUtilities::createField($role->id, 'Accounts', 'website', 50);

        SugarTestACLUtilities::setupUser($role);

        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            $this->assertEquals($expected_result, $acls);
        }
    }    

    // test owner write 1 field
    public function testReadOwnerWriteOneField()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
            'fields' =>
            array(),
            'admin' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'create', 'view', 'list', 'edit','delete','import', 'export', 'massupdate'));

        SugarTestACLUtilities::createField($role->id, 'Accounts', 'website', 60);

        SugarTestACLUtilities::setupUser($role);

        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            $this->assertEquals($expected_result, $acls);
        }
    }       

    // test owner read/owner write 1 field
    public function testOwnerReadOwnerWriteOneField()
    {
        $modules = array('Accounts');
        // user can view, list, delete, and export
        $expected_result = array(
            'fields' =>
            array(),
            'admin' => 'no',
        );

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, array(
            'access', 'create', 'view', 'list', 'edit','delete','import', 'export', 'massupdate'));

        SugarTestACLUtilities::createField($role->id, 'Accounts','website', 40);

        SugarTestACLUtilities::setupUser($role);

        $mm = new MetaDataManager($GLOBALS['current_user']);
        foreach($modules AS $module) {
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            $this->assertEquals($expected_result, $acls);
        }
    }
}
