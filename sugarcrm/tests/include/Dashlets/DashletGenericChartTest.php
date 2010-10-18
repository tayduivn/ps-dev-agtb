<?php
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
}
