<?php
// FILE SUGARCRM flav=pro ONLY
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional 
End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to 
the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the 
terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, 
redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the 
Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial 
gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable 
fees and any use of the
 *Software without first paying applicable fees is strictly 
prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user 
interface.
 * All copies of the Covered Code must include on each user interface 
screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full 
license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly 
stated in the License.  Please refer
 *to the License for the specific language governing these rights and 
limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; 
All Rights Reserved.
 ********************************************************************************/

require_once("data/BeanFactory.php");
class BeanFactoryTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $createdBeans = array();

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        foreach($this->createdBeans as $bean)
        {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }
    }


    /**
     * Create a new account and bug, then link them.
     * @return void
     */
    public function testGetBean()
    {
        $module = "Accounts";
        global $beanList, $beanFiles;
        require('include/modules.php');

        $account = BeanFactory::newBean($module);
        $account->name = "Unit Test";
        $account->save();
        $this->createdBeans[] = $account;

        $validBean = BeanFactory::retrieveBean($module, $account->id);

        $this->assertEquals($account->id, $validBean->id);

        //Ensure we get a false if we try to load a bad bean.
        $uniqueID = uniqid();
        $invalidBean = BeanFactory::retrieveBean($module, $uniqueID);
        $this->assertFalse(isset($invalidBean->id));
    }

    public function testRegisterBean()
    {
        // Create a new record
        $module = 'Accounts';
        $account = BeanFactory::newBean($module);
        $account->name = 'BeanFactoryTest';
        $account->save();
        $this->createdBeans[] = $account;

        // Test that it is registered
        $registered = BeanFactoryTestMock::isRegistered($account);
        $this->assertTrue($registered, "Newly created Account bean is not registered");

        // Change the record and get it again
        $account->name = 'BeanFactoryTestHASCHANGED';
        $account->save();

        // Test that the changes took
        $new = BeanFactory::getBean($module, $account->id);
        $this->assertEquals($account->name, $new->name);
    }
    
    public function testRegisterBeanLegacyStyle()
    {
        // Create a new record
        $module = 'Accounts';
        $account = BeanFactory::newBean($module);
        $account->name = 'BeanFactoryTest';
        $account->save();
        $this->createdBeans[] = $account;

        // Unregister it so we can test registration
        BeanFactory::unregisterBean($account);
        $unregistered = BeanFactoryTestMock::isRegistered($account);
        $this->assertFalse($unregistered, "New bean is still registered in the factory");
        
        // Test registration old style way
        $registered = BeanFactory::registerBean($module, $account, $account->id);
        $this->assertTrue($registered, "Legacy style registration of the bean failed");
        
        // Double ensure it worked
        $registered = BeanFactoryTestMock::isRegistered($account);
        $this->assertTrue($registered, "Legacy style registration did not actually register the bean");
    }

    public function testUnregisterBean()
    {
        // Create the bean and save to register
        $module = 'Accounts';
        $account = BeanFactory::newBean($module);
        $account->name = 'BeanFactoryTest';
        $account->save();
        $this->createdBeans[] = $account;

        // Test that unregister is true for a bean
        $unregistered = BeanFactory::unregisterBean($account);
        $this->assertTrue($unregistered, "Unregister with a bean failed");

        // Test that the bean is no longer in the registry
        $unregistered = BeanFactoryTestMock::isRegistered($account);
        $this->assertFalse($unregistered, "New bean is still registered in the factory");
    }
}

class BeanFactoryTestMock extends BeanFactory
{
    public static function isRegistered($bean)
    {
        if (!empty($bean->module_name) && !empty($bean->id)) {
            return isset(self::$loadedBeans[$bean->module_name][$bean->id]);
        }

        return false;
    }
}
