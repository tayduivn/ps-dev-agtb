<?php
require_once('include/SugarCharts/SugarChartFactory.php');

class CustomSugarChartFactoryTest extends Sugar_PHPUnit_Framework_TestCase {

var $hasCustomJit = false;
var $hasCustomFlash = false;

public function setUp()
{
	if(file_exists('custom/include/SugarCharts/Jit/Jit.php'))
	{
		$this->hasCustomJit = true;
		copy('custom/include/SugarCharts/Jit/Jit.php', 'custom/include/SugarCharts/Jit/Jit.php.bak');
		unlink('custom/include/SugarCharts/Jit/Jit.php');
	}

	if(file_exists('custom/include/SugarCharts/SugarFlash/SugarFlash.php'))
	{
		$this->hasCustomFlash = true;
		copy('custom/include/SugarCharts/SugarFlash/SugarFlash.php', 'custom/include/SugarCharts/SugarFlash/SugarFlash.php.bak');
		unlink('custom/include/SugarCharts/SugarFlash/SugarFlash.php');
	}

}

public function tearDown()
{
	if($this->hasCustomJit && file_exists('custom/include/SugarCharts/Jit/Jit.php.bak'))
	{
	   copy('custom/include/SugarCharts/Jit/Jit.php.bak', 'custom/include/SugarCharts/Jit/Jit.php');
	} else if(file_exists('custom/include/SugarCharts/Jit/Jit.php')) {
	   unlink('custom/include/SugarCharts/Jit/Jit.php');
	}

	if($this->hasCustomFlash && file_exists('custom/include/SugarCharts/SugarFlash/SugarFlash.php.bak'))
	{
	   copy('custom/include/SugarCharts/SugarFlash/SugarFlash.php.bak', 'custom/include/SugarCharts/SugarFlash/SugarFlash.php');
	} else if(file_exists('custom/include/SugarCharts/SugarFlash/SugarFlash.php')) {
	   unlink('custom/include/SugarCharts/SugarFlash/SugarFlash.php');
	}

	if(file_exists('custom/include/SugarCharts/Jit/Jit.php.bak'))
	{
	   unlink('custom/include/SugarCharts/Jit/Jit.php.bak');
	}

	if(file_exists('custom/include/SugarCharts/SugarFlash/SugarFlash.php.bak')) {
	   unlink('custom/include/SugarCharts/SugarFlash/SugarFlash.php.bak');
	}
}


public function testCustomFactories()
{
	if(!file_exists('custom/include/SugarCharts/Jit'))
	{
		mkdir_recursive('custom/include/SugarCharts/Jit');
	}

	if(!file_exists('custom/include/SugarCharts/SugarFlash'))
	{
		mkdir_recursive('custom/include/SugarCharts/SugarFlash');
	}

	copy('include/SugarCharts/Jit/Jit.php', 'custom/include/SugarCharts/Jit/Jit.php');
	copy('include/SugarCharts/SugarFlash/SugarFlash.php', 'custom/include/SugarCharts/SugarFlash/SugarFlash.php');

	$sugarChart = SugarChartFactory::getInstance('Jit');
	$name = get_class($sugarChart);
	$this->assertEquals('Jit', $name, 'Assert engine is Jit');

	$sugarChart = SugarChartFactory::getInstance('SugarFlash');
	$name = get_class($sugarChart);
	$this->assertEquals('SugarFlash', $name, 'Assert engine is SugarFlash');
}

}
