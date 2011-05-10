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
require_once('include/DetailView/DetailView2.php');

class ViewDetail extends SugarView{
	var $type ='detail';
	var $dv;
	
 	function ViewDetail(){
 		$this->options['show_subpanels'] = true;
 		parent::SugarView();
 	}

 	function preDisplay(){
 		
        $metadataFile = $this->getMetaDataFile();
		$this->dv = new DetailView2();
		$this->dv->ss =&  $this->ss;
		$this->dv->setup($this->module, $this->bean, $metadataFile, 'include/DetailView/DetailView.tpl'); 		
 	} 	
 	
 	function display(){
		if(empty($this->bean->id)){
			global $app_strings;
			sugar_die($app_strings['ERROR_NO_RECORD']);
		}				
		$this->dv->process();
		echo $this->dv->display();
 	}

}
