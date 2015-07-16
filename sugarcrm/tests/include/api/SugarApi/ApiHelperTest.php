<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'include/api/RestService.php';
require_once 'include/api/ApiHelper.php';

class ApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $toDelete = array();

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');
    }

    public static function tearDownAfterClass()
    {
        // rebuild the map JIC
        SugarAutoLoader::buildCache();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();

        foreach($this->toDelete as $file) {
            if(is_dir($file)) {
                rmdir_recursive($file);
                SugarAutoLoader::delFromMap($file, false);
                continue;
            }
            @SugarAutoLoader::unlink($file);
        }
        $this->toDelete = array();
    }

    protected function put($file, $data)
    {
        $this->toDelete[] = $file;
        SugarAutoLoader::put($file, $data);
    }

    public function testGetHelper_ReturnsBaseHelper()
    {
        $api = new RestService();

        $accountsBean = BeanFactory::newBean('Accounts');

        $helper = ApiHelper::getHelper($api,$accountsBean);

        $this->assertEquals('SugarBeanApiHelper',get_class($helper));
    }

    public function testGetHelper_ReturnsModuleHelper()
    {
        $api = new RestService();

        $bugsBean = BeanFactory::newBean('Users');

        $helper = ApiHelper::getHelper($api,$bugsBean);

        $this->assertEquals('UsersApiHelper',get_class($helper));
    }

    public function testGetHelper_ModulePathSubDirectory_ReturnModuleHelper()
    {
        $moduleName = 'Contacts';
        $modulePath = 'Activities/Contacts';

        mkdir_recursive("modules/Activities/Contacts");
        $this->put('modules/' . $modulePath . '/' . $moduleName . 'ApiHelper.php', "<?php class {$moduleName}ApiHelper {}");

        $api = new RestService();

        $bean = BeanFactory::newBean('Contacts');
        $bean->module_dir = $modulePath;

        $helper = ApiHelper::getHelper($api,$bean);

        $this->assertEquals('ContactsApiHelper', get_class($helper));
    }
}
