<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.detail.php');

class BugsViewDetail extends ViewDetail {
	function BugsViewDetail(){
		parent::ViewDetail();
	}
	function display(){
		global $app_list_strings;
		global $mod_strings;
		
		$this->dv->th->clearCache($this->module, 'DetailView.tpl');
		if(isset($this->dv->focus->fix_proposed_c) && $this->dv->focus->fix_proposed_c == '0'){
			$found = false;
			foreach($this->dv->defs['panels'] as $panel_index => $panel_rows){
				if($found) break;
				foreach($panel_rows as $panel_row_index => $panel_field_arrays){
					if($found) break;
					foreach($panel_field_arrays as $panel_field_array_index => $panel_field_arr){
						if($found) break;
						if($panel_field_arr['name'] == 'contribution_agreement_c'){
							//print_r($this->dv->defs['panels'][$panel_index][$panel_row_index]);
							$this->dv->defs['panels'][$panel_index][$panel_row_index][$panel_field_array_index] = '';
							//echo "<PRE>\n"; print_r($this->dv->defs['panels']);echo "</PRE>";
							$found = true;
						}
					}
				}
			}
		}
		
		parent::display();
	}
}
