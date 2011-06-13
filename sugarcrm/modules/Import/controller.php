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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: controller.php 31561 2008-02-04 18:41:10Z jmertic $
 * Description: Controller for the Import module
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/

require_once("modules/Import/Forms.php");
require_once("include/MVC/Controller/SugarController.php");

class ImportController extends SugarController
{
    /**
     * @see SugarController::loadBean()
     */
    public function loadBean()
    {
        global $mod_strings;
        
        $this->bean = loadBean($_REQUEST['import_module']);
        if ( $this->bean ) {
            if ( !$this->bean->importable )
                $this->bean = false;
            elseif ( $_REQUEST['import_module'] == 'Users' && !is_admin($GLOBALS['current_user']) )
                $this->bean = false;
            elseif ( $this->bean->bean_implements('ACL')){
                if(!ACLController::checkAccess($this->bean->module_dir, 'import', true)){
                    ACLController::displayNoAccess();
                    sugar_die('');
                }
            }
        }
        
        if ( !$this->bean ) {
            $_REQUEST['message'] = $mod_strings['LBL_ERROR_IMPORTS_NOT_SET_UP'];
            $this->view = 'error';
        }
        else
            $GLOBALS['FOCUS'] = $this->bean;
    }
    
    function action_index()
    {
        $this->action_Step1();
    }

    function action_RefreshMapping()
    {
        require_once('modules/Import/ImportFile.php');
        require_once('modules/Import/views/view.confirm.php');
        $v = new ImportViewConfirm();
        $fileName = $_REQUEST['importFile'];
        $delim = $_REQUEST['delim'];
        $enclosure = $_REQUEST['qualif'];
        $enclosure = html_entity_decode($enclosure, ENT_QUOTES);
        $hasHeader = isset($_REQUEST['header']) && !empty($_REQUEST['header']) ? TRUE : FALSE;

        $importFile = new ImportFile( $fileName, $delim, $enclosure, FALSE);
        $importFile->setHeaderRow($hasHeader);
        
        $rows = $v->getSampleSet($importFile);

        $ss = new Sugar_Smarty();
        $ss->assign("SAMPLE_ROWS",$rows);
        $ss->display('modules/Import/tpls/confirm_table.tpl');
        sugar_cleanup(TRUE);

    }
	function action_Step1()
    {
		$this->view = 'step1';
    }
    
    function action_Step2()
    {
		$this->view = 'step2';
    }

    function action_Confirm()
    {
		$this->view = 'confirm';
    }

    function action_Step3()
    {
		$this->view = 'step3';
    }
    
    function action_Step4()
    {
		$this->view = 'step4';
    }
    
    function action_Last()
    {
		$this->view = 'last';
    }
    
    function action_Undo()
    {
		$this->view = 'undo';
    }
    
    function action_Error()
    {
		$this->view = 'error';
    }
    
    function action_GetControl()
    {
        echo getControl($_REQUEST['import_module'],$_REQUEST['field_name']);
        exit;
    }
}
?>