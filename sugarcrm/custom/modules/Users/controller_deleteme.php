<?php


class UsersController extends SugarController{

	function UsersController(){
		parent::SugarController();
	}
	
	function action_login(){
		if (isset($_GET['mobile']) && $_GET['mobile'] == 1) {
			$this->view = 'login_mobile';
		} else {
			$this->view = 'login';
		}
	}
	
	function action_default() {
		if (isset($_GET['mobile']) && $_GET['mobile'] == 1) {
			$this->view = 'login_mobile';
		} else {
			$this->view = 'classic';
		}
	}

	function action_home_mobile() {
		$this->view = 'home_mobile';
	}
}	
?>
