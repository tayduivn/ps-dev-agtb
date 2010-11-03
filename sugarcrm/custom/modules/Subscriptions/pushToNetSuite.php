<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class pushToNetSuite
{
    function push(&$bean, $event, $arguments)
    {
        if ($event != "before_save") return false;

        if (!is_null($bean->fetched_row['expiration_date']) && $bean->fetched_row['expiration_date'] != $bean->expiration_date) {

            $bean->load_relationship('orders');
            $order = array();

            $order['account_id'] = $bean->account_id;
            $order['order_id'] = $bean->orders_subef4esorders_ida;
            $order['new_expiration_date'] = $bean->expiration_date;
            $order['old_expiration_date'] = $bean->fetched_row['expiration_date'];
            $workload = serialize($order);

            require_once('custom/si_custom_files/MoofCartHelper.php');
            $server = MoofCartHelper::getGearmanServers();

            $client = new GearmanClient();
            $client->addServers($server);
            $client->doBackground('ns-subscription-change-task', $workload);

        }
    }

}
