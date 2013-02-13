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

require_once 'include/SugarObjects/SugarConfig.php';
require_once 'include/SugarObjects/VardefManager.php';

/**
 * @group bug60047
 */
class Bug60047Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected static $reloadVardefs;
    protected static $inDeveloperMode;
    
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        
        self::$reloadVardefs = isset($GLOBALS['reload_vardefs']) ? $GLOBALS['reload_vardefs'] : null;
        self::$inDeveloperMode = isset($_SESSION['developerMode']) ? $_SESSION['developerMode'] : false;
        
        // Force a vardef refresh forcefully because some tests in the suite 
        // actively destroy some globals. This will have an effect on getBean
        // for the duration of this test
        $GLOBALS['reload_vardefs'] = true;
        $_SESSION['developerMode'] = true;
    }
    
    public static function tearDownAfterClass()
    {
        if (self::$reloadVardefs) {
            $GLOBALS['reload_vardefs'] = self::$reloadVardefs;
        }
        
        if (self::$inDeveloperMode) {
            $_SESSION['developerMode'] = self::$inDeveloperMode;
        }
    }

    public function testForecastBean()
    {
        VardefManager::loadVardef("Forecasts", 'Forecast', true);
        $this->assertArrayHasKey("acls", $GLOBALS['dictionary']['Forecast']);
        $this->assertArrayHasKey("SugarACLStatic", $GLOBALS['dictionary']['Forecast']['acls']);
    }

    public function get_beans()
    {
        return array(
            array('ForecastWorksheets'),
            array('ForecastManagerWorksheets'),
            array('Worksheet'),
            array('ForecastOpportunities'),
        );
    }

    /**
     * @dataProvider get_beans
     */
    public function testForecastSubordinateBean($module)
    {
        // drop forecasting vardefs
        foreach(glob("cache/modules/Forecasts/*vardefs.php") as $file) {
            @unlink($file);
        }
        
        $bean = BeanFactory::getBean($module);
        $this->assertNotEmpty($bean);
        $this->assertArrayNotHasKey("acls", $GLOBALS['dictionary'][$bean->object_name]);
    }
}