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
 
require_once 'modules/Import/UsersLastImport.php';

class UsersLastImportTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_importModule;
    private $_importRecordCount;
    private $_importIds;
    private $_usersLastImport;
    private $_usersLastImportIds;
    
    public function setUp() 
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->_importModule = 'Notes';
        $this->_importObject = 'Note';
        $this->_importRecordCount = 3;
        $this->_importIds = array();
        $this->_usersLastImport = new UsersLastImport();
        $this->_addImportedRecords();
    }
    
    public function tearDown() 
    {
        $focus = $this->_loadBean($this->_importModule);
        $GLOBALS['db']->query(
            'DELETE FROM ' . $focus->table_name . ' 
                WHERE id IN (\'' . 
                    implode("','",$this->_importIds) . '\')');
        $GLOBALS['db']->query(
            'DELETE FROM users_last_import 
                WHERE id IN (\'' . 
                    implode("','",$this->_usersLastImportIds) . '\')');
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }
    
    private function _loadBean()
    {
        return loadBean($this->_importModule);
    }
    
    private function _addImportedRecords()
    {
        for ( $i = 0; $i < $this->_importRecordCount; $i++ ) {
            $focus = $this->_loadBean($this->_importModule);
            $focus->name = "record $i";
            $focus->save();
            $this->_importIds[$i] = $focus->id;
            
            $last_import = new UsersLastImport();
            $last_import->assigned_user_id = $GLOBALS['current_user']->id;
            $last_import->import_module = $this->_importModule;
            $last_import->bean_type = $this->_importObject;
            $last_import->bean_id = $this->_importIds[$i];
            $this->_usersLastImportIds[] = $last_import->save();
        }
    }
    
    public function testMarkDeletedByUserId()
    {
        $this->_usersLastImport->mark_deleted_by_user_id($GLOBALS['current_user']->id);
        
        $query = "SELECT * FROM users_last_import 
                    WHERE assigned_user_id = '{$GLOBALS['current_user']->id}'";
        
        $result = $GLOBALS['db']->query($query);
       
        $this->assertFalse($GLOBALS['db']->fetchByAssoc($result),'There should not be any records in the table now');
    }
    
    public function testUndo()
    {
        $this->assertTrue(
            $this->_usersLastImport->undo(
                $this->_importModule
                )
            );
        
        $focus = $this->_loadBean($this->_importModule);
        
        $query = "SELECT * FROM {$focus->table_name}
                    WHERE id IN ('" . 
                        implode("','",$this->_importIds) . "')";
        
        $result = $GLOBALS['db']->query($query);
        
        $this->assertFalse($GLOBALS['db']->fetchByAssoc($result),'There should not be any records in the table now');
    }
    
    /**
     * @ticket 21828
     */
    public function testUndoRemovedAddedEmailAddresses()
    {
        $time = date('Y-m-d H:i:s');
        $unid = uniqid();
        
        $focus = new Account();
        $focus->id = "Account_".$unid;
        
        $last_import = new UsersLastImport();http://www.php.net/manual/en/refs.database.php
        $last_import->assigned_user_id = $GLOBALS['current_user']->id;
        $last_import->import_module = 'Accounts';
        $last_import->bean_type = 'Account';
        $last_import->bean_id = $focus->id;
        $last_import->save();
        
        $this->email_addr_bean_rel_id = 'email_addr_bean_rel_'.$unid;
        $this->email_address_id = 'email_address_id_'.$unid;
        $GLOBALS['db']->query("insert into email_addr_bean_rel (id , email_address_id, bean_id, bean_module, primary_address, date_created , date_modified) values ('{$this->email_addr_bean_rel_id}', '{$this->email_address_id}', '{$focus->id}', 'Accounts', 1, '$time', '$time')");
				
        $GLOBALS['db']->query("insert into email_addresses (id , email_address, email_address_caps, date_created, date_modified) values ('{$this->email_address_id}', 'test@g.com', 'TEST@G.COM', '$time', '$time')");

        // setup
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        
        $this->assertTrue(
            $last_import->undo(
                $last_import->import_module
                )
            );
        
        // teardown
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    	
    	$result = $GLOBALS['db']->query("SELECT * FROM email_addr_bean_rel where id = '{$this->email_addr_bean_rel_id}'");
		$rows = $GLOBALS['db']->fetchByAssoc($result);
    	$this->assertFalse($rows);
    	
    	$result = $GLOBALS['db']->query("SELECT * FROM email_addresses where id = '{$this->email_address_id}'");
		$rows = $GLOBALS['db']->fetchByAssoc($result);
    	$this->assertFalse($rows);
        
        $GLOBALS['db']->query("DELETE FROM users_last_import WHERE id = '{$last_import->id}'");
    }
    
    public function testUndoById()
    {
        $this->assertTrue(
            $this->_usersLastImport->undoById(
                $this->_usersLastImportIds[0]
                )
            );
        
        $focus = $this->_loadBean($this->_importModule);
        
        $query = "SELECT * FROM {$focus->table_name}
                    WHERE id = '{$this->_importIds[0]}'";
        
        $result = $GLOBALS['db']->query($query);
        
        $this->assertFalse($GLOBALS['db']->fetchByAssoc($result),'There should not be any records in the table now');

    }
    
    public function testGetBeansByImport()
    {
        foreach ( UsersLastImport::getBeansByImport('Notes') as $objectName )
            $this->assertEquals($objectName,'Note');
    }
}
