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
require_once 'include/Dashlets/Dashlet.php';

/**
 * @ticket 33948
 */
class DashletAutoRefreshTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setup()
    {
        if ( isset($GLOBALS['sugar_config']['dashlet_auto_refresh_min']) ) {
            $this->backup_dashlet_auto_refresh_min = $GLOBALS['sugar_config']['dashlet_auto_refresh_min'];
        }
        unset($GLOBALS['sugar_config']['dashlet_auto_refresh_min']);
    }
    
    public function tearDown()
    {
        if ( isset($this->backup_dashlet_auto_refresh_min) ) {
            $GLOBALS['sugar_config']['dashlet_auto_refresh_min'] = $this->backup_dashlet_auto_refresh_min;
        }
    }
    
    public function testIsAutoRefreshableIfRefreshable() 
    {
        $dashlet = new DashletAutoRefreshTestMock('unit_test_run');
        $dashlet->isRefreshable = true;
        
        $this->assertTrue($dashlet->isAutoRefreshable());
    }
    
    public function testIsNotAutoRefreshableIfNotRefreshable() 
    {
        $dashlet = new DashletAutoRefreshTestMock('unit_test_run');
        $dashlet->isRefreshable = false;
        
        $this->assertFalse($dashlet->isAutoRefreshable());
    }
  
    public function testReturnCorrectAutoRefreshOptionsWhenMinIsSet() 
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setAppListString('dashlet_auto_refresh_options',
            array(
                '-1' 	=> 'Never',
                '30' 	=> 'Every 30 seconds',
                '60' 	=> 'Every 1 minute',
                '180' 	=> 'Every 3 minutes',
                '300' 	=> 'Every 5 minutes',
                '600' 	=> 'Every 10 minutes',
                )
            );
        $langpack->save();
    
        $GLOBALS['sugar_config']['dashlet_auto_refresh_min'] = 60;
        
        $dashlet = new DashletAutoRefreshTestMock('unit_test_run');
        $options = $dashlet->getAutoRefreshOptions();
        $this->assertEquals(
            array(
                '-1' 	=> 'Never',
                '60' 	=> 'Every 1 minute',
                '180' 	=> 'Every 3 minutes',
                '300' 	=> 'Every 5 minutes',
                '600' 	=> 'Every 10 minutes',
                ),
            $options
            );
        
        unset($langpack);
    }
    
    public function testReturnCorrectAutoRefreshOptionsWhenMinIsNotSet() 
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setAppListString('dashlet_auto_refresh_options',
            array(
                '-1' 	=> 'Never',
                '30' 	=> 'Every 30 seconds',
                '60' 	=> 'Every 1 minute',
                '180' 	=> 'Every 3 minutes',
                '300' 	=> 'Every 5 minutes',
                '600' 	=> 'Every 10 minutes',
                )
            );
        $langpack->save();
    
        $dashlet = new DashletAutoRefreshTestMock('unit_test_run');
        $options = $dashlet->getAutoRefreshOptions();
        $this->assertEquals(
            array(
                '-1' 	=> 'Never',
                '30' 	=> 'Every 30 seconds',
                '60' 	=> 'Every 1 minute',
                '180' 	=> 'Every 3 minutes',
                '300' 	=> 'Every 5 minutes',
                '600' 	=> 'Every 10 minutes',
                ),
            $options
            );
        
        unset($langpack);
    }
    
    public function testProcessAutoRefreshReturnsAutoRefreshTemplateNormally()
    {
        $dashlet = new DashletAutoRefreshTestMock('unit_test_run');
        $dashlet->isRefreshable = true;
        $_REQUEST['module'] = 'unit_test';
        $_REQUEST['action'] = 'unit_test';
        $dashlet->seedBean = new stdClass;
        $dashlet->seedBean->object_name = 'unit_test';
        
        $this->assertNotEmpty($dashlet->processAutoRefresh());
    }
    
    public function testProcessAutoRefreshReturnsNothingIfDashletIsNotRefreshable()
    {
        $dashlet = new DashletAutoRefreshTestMock('unit_test_run');
        $dashlet->isRefreshable = false;
        $_REQUEST['module'] = 'unit_test';
        $_REQUEST['action'] = 'unit_test';
        $dashlet->seedBean = new stdClass;
        $dashlet->seedBean->object_name = 'unit_test';
        
        $this->assertEmpty($dashlet->processAutoRefresh());
    }
    
    public function testProcessAutoRefreshReturnsNothingIfAutoRefreshingIsDisabled()
    {
        $dashlet = new DashletAutoRefreshTestMock('unit_test_run');
        $GLOBALS['sugar_config']['dashlet_auto_refresh_min'] = -1;
        $_REQUEST['module'] = 'unit_test';
        $_REQUEST['action'] = 'unit_test';
        $dashlet->seedBean = new stdClass;
        $dashlet->seedBean->object_name = 'unit_test';
        
        $this->assertEmpty($dashlet->processAutoRefresh());
    }
}

class DashletAutoRefreshTestMock extends Dashlet
{
    public function isAutoRefreshable() 
    {
        return parent::isAutoRefreshable();
    }
    
    public function getAutoRefreshOptions() 
    {
        return parent::getAutoRefreshOptions();
    }
    
    public function processAutoRefresh() 
    {
        return parent::processAutoRefresh();
    }
}
