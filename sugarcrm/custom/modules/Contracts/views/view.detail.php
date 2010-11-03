<?php
require_once('include/json_config.php');
require_once('include/MVC/View/views/view.detail.php');

class ContractsViewDetail extends ViewDetail {


 	function ContractsViewDetail(){
 		parent::ViewDetail();
 	}

 	/**
 	 * display
 	 *
 	 * We are overridding the display method to manipulate the portal information.
 	 * If portal is not enabled then don't show the portal fields.
 	 */
 	function display() {

 			$accountLink = $this->bean->load_relationship('accounts');
 			$account_ids = $this->bean->accounts->get();
 			$type = '';
 			if(!empty($account_ids)){
 				require_once('modules/Accounts/Account.php');
 				$acc = new Account();
 				$acc->retrieve($account_ids[0]);
 				$type = $acc->account_type;	
 			}
           $this->ss->assign('ACCT_TYPE', $type);
 		parent::display();
 	}
}

?>
