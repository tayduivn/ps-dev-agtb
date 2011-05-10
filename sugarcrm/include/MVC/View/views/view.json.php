<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
class ViewJson extends SugarView{
	var $type ='detail';
	function ViewJson(){
 		parent::SugarView();
 	}
 	
	function display(){
 		global $beanList;
		$module = $GLOBALS['module'];
		$json = getJSONobj();
		$bean = $this->bean;
		$all_fields = array_merge($bean->column_fields,$bean->additional_column_fields);
		
		$js_fields_arr = array();
		foreach($all_fields as $field) {
			if(isset($bean->$field)) {
				$bean->$field = from_html($bean->$field);
				$bean->$field = preg_replace('/\r\n/','<BR>',$bean->$field);
				$bean->$field = preg_replace('/\n/','<BR>',$bean->$field);
				$js_fields_arr[$field] = addslashes($bean->$field);
			}
		}
		$out = $json->encode($js_fields_arr, true);
		ob_clean();
		print($out);
		sugar_cleanup(true);
	}
}
?>
