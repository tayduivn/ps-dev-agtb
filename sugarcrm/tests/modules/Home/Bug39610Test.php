<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

 
require_once 'include/MVC/Controller/SugarController.php';
require_once 'include/MVC/View/views/view.classic.php';
require_once 'include/MVC/View/views/view.classic.config.php';

class Bug39610Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $app_strings, $app_list_strings;
        $app_strings = return_application_language($GLOBALS['current_language']);
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']); 
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }
    
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
    public function testUseCustomViewAndCustomClassName()
    {
        $target_module = 'Accounts';
        sugar_mkdir('custom/modules/'. $target_module . '/views/',null,true);
        if( $fh = @fopen('custom/modules/'. $target_module . '/views/view.subpanelquickcreate.php', 'w') )
        {
$string = <<<EOQ
<?php
class CustomAccountsSubpanelQuickCreate {};
?>
EOQ;
            fputs( $fh, $string);
            fclose( $fh );
        }

        
        $_REQUEST = array(
            'module' => 'Home',
            'target_module' => $target_module,
            'action' => 'SubpanelCreates',
            );
        $controller = new SugarControllerMockBug39610Test;
        $controller->setup();
        $controller->do_action = 'SubpanelCreates';
        $controller->process();
        $GLOBALS['app']->controller = $controller;
        $view = new ViewClassicMock();
		$view->init(loadBean($target_module));
        $GLOBALS['current_view'] = $view;
        ob_start();
        $view->process();
        ob_clean();
        
        $this->assertEquals('CustomAccountsSubpanelQuickCreate', $view->_sqc);
        
        @unlink('custom/modules/'. $target_module . '/views/view.subpanelquickcreate.php');
    }

}

class SugarControllerMockBug39610Test extends SugarController
{
    public $do_action;
    
    public function callLegacyCode()
    {
        return parent::callLegacyCode();
    }
}

class ViewClassicMock extends ViewClassic
{
    var $_sqc = '';
    
    public function includeClassicFile($file)
    {
        global $sqc;
        ob_clean();
        
        // BEGIN DUPLICATE FROM SugarView::includeClassicFile
        
        global $sugar_config, $theme, $current_user, $sugar_version, $sugar_flavor, $mod_strings, $app_strings, $app_list_strings, $action, $timezones;
        global $gridline, $request_string, $modListHeader, $dashletData, $authController, $locale, $currentModule, $import_bean_map, $image_path, $license;
        global $user_unique_key, $server_unique_key, $barChartColors, $modules_exempt_from_availability_check, $dictionary, $current_language, $beanList, $beanFiles, $sugar_build, $sugar_codename;
        global $timedate, $login_error; // cn: bug 13855 - timedate not available to classic views.
        $currentModule = $this->module;
        require_once ($file);
       
        // END DUPLICATE FROM SugarView::includeClassicFile
        
        if (is_object($sqc)) {
            $this->_sqc = get_class($sqc);
        }            
    }    
}
