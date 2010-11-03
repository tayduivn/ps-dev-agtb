<?php
/*
** @author: Jon Whitcraft
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 15092
** Description:ITR #12893 addressed moving DL keys to disable on the expiration date. We now need to have the system do the following
** when ALL DL keys are disabled for the customer. (It's possible that a customer could have more than one DL key).
*/
class subscriptionAccountUpdate
{
    public function update(&$bean, $event, $arguments)
    {

        global $current_user;
        if(empty($current_user->id)) {
            $current_user->getSystemUser();
        }

        // Only run this hook when the event is after_save and the bean status is disabled
        if($event == "after_save" && $bean->status == "disabled") {

            // pull in any enabled subscriptions for the current account
            $active_subscriptions = "SELECT subscription_id FROM subscriptions WHERE status = 'enabled'
                    and account_id = '" . $bean->account_id . "' and deleted = '0'";
            $res = $GLOBALS['db']->query($active_subscriptions);

            // if there are not enabled subscriptions then we need to modify the account
            if($GLOBALS['db']->getRowCount($res) == 0) {
                // now we update the account that is associated to the account
                $account = new Account();
                $ret = $account->retrieve($bean->account_id);
                
                // set the support service level to "no_support"
                $account->support_service_level_c = "no_support";
                // set the account_type to past customers
                $account->account_type = "Past Customer";

                // return all the contacts for the given account
                $contacts = $account->get_contacts();

                // loop though the contacts and if they have portal access disable it!
                foreach($contacts as $contact) {
                    if($contact->portal_active == 1) {
                        $contact->portal_active = 0;
                        $contact->save();
                    }
                }

                // save the account changes
                $account->save();
            }
        }
    }
}

