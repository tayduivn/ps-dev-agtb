<?php


class OpportunitiesController extends SugarController{

	function OpportunitiesController(){
		parent::SugarController();
	}
	function action_ajaxformsave(){
		$this->pre_save();
		$this->bean->save();
		$this->view = 'multiedit';
	}
}	
?>
