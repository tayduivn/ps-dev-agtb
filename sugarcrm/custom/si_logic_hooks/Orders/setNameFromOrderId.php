<?php

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 3
 * Sets the Order Name to the Order Id so triggers and search work.
 */

class setNameFromOrderId
{

    function set(&$bean, $event, $arguments)
    {
        /*if ($event != "after_save") return false;

        if (empty($bean->name)) {
            // SINCE order_id is an auto_increment it doesn't exist in the bean so you gotta hit the db to get it
            $db = &DBManagerFactory::getInstance();
            $result = $db->query("SELECT order_id FROM orders WHERE id = '{$bean->id}'");
            while ($row = $db->fetchByAssoc($result)) {
                $order_id = $row['order_id'];
            }
            $bean->name = $order_id;
            $bean->save();
        }

        return true;*/
    }
}

