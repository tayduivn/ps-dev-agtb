<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
/*
 * Created on Apr 13, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once('include/EditView/EditView2.php');
 class ViewEdit extends SugarView{
 	var $ev;
 	var $type ='edit';
 	var $useForSubpanel = false;  //boolean variable to determine whether view can be used for subpanel creates
 	var $useModuleQuickCreateTemplate = false; //boolean variable to determine whether or not SubpanelQuickCreate has a separate display function
 	var $showTitle = true;

 	function ViewEdit(){
 		parent::SugarView();
 	}

 	function preDisplay(){
		
		$this->removeDisabledTabsFromParentType();
		
		$metadataFile = $this->getMetaDataFile();
 		$this->ev = new EditView();
 		$this->ev->ss =& $this->ss;
 		$this->ev->setup($this->module, $this->bean, $metadataFile, 'include/EditView/EditView.tpl');

 	}

 	function display(){
		$this->ev->process();
		echo $this->ev->display($this->showTitle);
 	}

	function removeDisabledTabsFromParentType(){
        require_once("modules/MySettings/TabController.php");
        $controller = new TabController();
        $tabs = $controller->get_tabs_system();
		$enabled = $tabs[0];

		// In the next if blocks, we add back in modules that are categories of other modules, to avoid removing them unnecessarily
		if(isset($enabled['Project'])){
			$enabled['ProjectTask'] = 'ProjectTask';
		}
		
		if(isset($enabled['Activities'])){
			$enabled['Calls'] = 'Calls';
			$enabled['Meetings'] = 'Meetings';
			$enabled['Tasks'] = 'Tasks';
			$enabled['Notes'] = 'Notes';
			$enabled['Emails'] = 'Emails';
		}
		
		if(isset($enabled['Campaigns'])){
			$enabled['Prospects'] = 'Prospects';
		}
		
		if(isset($enabled['Products'])){
			$enabled['ProductTemplates'] = 'ProductTemplates';
		}
		
		// Remove them from the parent_type_display options, which are used in Calls, Meetings, and Tasks
		global $app_list_strings;
		foreach($app_list_strings['parent_type_display'] as $key => $val){
			if(!in_array($key, $enabled)){
				unset($app_list_strings['parent_type_display'][$key]);
			}
		}

		// Remove them from the record_type_display_notes options, which are used in Notes
		foreach($app_list_strings['record_type_display_notes'] as $key => $val){
			if(!in_array($key, $enabled)){
				unset($app_list_strings['record_type_display_notes'][$key]);
			}
		}
	}
 }
?>
