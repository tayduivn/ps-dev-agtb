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
require_once 'include/Dashlets/DashletGenericChart.php';

class DashletGenericChartTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testLazyLoadSmartyObject() 
    {
        $dgc = new DashletGenericChartTestMock('unit_test_run');
        
        $smarty = $dgc->getConfigureSmartyInstance();
        
        $this->assertInstanceOf('Sugar_Smarty',$smarty);
        
        $smarty->assign('dog','cat');
        
        $smarty2 = $dgc->getConfigureSmartyInstance();
        
        $this->assertEquals('cat',$smarty2->get_template_vars('dog'));
    }
    
    public function testLazyLoadSeedBean() 
    {
        $dgc = new DashletGenericChartTestMock('unit_test_run');
        
        $focus = $dgc->getSeedBean();
        
        $this->assertInstanceOf('User',$focus);
        
        $focus->user_name = 'foobar';
        
        $focus2 = $dgc->getSeedBean();
        
        $this->assertEquals('foobar',$focus2->user_name);
    }
    
    public function testDisplay()
    {
        $dashlet = $this->getMock('DashletGenericChartTestMock',
                                    array('processAutoRefresh'),
                                    array('unit_test_run')
                                    );
        $dashlet->expects($this->any())
                ->method('processAutoRefresh')
                ->will($this->returnValue('successautorefresh'));
                
        $this->assertEquals('successautorefresh',$dashlet->display());
    }
    
    public function testSetRefreshIconIfRefreshable()
    {
        $dashlet = new DashletGenericChartTestMock('unit_test_run');
        $dashlet->isRefreshable = true;
        
        $this->assertContains('SUGAR.mySugar.retrieveDashlet(\'unit_test_run\',\'predefined_chart\');',$dashlet->setRefreshIcon());
    }
    
    public function testSetRefreshIconIfNotRefreshable()
    {
        $dashlet = new DashletGenericChartTestMock('unit_test_run');
        $dashlet->isRefreshable = false;
        
        $this->assertNotContains('SUGAR.mySugar.retrieveDashlet(\'unit_test_run\',\'predefined_chart\');',$dashlet->setRefreshIcon());
    }
    
    public function testConstructQueryReturnsNothing()
    {
        $dashlet = new DashletGenericChartTestMock('unit_test_run');
        
        $this->assertEmpty($dashlet->constructQuery());
    }
    
    public function testConstructGroupByReturnsNothing()
    {
        $dashlet = new DashletGenericChartTestMock('unit_test_run');
        
        $this->assertEquals(array(),$dashlet->constructGroupBy());
    }
}

class DashletGenericChartTestMock extends DashletGenericChart
{
    protected $_seedName = 'Users';
    
    public function getConfigureSmartyInstance()
    {
        return parent::getConfigureSmartyInstance();
    }
    
    public function getSeedBean()
    {
        return parent::getSeedBean();
    }
    
    public function constructQuery()
    {
        return parent::constructQuery();
    }
    
    public function constructGroupBy()
    {
        return parent::constructGroupBy();
    }
}
