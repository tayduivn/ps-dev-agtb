<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: EditView.php 18703 2006-12-15 09:42:43Z majed $

//FILE SUGARCRM flav=ent ONLY

require_once('modules/ModuleBuilder/views/view.listview.php');

class ViewPortalListView extends ViewListView 
{
    function __construct()
    {
        $this->editModule = $_REQUEST['view_module'];
        $this->editLayout = $_REQUEST['view'];
        $this->subpanel = (!empty($_REQUEST['subpanel'])) ? $_REQUEST['subpanel'] : false;
        $this->fromModuleBuilder = ! empty ( $_REQUEST [ 'view_package' ] ) ;
    }

    /**
	 * @see SugarView::_getModuleTitleParams()
	 */
	protected function _getModuleTitleParams()
	{
	    global $mod_strings;
	    
    	return array(
    	   translate('LBL_MODULE_NAME','Administration'),
    	   $mod_strings['LBL_MODULEBUILDER'],
    	   );
    }

    function display() 
    {
        require_once('modules/ModuleBuilder/parsers/ParserFactory.php');
        $parser = ParserFactory::getParser("PortalListView",$this->editModule);

        $parser->init($this->editModule, $this->subpanel);
        $smarty = $this->constructSmarty($parser);
        $smarty->assign('fromPortal',true); // flag for form submittal - when the layout is submitted the actions are the same for layouts and portal layouts, but the parsers must be different...
        //Override the list view buttons to remove references to the history feature as the portal editors do not support it.
        $buttons = array ( 
            array ( 
                'id' =>'savebtn', 
                'name' => 'savebtn', 
                'text' => translate('LBL_BTN_SAVEPUBLISH'), 
                'actionScript' => "onclick='studiotabs.generateGroupForm(\"edittabs\");" 
                    . "if (countListFields()==0) ModuleBuilder.layoutValidation.popup() ; else ModuleBuilder.handleSave(\"edittabs\" )'" 
            )
        ) ;
        $smarty->assign ( 'buttons', $this->_buildImageButtons ( $buttons ) ) ;
        
        
        $ajax = $this->constructAjax();
        $ajax->addSection('center', translate('LBL_EDIT_LAYOUT', 'ModuleBuilder'), $smarty->fetch("modules/ModuleBuilder/tpls/listView.tpl") );
        echo $ajax->getJavascript();

    }

    function constructAjax()
    {
        require_once('modules/ModuleBuilder/MB/AjaxCompose.php');
        $ajax = new AjaxCompose();

		$ajax->addCrumb(translate('LBL_SUGARPORTAL', 'ModuleBuilder'), 'ModuleBuilder.main("sugarportal")');
        $ajax->addCrumb(translate('LBL_LAYOUTS', 'ModuleBuilder'), 'ModuleBuilder.getContent("module=ModuleBuilder&action=wizard&portal=1&layout=1")');
  		$ajax->addCrumb(ucwords($this->editModule), 'ModuleBuilder.getContent("module=ModuleBuilder&action=wizard&portal=1&editModule='.$this->editModule.'")');
		$ajax->addCrumb(ucwords($this->editLayout), '');

        return $ajax;
    }
}
