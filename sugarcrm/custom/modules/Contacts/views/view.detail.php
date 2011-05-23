<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.detail.php');

class ContactsViewDetail extends ViewDetail {
        function ContactsViewDetail(){
                parent::ViewDetail();
        }
        function display(){
		if(!empty($this->dv->focus->account_status)){
			$this->dv->focus->account_status = $GLOBALS['app_list_strings']['account_status_options'][$this->dv->focus->account_status];
		}
                parent::display();
        }
}
