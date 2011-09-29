<?php

class CalendarController extends SugarController {


	function action_AjaxSave(){
		$this->view = 'ajaxsave';
	}
		
	function action_AjaxLoad(){
		$this->view = 'ajaxload';
	}
	
	function action_AjaxReschedule(){
		$this->view = 'ajaxreschedule';
	}
	
	function action_AjaxRemove(){
		$this->view = 'ajaxremove';
	}
	
	function action_AjaxGetGR(){
		$this->view = 'ajaxgetgr';
	}
	
	function action_AjaxGetGRUsers(){
		$this->view = 'ajaxgetgrusers';
	}
	
	function action_AjaxLoadForm(){
		$this->view = 'ajaxloadform';
	}
	
	function action_SaveSettings(){
		$this->view = 'savesettings';
	}
	
}

?>