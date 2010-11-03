<?php

define('sugarEntry', true);
chdir('..');
require_once('include/entryPoint.php');


	function updateSubscriptionOrderChange($account_id, $order_number){
		// The order number has changed. Now we check to see if we can associate a new subscription with the parent account.
		$subscription = file_get_contents("http://www.sugarcrm.com/crm/get_subscription.php?key=934djasd81fDFefads34c234&order_id={$order_number}");

		// There was a subscription found on sugarcrm.com
		if(!empty($subscription)){
			require_once('modules/Accounts/Account.php');
			$account = new Account();
			$account->disable_row_level_security = true;
			$account->retrieve($account_id);

			// We were successfully able to retrieve the account associated with this opportunity
			if(!empty($account->id)){
				$sub_query = "select id from subscriptions where subscription_id = '{$subscription}' and deleted = 0";
				$res = $GLOBALS['db']->query($sub_query);
				$row = $GLOBALS['db']->fetchByAssoc($res);

				// We found the subscription in the database
				if($row){
					require_once('modules/Subscriptions/Subscription.php');
					$subscription = new Subscription();
					$subscription->disable_row_level_security = true;
					$subscription->retrieve($row['id']);

					// We now associate this subscription with the account
					if(!empty($subscription->id)){
						$account->load_relationship('subscriptions');
						$account->subscriptions->add($subscription->id);
						$account->update_date_modified = false;
						$account->update_modified_by = false;
						if(!empty($account->description)){
							$account->description .= "\n\n";
						}
						$account->description .= "Sadek Script: Auto added subscription {$subscription->subscription_id} to this account based on order number {$order_number}";
						$account->save(false);
					}
				}
			}
		}
	}

$query=	"select accounts.id, opportunities_cstm.order_number ".
		"from accounts left join subscriptions on accounts.id = subscriptions.account_id ".
			"inner join accounts_opportunities on accounts.id = accounts_opportunities.account_id and accounts_opportunities.deleted = 0 ".
			"inner join opportunities on accounts_opportunities.opportunity_id = opportunities.id and opportunities.deleted = 0 ".
			"inner join opportunities_cstm on opportunities.id = opportunities_cstm.id_c ".
		"where subscriptions.id is null and accounts.deleted = 0 and opportunities.sales_stage = 'Finance Closed' and ".
			"opportunities_cstm.order_number is not null and opportunities_cstm.order_number != ''";

$res = $GLOBALS['db']->query($query);
while($row = $GLOBALS['db']->fetchByAssoc($res)){
	updateSubscriptionOrderChange($row['id'], $row['order_number']);
}
