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
 
require_once 'modules/Import/ImportDuplicateCheck.php';

class ImportDuplicateCheckTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp() 
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $app_strings = array();
        require('include/language/en_us.lang.php');
        $GLOBALS['app_strings'] = $app_strings;
    }
    
    public function tearDown() 
    {
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['app_strings']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
    public function testGetDuplicateCheckIndexesWithEmail()
    {
        $focus = loadBean('Contacts');
        
        $idc     = new ImportDuplicateCheck($focus);
        $indexes = $idc->getDuplicateCheckIndexes();
        
        foreach ( $focus->getIndices() as $key => $index ) {
            if ($key != 'id') $this->assertTrue(isset($indexes[$index['name']]),"{$index['name']} should be in the list");
        }
        
        $this->assertTrue(isset($indexes['special_idx_email1']));
        $this->assertTrue(isset($indexes['special_idx_email2']));
    }
    
    public function testGetDuplicateCheckIndexesNoEmail()
    {
        $focus = loadBean('Calls');
        
        $idc     = new ImportDuplicateCheck($focus);
        $indexes = $idc->getDuplicateCheckIndexes();
        
        foreach ( $focus->getIndices() as $key => $index ) {
            if ($key != 'id') $this->assertTrue(isset($indexes[$index['name']]));
        }
        
        $this->assertFalse(isset($indexes['special_idx_email1']));
        $this->assertFalse(isset($indexes['special_idx_email2']));
    }
    
    public function testIsADuplicateRecord()
    {
        $last_name = 'FooBar'.date("YmdHis");
        
        $focus = loadBean('Contacts');
        $focus->last_name = $last_name;
        $id = $focus->save(false);
        
        $focus = loadBean('Contacts');
        $focus->last_name = $last_name;
        
        $idc = new ImportDuplicateCheck($focus);
        
        $this->assertTrue($idc->isADuplicateRecord(array('idx_contacts_del_last::last_name')));
        
        $focus->mark_deleted($id);
    }
    
    public function testIsADuplicateRecordEmail()
    {
        $email = date("YmdHis").'@foobar.com';
        
        $focus = loadBean('Contacts');
        $focus->email1 = $email;
        $id = $focus->save(false);
        
        $focus = loadBean('Contacts');
        $focus->email1 = $email;
        
        $idc = new ImportDuplicateCheck($focus);
        
        $this->assertTrue($idc->isADuplicateRecord(array('special_idx_email1')));
        
        $focus->mark_deleted($id);
    }
    
    public function testIsADuplicateRecordNotFound()
    {
        $last_name = 'BadFooBar'.date("YmdHis");
        
        $focus = loadBean('Contacts');
        $focus->last_name = $last_name;
        
        $idc = new ImportDuplicateCheck($focus);
        
        $this->assertFalse($idc->isADuplicateRecord(array('idx_contacts_del_last::'.$last_name)));
    }
    
    public function testIsADuplicateRecordEmailNotFound()
    {
        $email = date("YmdHis").'@badfoobar.com';
        
        $focus = loadBean('Contacts');
        $focus->email1 = $email;
        
        $idc = new ImportDuplicateCheck($focus);
        
        $this->assertFalse($idc->isADuplicateRecord(array('special_idx_email1')));
    }
}
