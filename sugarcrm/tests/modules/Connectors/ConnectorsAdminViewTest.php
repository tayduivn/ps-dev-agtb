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

/**
 * ConnectorsAdminViewTest
 *
 * @author Collin Lee
 */
class ConnectorsAdminViewTest extends Sugar_PHPUnit_Framework_OutputTestCase
{

public static function setUpBeforeClass()
{
    global $mod_strings, $app_strings, $theme;
    $theme = SugarTestThemeUtilities::createAnonymousTheme();
    $mod_strings = return_module_language($GLOBALS['current_language'], 'Connectors');
    $app_strings = return_application_language($GLOBALS['current_language']);
}

public static function tearDownAfterClass()
{
    global $mod_strings, $app_strings, $theme;
    SugarTestThemeUtilities::removeAllCreatedAnonymousThemes();
    unset($theme);
    unset($mod_strings);
    unset($app_strings);
}

public function testMapConnectorFields()
{
    require_once('modules/Connectors/views/view.modifymapping.php');
    $view = new ViewModifyMapping(null, null);
    $view->ss = new Sugar_Smarty();
    $view->display();
    $this->expectOutputRegex('/ext_rest_linkedin/', 'Failed to asssert that LinkedIn connector appears');
    $this->expectOutputNotRegex('/ext_rest_insideview/', 'Failed to asssert that InsideView text does not appear');

}

public function testEnableConnectors()
{
    require_once('modules/Connectors/views/view.modifydisplay.php');
    $view = new ViewModifyDisplay(null, null);
    $view->ss = new Sugar_Smarty();
    $view->display();
    $this->expectOutputRegex('/ext_rest_linkedin/', 'Failed to asssert that LinkedIn connector appears');
    $this->expectOutputRegex('/ext_rest_insideview/', 'Failed to asssert that InsideView text does not appear');

}

public function testConnectorProperties()
{
    require_once('modules/Connectors/views/view.modifyproperties.php');
    $view = new ViewModifyProperties(null, null);
    $view->ss = new Sugar_Smarty();
    $view->display();
    $this->expectOutputRegex('/ext_rest_linkedin/', 'Failed to asssert that LinkedIn connector appears');
    $this->expectOutputNotRegex('/ext_rest_insideview/', 'Failed to asssert that InsideView text does not appear');

}

public function testConnectorSearchProperties()
{
    require_once('modules/Connectors/views/view.modifysearch.php');
    $view = new ViewModifySearch(null, null);
    $view->ss = new Sugar_Smarty();
    $view->display();
    $this->expectOutputNotRegex('/ext_rest_linkedin/', 'Failed to asssert that LinkedIn connector appears');
    $this->expectOutputNotRegex('/ext_rest_insideview/', 'Failed to asssert that InsideView text does not appear');
}

}


