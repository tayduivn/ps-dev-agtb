<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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

 ********************************************************************************/

require_once('include/MVC/Controller/SugarController.php');
class ConfiguratorController extends SugarController
{
    //BEGIN SUGARCRM flav!=sales ONLY
    /**
     * Go to the font manager view
     */
    function action_FontManager(){
        $this->view = 'fontmanager';
    }
    
    /**
     * Delete a font and go back to the font manager
     */
    function action_deleteFont(){
        global $current_user;
        if(!is_admin($current_user)){
            sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN']); 
        }
        $urlSTR = 'index.php?module=Configurator&action=FontManager';
        if(!empty($_REQUEST['filename'])){
            require_once('include/Sugarpdf/FontManager.php');
            $fontManager = new FontManager();
            $fontManager->filename = $_REQUEST['filename'];
            if(!$fontManager->deleteFont()){
                $urlSTR .='&error='.urlencode(implode("<br>",$fontManager->errors));
            }
        }
        header("Location: $urlSTR");
    }
    
    function action_listview(){
    	$this->view = 'edit';
    }
    /**
     * Show the addFont view
     */
    function action_addFontView(){
        $this->view = 'addFontView';
    }
    /**
     * Add a new font and show the addFontResult view
     */
    function action_addFont(){
        global $current_user, $mod_strings;
        if(!is_admin($current_user)){
            sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN']); 
        }
        if(empty($_FILES['pdf_metric_file']['name'])){
            $this->errors[]=translate("ERR_MISSING_REQUIRED_FIELDS")." ".translate("LBL_PDF_METRIC_FILE", "Configurator");
            $this->view = 'addFontView';
            return;
        }
        if(empty($_FILES['pdf_font_file']['name'])){
            $this->errors[]=translate("ERR_MISSING_REQUIRED_FIELDS")." ".translate("LBL_PDF_FONT_FILE", "Configurator");
            $this->view = 'addFontView';
            return;
        }
        $path_info = pathinfo($_FILES['pdf_font_file']['name']);
        $path_info_metric = pathinfo($_FILES['pdf_metric_file']['name']);
        if(($path_info_metric['extension']!="afm" && $path_info_metric['extension']!="ufm") || 
        ($path_info['extension']!="ttf" && $path_info['extension']!="otf" && $path_info['extension']!="pfb")){
            $this->errors[]=translate("JS_ALERT_PDF_WRONG_EXTENSION", "Configurator");
            $this->view = 'addFontView';
            return;
        }
        
        if($_REQUEST['pdf_embedded'] == "false"){
            if(empty($_REQUEST['pdf_cidinfo'])){
                $this->errors[]=translate("ERR_MISSING_CIDINFO", "Configurator");
                $this->view = 'addFontView';
                return;
            }
            $_REQUEST['pdf_embedded']=false;
        }else{
            $_REQUEST['pdf_embedded']=true;
            $_REQUEST['pdf_cidinfo']="";
        }
        if(empty($_REQUEST['pdf_patch'])){
            $_REQUEST['pdf_patch']="return array();";
        }else{
            $_REQUEST['pdf_patch']="return {$_REQUEST['pdf_patch']};";
        }
        $this->view = 'addFontResult';
    }
    //END SUGARCRM flav!=sales ONLY
    function action_saveadminwizard()
    {
        $focus = new Administration();
        $focus->retrieveSettings();
        $focus->saveConfig();
        
        $configurator = new Configurator();
        $configurator->populateFromPost();
        $configurator->handleOverride();
	    $configurator->parseLoggerSettings();
        $configurator->saveConfig();
        
        SugarApplication::redirect('index.php?module=Users&action=Wizard&skipwelcome=1');
    }
    
    function action_saveconfig()
    {
        $configurator = new Configurator();
        $configurator->saveConfig();
        
        $focus = new Administration();
        $focus->saveConfig();
        
        // Clear the Contacts file b/c portal flag affects rendering
        if (file_exists($GLOBALS['sugar_config']['cache_dir'].'modules/Contacts/EditView.tpl'))
           unlink($GLOBALS['sugar_config']['cache_dir'].'modules/Contacts/EditView.tpl');
        
        SugarApplication::redirect('index.php?module=Administration&action=index');
	}
	
	function action_detail()
    {
        $this->view = 'edit';
    }
}
