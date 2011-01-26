<?php
require_once('include/SugarCharts/SugarChartFactory.php');

class SugarChartFactoryTest extends Sugar_PHPUnit_Framework_TestCase {

var $engine;

	public static function setUpBeforeClass()
	{
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

	public static function tearDownAfterClass()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}


public function setUp()
{
    global $sugar_config;
	if(!empty($sugar_config['chartEngine']))
	{
		$this->engine = $sugar_config['chartEngine'];
	}
}

public function tearDown()
{
	if(!empty($this->engine))
	{
		global $sugar_config;
		$sugar_config['chartEngine'] = $this->engine;
	}
}

public function testChartFactoryDefault()
{
	$sugarChart = SugarChartFactory::getInstance();
	$name = get_class($sugarChart);
	$this->assertEquals('Jit', $name, 'Assert chart engine defaults to Jit');
}

public function testChartFactoryJit()
{
	$sugarChart = SugarChartFactory::getInstance('Jit');
	$name = get_class($sugarChart);
	$this->assertEquals('Jit', $name, 'Assert engine is Jit');

	$sugarChart = SugarChartFactory::getInstance('Jit', 'Reports');
	$name = get_class($sugarChart);
	$this->assertEquals('JitReports', $name, 'Assert chart engine is JitReport');
}

public function testChartFactoryFlash()
{
	$sugarChart = SugarChartFactory::getInstance('SugarFlash');
	$name = get_class($sugarChart);
	$this->assertEquals('SugarFlash', $name, 'Assert engine is SugarFlash');

	$sugarChart = SugarChartFactory::getInstance('SugarFlash', 'Reports');
	$name = get_class($sugarChart);
	$this->assertEquals('SugarFlashReports', $name, 'Assert chart engine is SugarFlashReports');
}

public function testConfigChartFactory()
{
 	global $sugar_config;
 	$sugar_config['chartEngine'] = 'SugarFlash';
	$sugarChart = SugarChartFactory::getInstance();
	$name = get_class($sugarChart);
	$this->assertEquals('SugarFlash', $name, 'Assert chart engine set in global sugar_config is correct');
}
}
