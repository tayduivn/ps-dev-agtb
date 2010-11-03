<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.detail.php');

class OrdersViewDetail extends ViewDetail
{
    function OrdersViewDetail()
    {
        parent::ViewDetail();
    }

    function display()
    {
        global $app_list_strings;
        global $mod_strings;

        $this->dv->th->clearCache($this->module, 'DetailView.tpl');

        global $current_user;
	
        if($current_user->check_role_membership('Finance') || $current_user->check_role_membership('Sales Operations') || is_admin($current_user)) {
           $this->dv->ss->assign('button_flag','true'); 
        } else {
	  $this->dv->ss->assign('button_flag','false'); 
	}

        if(!$current_user->check_role_membership('IT')) {
            unset($this->dv->defs['panels']['LBL_WORKLOAD']);
        }

        parent::display();

    }
}
