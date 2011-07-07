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
 
require_once('include/SugarCharts/SugarChartFactory.php');

class CustomSugarChartFactoryTest extends Sugar_PHPUnit_Framework_TestCase {

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

mkdir_recursive('custom/include/SugarCharts/CustomSugarChartFactory');
	
$the_string = <<<EOQ
<?php

require_once("include/SugarCharts/JsChart.php");

class CustomSugarChartFactory extends JsChart {
	
	function __construct() {
		parent::__construct();
	}
	
	function getChartResources() {
		return '
		<link type="text/css" href="'.getJSPath('include/SugarCharts/Jit/css/base.css').'" rel="stylesheet" />
		<!--[if IE]><script language="javascript" type="text/javascript" src="'.getJSPath('include/SugarCharts/Jit/js/Jit/Extras/excanvas.js').'"></script><![endif]-->
		<script language="javascript" type="text/javascript" src="'.getJSPath('include/SugarCharts/Jit/js/Jit/jit.js').'"></script>
		<script language="javascript" type="text/javascript" src="'.getJSPath('include/SugarCharts/Jit/js/sugarCharts.js').'"></script>
		';
	}
	
	function getMySugarChartResources() {
		return '
		<script language="javascript" type="text/javascript" src="'.getJSPath('include/SugarCharts/Jit/js/mySugarCharts.js').'"></script>
		';
	}
	

	function display(\$name, \$xmlFile, \$width='320', \$height='480', \$resize=false) {
	
		parent::display(\$name, \$xmlFile, \$width, \$height, \$resize);

		return \$this->ss->fetch('include/SugarCharts/Jit/tpls/chart.tpl');	
	}
	

	function getDashletScript(\$id,\$xmlFile="") {
		
		parent::getDashletScript(\$id,\$xmlFile);
		return \$this->ss->fetch('include/SugarCharts/Jit/tpls/DashletGenericChartScript.tpl');
	}

}

?>
EOQ;

$fp = sugar_fopen('custom/include/SugarCharts/CustomSugarChartFactory/CustomSugarChartFactory.php', "w");
fwrite($fp, $the_string );
fclose($fp );

}

public function tearDown()
{
	rmdir_recursive('custom/include/SugarCharts/CustomSugarChartFactory');
}


public function testCustomFactory()
{
	$sugarChart = SugarChartFactory::getInstance('CustomSugarChartFactory');
	$name = get_class($sugarChart);
	$this->assertEquals('CustomSugarChartFactory', $name, 'Assert engine is CustomSugarChartFactory');
}

}
