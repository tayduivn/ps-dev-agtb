<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once('modules/Trackers/store/Store.php');

class TrackerSessionsDatabaseStore implements Store
{
    public function flush($monitor)
    {
        $db = DBManagerFactory::getInstance();

        $values = array();
        $metrics = $monitor->getMetrics();
        foreach ($metrics as $name => $metric) {
            if (isset($monitor->$name)) {
                $values[$name] = $db->quoteType($metrics[$name]->_type, $monitor->$name);
            }
        }

        if (empty($values)) {
            return;
        }

        if ($monitor->new === true) {
            if ($db->supports("auto_increment_sequence")) {
                $values[] = $db->getAutoIncrementSQL($monitor->table_name, 'id');
                $columns[] = 'id';
            }

            $query = "INSERT INTO
                      $monitor->table_name (" . implode(",", array_keys($values)) . ")
                      VALUES (" . implode(",", $values) . ')';
            $db->query($query);
        } else {
            $query = "UPDATE $monitor->table_name SET";

            $set = array();
            foreach ($values as $key => $value) {
                $set[] = " $key = $value ";
            }
            $query .= implode(",", $set);
            $query .= "WHERE session_id = '{$monitor->session_id}'";

            $GLOBALS['db']->query($query);
        }
    }
}
