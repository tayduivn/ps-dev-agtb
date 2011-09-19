<?php

require_once('include/MVC/View/views/view.ajax.php');
require_once('include/EditView/EditView2.php');


class CalendarViewAjaxLoadForm extends SugarView {

	//var $_isDCForm = false;
	var $ev;
	var $editable;
	
	
	public function preDisplay(){
		global $beanFiles,$beanList;
		$module = $_REQUEST['current_module'];
		require_once($beanFiles[$beanList[$module]]);
		$this->bean = new $beanList[$module]();
		if(!empty($_REQUEST['record']))
			$this->bean->retrieve($_REQUEST['record']);
			
		if(!$this->bean->ACLAccess('DetailView')) {
			$json_arr = array(
				'success' => 'no',
			);
			echo json_encode($json_arr);
			die;	
		}

		if($this->bean->ACLAccess('Save')){
			$this->editable = 1;
		}else{
			$this->editable = 0;
		}		
    
	}
	
	public function display(){
		require_once("modules/Calendar/utils.php");
		
		$module = $_REQUEST['current_module'];
		
		$_REQUEST['module'] = $module;
				
		$base = 'modules/' . $module . '/metadata/';
		$source = 'custom/'.$base.'quickcreatedefs.php';
		if (!file_exists($source)){
			$source = $base . 'quickcreatedefs.php';
			if (!file_exists($source)){
				$source = 'custom/' . $base . 'editviewdefs.php';
				if (!file_exists($source)){
					$source = $base . 'editviewdefs.php';
				}
			}
		}		
		
		$tpl = "custom/include/EditView/EditView.tpl";	
		if(!file_exists($tpl))
			$tpl = "include/EditView/EditView.tpl";	
		$this->ev = new EditView();
		$this->ev->view = "QuickCreate";
		$this->ev->ss = new Sugar_Smarty();
		$this->ev->formName = "CalendarEditView";
		$this->ev->setup($module,$this->bean,$source,$tpl);
		$this->ev->defs['templateMeta']['form']['headerTpl'] = "modules/Calendar/tpls/empty.tpl";
		$this->ev->defs['templateMeta']['form']['footerTpl'] = "modules/Calendar/tpls/empty.tpl";						
		$this->ev->process(false, "CalendarEditView");		
		
		if(!empty($this->bean->id)){
			require_once('include/json_config.php');
			global $json;
			$json = getJSONobj();
			$json_config = new json_config();
			$GRjavascript = $json_config->getFocusData($module, $this->bean->id);
        	}else{
        		$GRjavascript = "";
        	}	
	
		$json_arr = array(
				'success' => 'yes',
				'module_name' => $this->bean->module_dir,
				'record' => $this->bean->id,
				'editview' => $this->editable,
				'html'=> $this->ev->display(false, true),
				'gr' => $GRjavascript,
		);
			
		ob_clean();		
		echo json_encode($json_arr);
	}
}

?>
