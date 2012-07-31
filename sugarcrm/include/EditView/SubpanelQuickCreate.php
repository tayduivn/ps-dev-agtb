<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 * $Id: additionalDetails.php 13782 2006-06-06 17:58:55Z majed $
 *********************************************************************************/
require_once('include/EditView/EditView2.php');
/**
 * Quick create form in the subpanel
 * @api
 */
class SubpanelQuickCreate{
    public $defaultProcess = true;

    /**
     * The view type to use
     *
     * @var string
     */
    public $viewType = 'QuickCreate';

    public function SubpanelQuickCreate($module, $view='QuickCreate', $proccessOverride = false)
    {
        $this->viewType = $view;

        //treat quickedit and quickcreate views as the same
        if($this->viewType == 'QuickEdit') {
            $this->viewType = 'QuickCreate';
        }

        // Get the viewdefs source file, called here to ensure proper viewType setting
        $source = $this->getModuleViewDefsSourceFile($module, $this->viewType);

		// locate the best viewdefs to use: 1. custom/module/quickcreatedefs.php 2. module/quickcreatedefs.php 3. custom/module/editviewdefs.php 4. module/editviewdefs.php
		$base = 'modules/' . $module . '/metadata/';
		$source = 'custom/' . $base . strtolower($view) . 'defs.php';
		if (!file_exists( $source))
		{
			$source = $base . strtolower($view) . 'defs.php';
			if (!file_exists($source))
			{
				//if our view does not exist default to EditView
				$view = 'EditView';
				$source = 'custom/' . $base . 'editviewdefs.php';
				if (!file_exists($source))
				{
					$source = $base . 'editviewdefs.php';
				}
			}
		}

        $this->ev = $this->getEditView();
		$this->ev->view = $this->viewType;
		$this->ev->ss = new Sugar_Smarty();
		//$_REQUEST['return_action'] = 'SubPanelViewer';

        $class = $GLOBALS['beanList'][$module];
        $bean = new $class();
        if(!empty($_REQUEST['record'])) {
            $bean->retrieve($_REQUEST['record']);
        }
		$this->ev->setup($module, $bean, $source);
		unset($bean);


		// Bug 49219 - Check empty before set defaults, or the settings from viewdefs above will be overridden.
        if (!isset($this->ev->defs['templateMeta']['form']['headerTpl']))
        {
            $this->ev->defs['templateMeta']['form']['headerTpl'] = 'include/EditView/header.tpl';
        }

		if (!isset($this->ev->defs['templateMeta']['form']['footerTpl']))
        {
            $this->ev->defs['templateMeta']['form']['footerTpl'] = 'include/EditView/footer.tpl';
        }
		// Comment below, breaks many out of the box viewdefs
		/*if (empty($this->ev->defs['templateMeta']['form']['buttons'])) $this->ev->defs['templateMeta']['form']['buttons'] = array('SUBPANELSAVE', 'SUBPANELCANCEL', 'SUBPANELFULLFORM');*/
		$this->ev->defs['templateMeta']['form']['buttons'] = array('SUBPANELSAVE', 'SUBPANELCANCEL', 'SUBPANELFULLFORM');

        //Load the parent view class if it exists.  Check for custom file first
        loadParentView('edit');

		$viewEditSource = 'modules/'.$module.'/views/view.edit.php';
		if (file_exists('custom/'. $viewEditSource)) {
			$viewEditSource = 'custom/'. $viewEditSource;
		}

		if(file_exists($viewEditSource) && !$proccessOverride) {
            include($viewEditSource);
            $c = $module . 'ViewEdit';

            $customClass = 'Custom' . $c;
            if(class_exists($customClass)) {
                $c = $customClass;
            }

            if(class_exists($c)) {
	            $view = new $c;
	            if($view->useForSubpanel) {
	            	$this->defaultProcess = false;

	            	// Check if we should use the module's QuickCreate.tpl file.
	            	if($view->useModuleQuickCreateTemplate && file_exists('modules/'.$module.'/tpls/QuickCreate.tpl')) {
	            	   $this->ev->defs['templateMeta']['form']['headerTpl'] = 'modules/'.$module.'/tpls/QuickCreate.tpl';
	            	}

		            $view->ev = & $this->ev;
		            $view->ss = & $this->ev->ss;
					$class = $GLOBALS['beanList'][$module];
					if(!empty($GLOBALS['beanFiles'][$class])){
						require_once($GLOBALS['beanFiles'][$class]);
						$bean = new $class();
						$view->bean = $bean;
					}
					$this->ev->formName = 'form_Subpanel'.$this->ev->view .'_'.$module;
					$view->showTitle = false; // Do not show title since this is for subpanel
		            $view->display();
	            }
            }
		} //if

		if($this->defaultProcess && !$proccessOverride) {
		   $this->process($module);
		}
	}

	function process($module){
        if($_REQUEST['target_action'] == 'QuickCreate'){
            $this->ev->view = 'QuickCreate';
        }
        $form_name = 'form_Subpanel'.$this->ev->view .'_'.$module;
        $this->ev->formName = $form_name;
        $this->ev->process(true, $form_name);
		echo $this->ev->display(false, true);
	}

    /**
     * Get EditView object
     * @return EditView
     */
    protected function getEditView()
    {
        return new EditView();
    }


    /**
     * Finds and returns the best viewdefs to use:
     *  1. custom/module/quickcreatedefs.php
     *  2. module/quickcreatedefs.php
     *  3. custom/module/editviewdefs.php
     *  4. module/editviewdefs.php
     *
     * @param $module
     * @param $view
     * @return string The path to the viewdefs file to use
     */
    public function getModuleViewDefsSourceFile($module, $view) {
        $base = 'modules/' . $module . '/metadata/';
		$source = 'custom/' . $base . strtolower($view) . 'defs.php';
		if (!file_exists($source)) {
			$source = $base . strtolower($view) . 'defs.php';
			if (!file_exists($source)) {
				//if our view does not exist default to EditView
				$this->viewType = 'EditView';
				$source = 'custom/' . $base . 'editviewdefs.php';
				if (!file_exists($source)) {
					$source = $base . 'editviewdefs.php';
				}
			}
		}

        return $source;
    }
}
