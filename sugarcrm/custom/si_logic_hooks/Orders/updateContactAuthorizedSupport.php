<?php

/**
 * @author jwhitcraft
 * @project moofcart
 * @tasknum 50
 *
 * Update the contact Authorized support if the order status is
 */

require_once('custom/si_custom_files/MoofCartHelper.php');

class updateContactAuthorizedSupport
{
    function update(&$bean, $event, $arguments)
    {
        // only run if the event is after_save
        if ($event != "after_save") return false;

        // only run if the bean status is set to Completed
        if ($bean->status != "Completed") return false;


        $account = $bean->get_linked_beans('accounts_orders', 'Account', array(), 0, 1);
        /**
         * @var $account Account
         */
        $account = $account[0];

        $products = $bean->get_linked_beans('orders_products', 'Product');
        /**
         * This is used to set the support level if an order has one of the support levels
         *
         * @var $product Product
         */
        foreach($products as $product) {
            switch($product->product_template_id) {
                case "5922bad2-a760-3456-9a7c-4c4eef5c3957":
                    $account->Support_Service_Level_c = "premium";
                    $account->save(false);
                    break 2;
                case "554ceb22-8c4e-f225-b62c-4c4eef6ff9b0": // extended - annual
                case "bd5bc400-042b-7373-3ef3-4c4ee8392337": // extended - 90 day
                    $account->Support_Service_Level_c = "extended";
                    $account->save(false);
                    break 2;
                default:
                    break;
            }
        }


        switch ($account->Support_Service_Level_c) {
            case 'standard':
            case 'extended':
                // only allow two
                $contact = $bean->get_linked_beans('contacts_orders', 'Contact', array(), 0, -1);
                $contact = $contact[0];

                $a_contacts = $account->get_linked_beans('contacts', 'Contact', array(), 0, -1);

                $maxSupportUsers = MoofCartHelper::$supportTypeMaxUsers[$account->Support_Service_Level_c];
                $x = 0;
                foreach ($a_contacts as $a_c) {
                    if ($a_c->support_authorized_c == 1) {
                        $x++;
                    }
                    if ($a_c->id == $contact->id) {
                        // we already have this users
                        // lets make sure this users
                        if ($x < $maxSupportUsers) {
                            $contact->support_authorized_c = 1;
                            $contact->save(false);
                            $x++;
                        }
                    }
                    if ($x == $maxSupportUsers) {
                        break;
                    }
                }

                if ($x > $maxSupportUsers) {
                    $contact->support_authorized_c = 1;
                    $contact->save(false);
                }

                return true;
                break;
            case 'premium':
                // only allow four
                // only allow two
                $contact = $bean->get_linked_beans('contacts_orders', 'Contact', array(), 0, -1);

                $a_contacts = $account->get_linked_beans('contacts', 'Contact', array(), 0, -1);

                $maxSupportUsers = MoofCartHelper::$supportTypeMaxUsers[$account->Support_Service_Level_c];
                $x = 0;
                foreach ($a_contacts as $a_c) {
                    if ($a_c->support_authorized_c == 1) {
                        $x++;
                    }
                    if ($a_c->id == $contact->id) {
                        // we already have this users
                        // lets make sure this users
                        if ($x < $maxSupportUsers) {
                            $contact->support_authorized_c = 1;
                            $contact->save(false);
                            $x++;
                        }
                    }
                    if ($x == $maxSupportUsers) {
                        break;
                    }
                }

                if ($x > $maxSupportUsers) {
                    $contact->support_authorized_c = 1;
                    $contact->save(false);
                }

                return true;
                break;
            default:
                // nothing
                break;
        }
        return false;
    }
}

