<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

require_once 'include/api/RestService.php';
require_once 'include/api/ApiHelper.php';

class Bug67650Test extends Sugar_PHPUnit_Framework_TestCase
{
	protected $customIncludeDir = 'custom/data';
	protected $customIncludeFile = 'SugarBeanApiHelper.php';

    public function setUp()
    {
        // create a custom include file
        $customIncludeFileContent = <<<EOQ
<?php
class CustomSugarBeanApiHelper
{
}
EOQ;
        if (!file_exists($this->customIncludeDir)) {
            sugar_mkdir($this->customIncludeDir, 0777, true);
        }
          
        SugarAutoLoader::put($this->customIncludeDir . '/' . $this->customIncludeFile, $customIncludeFileContent);
    }

    public function tearDown()
    {
        if (file_exists($this->customIncludeDir . '/' . $this->customIncludeFile)) {
            SugarAutoLoader::unlink($this->customIncludeDir . '/' . $this->customIncludeFile);
        }
    }

    public function testFindCustomHelper()
    {
        $api = new RestService();
        $accountsBean = BeanFactory::getBean('Accounts');
        $helper = ApiHelper::getHelper($api,$accountsBean);
        $this->assertEquals('CustomSugarBeanApiHelper',get_class($helper));
    }
}
