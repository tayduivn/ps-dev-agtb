<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * @ticket 66034
 */
class Bug66034Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $aclAction;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $old_current_user = $GLOBALS['current_user'];
        $new_current_user = new SugarACLDeveloperOrAdminUserMock();
        $new_current_user->retrieve($old_current_user->id);
        $GLOBALS['current_user'] = $new_current_user;
        $this->aclAction = new ACLAction();
    }

    public function tearDown()
    {
        $this->aclAction->clearSessionCache();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function aclAccessData() 
    {
        return array(
                array('ContractTypes', 'Contracts', 'list', 'Developer', true, 'Developer should be able to access ContractTypes'),
                array('ContractTypes', 'Contracts', 'list', 'Admin', true, 'Admin should be able to access ContractTypes'),
                array('ContractTypes', 'Contracts', 'list', '', false, 'Regular user should not be able to access ContractTypes'),
                array('Releases', 'Bugs', 'edit', 'Developer', true, 'Developer should be able to edit Releases'),
                array('Releases', 'Bugs', 'edit', 'Admin', true, 'Admin should be able to edit Releases'),
                array('Releases', 'Bugs', 'edit', '', false, 'Regular user should not be able to edit Releases'),
                array('ACLRoles', 'Users', 'list', 'Developer', true, 'Developer should be able to list ACLRoles'),
                array('ACLRoles', 'Users', 'list', 'Admin', true, 'Admin should be able to list ACLRoles'),
                array('ACLRoles', 'Users', 'list', '', false, 'Regular user should not be able to list ACLRoles'),
                array('ProductTemplates', 'Products', 'edit', 'Developer', true, 'Developer should be able to edit ProductTemplates'),
                array('ProductTemplates', 'Products', 'edit', 'Admin', true, 'Admin should be able to edit ProductTemplates'),
                array('ProductTemplates', 'Products', 'edit', '', false, 'Regular user should not be able to edit ProductTemplates'),
                array('ProductTypes', 'Products', 'edit', 'Developer', true, 'Developer should be able to edit ProductTypes'),
                array('ProductTypes', 'Products', 'edit', 'Admin', true, 'Admin should be able to edit ProductTypes'),
                array('ProductTypes', 'Products', 'edit', '', false, 'Regular user should not be able to edit ProductTypes'),
                array('ProductCategories', 'Products', 'edit', 'Developer', true, 'Developer should be able to edit ProductCategories'),
                array('ProductCategories', 'Products', 'edit', 'Admin', true, 'Admin should be able to edit ProductCategories'),
                array('ProductCategories', 'Products', 'edit', '', false, 'Regular user should not be able to edit ProductCategories'),
                array('Manufacturers', 'Products', 'edit', 'Developer', true, 'Developer should be able to edit Manufacturers'),
                array('Manufacturers', 'Products', 'edit', 'Admin', true, 'Admin should be able to edit Manufacturers'),
                array('Manufacturers', 'Products', 'edit', '', false, 'Regular user should not be able to edit Manufacturers'),
                array('Shippers', 'Products', 'edit', 'Developer', true, 'Developer should be able to edit Shippers'),
                array('Shippers', 'Products', 'edit', 'Admin', true, 'Admin should be able to edit Shippers'),
                array('Shippers', 'Products', 'edit', '', false, 'Regular user should not be able to edit Shippers'),
                array('TaxRates', 'Quotes', 'edit', 'Developer', true, 'Developer should be able to edit TaxRates'),
                array('TaxRates', 'Quotes', 'edit', 'Admin', true, 'Admin should be able to edit TaxRates'),
                array('TaxRates', 'Quotes', 'edit', '', false, 'Regular user should not be able to edit TaxRates'),
                );
    }

    /**
     * @dataProvider aclAccessData
     */
    public function testAclAccess($module, $aclModule, $action, $role, $result, $message)
    {
        $bean = BeanFactory::getBean($module);

        if (!empty($role)) {
            $method = 'set'.$role.'ForModule';
            $GLOBALS['current_user']->$method($aclModule);
            $this->aclAction->clearSessionCache();
        }
        
        $this->assertEquals($result, $bean->ACLAccess($action), $message);
    }
}

class SugarACLDeveloperOrAdminUserMock extends User
{
    protected $developerForModules = array();
    protected $adminForModules = array();

    public function setDeveloperForModule($module)
    {
        $this->developerForModules[$module] = true;
    }

    public function setAdminForModule($module)
    {
        $this->adminForModules[$module] = true;
    }

    public function isDeveloperForModule($module)
    {
        return !empty($this->developerForModules[$module]);
    }
    
    public function isAdminForModule($module)
    {
        return !empty($this->adminForModules[$module]);
    }
}