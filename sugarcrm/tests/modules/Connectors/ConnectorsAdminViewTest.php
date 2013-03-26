<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA") which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

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
        require_once 'modules/Connectors/views/view.modifymapping.php';
        $view = new ViewModifyMapping(null, null);
        $view->ss = new Sugar_Smarty();
        $view->display();
        $this->expectOutputRegex('/ext_rest_linkedin/', 'Failed to asssert that LinkedIn connector appears');
        $this->expectOutputNotRegex('/ext_rest_insideview/', 'Failed to asssert that InsideView text does not appear');

    }

    public function testEnableConnectors()
    {
        require_once 'modules/Connectors/views/view.modifydisplay.php';
        $view = new ViewModifyDisplay(null, null);
        $view->ss = new Sugar_Smarty();
        $view->display();
        $this->expectOutputRegex('/ext_rest_linkedin/', 'Failed to asssert that LinkedIn connector appears');
        $this->expectOutputRegex('/ext_rest_insideview/', 'Failed to asssert that InsideView text does not appear');

    }

    public function testConnectorProperties()
    {
        require_once 'modules/Connectors/views/view.modifyproperties.php';
        $view = new ViewModifyProperties(null, null);
        $view->ss = new Sugar_Smarty();
        $view->display();
        $this->expectOutputRegex('/ext_rest_linkedin/', 'Failed to asssert that LinkedIn connector appears');
        $this->expectOutputNotRegex('/ext_rest_insideview/', 'Failed to asssert that InsideView text does not appear');

    }

    public function testConnectorSearchProperties()
    {
        require_once 'modules/Connectors/views/view.modifysearch.php';
        $view = new ViewModifySearch(null, null);
        $view->ss = new Sugar_Smarty();
        $view->display();
        $this->expectOutputNotRegex('/ext_rest_linkedin/', 'Failed to asssert that LinkedIn connector appears');
        $this->expectOutputNotRegex('/ext_rest_insideview/', 'Failed to asssert that InsideView text does not appear');
    }
}
