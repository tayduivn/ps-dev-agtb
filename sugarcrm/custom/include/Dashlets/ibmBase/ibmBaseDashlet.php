<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// START jvink - Simple List View Base Dashlet

require_once('include/Dashlets/Dashlet.php');
require_once('modules/Currencies/Currency.php');

class ibmBaseDashlet extends Dashlet {

	// local defs
	var $vardefs;
	var $listviewdefs;

	// dataset containing our rows
	var $dataset = array();
	
	// dataset to be displayed
	var $dataset_diplay = array();
	
    function ibmBaseDashlet($id, $def) {

    	// if no custom title, use default
        if(empty($def['title'])) {
        	$this->title = $this->dashletStrings['LBL_TITLE'];
        } else {
        	$this->title = $def['title'];        
        }
        
        $this->currency = new Currency();
        parent::Dashlet($id); // call parent constructor
    }

    function log($msg) {
    	$GLOBALS['log']->debug('SKY '.str_replace("\n",' ',$msg));
    }
    
    function generate_list_view() {

    	// prepare dataset for display
    	$this->prepare_display_dataset();
    	
    	// setup template
        $ss = new Sugar_Smarty();
        $ss->assign('id', $this->id);
        $ss->assign('listviewdefs', $this->listviewdefs);
        $ss->assign('vardefs', $this->vardefs);
        $ss->assign('dataset', $this->dataset);
        $ss->assign('dataset_display', $this->dataset_diplay);
        return $ss->fetch('custom/include/Dashlets/ibmBase/ibmBaseDashlet.tpl');     
    	
    }
    
    function add_row($query_result) {
    	
    	$row = array();
    	foreach($this->vardefs as $field => $field_def) {
    		if(isset($query_result[$field])) {
    			$row[$field] = $query_result[$field];
    		} else {
    			$row[$field] = null;
    		}
    	}
    	$this->dataset[] = $row;
    }
    
    // this function processes the listview in combination with vardef settings
    function prepare_display_dataset() {
		foreach($this->dataset as $id => $row) {    
		    foreach($this->listviewdefs as $col => $def) {
		    	switch($def['type']) {
		    		case 'link':
		    			$url = "index.php?module={$def['link_module']}&action={$def['link_type']}&record={$row[$def['link_id']]}";
		    			$value = '<a href="'.$url.'">'.$row[$col].'</a>';
		    			break;
		    		case 'icon':
		    			$img = '<img src="'.$def['icon'].'" border="0" />';
		    			$url = "index.php?module={$def['link_module']}&action={$def['link_type']}&record={$row[$def['link_id']]}";
		    			$value = '<a href="'.$url.'">'.$img.'</a>';
		    			break;
		    		case 'currency':
		    			$cur_param = array('currency_symbol' => true);
		    			$value = format_number($row[$col],null,null,$cur_param);
		    			break;
		    		case 'varchar':
		    		default:
		    			$value = $row[$col];
		    	}
		    	$this->dataset_diplay[$id][$col] = $value;
		    	
		    }
		}
		
		// column labels
		foreach($this->listviewdefs as $col => $def) {
	    	if(isset($this->dashletStrings[$def['label']])) {
	    		$this->listviewdefs[$col]['label'] = $this->dashletStrings[$def['label']];
	    	}
		}
    }
}

?>