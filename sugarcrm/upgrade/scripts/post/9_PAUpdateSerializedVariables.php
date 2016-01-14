<?php
//FILE SUGARCRM flav=ent ONLY
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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

/**
 * Upgrader to handle conversion of PHP serialized data from 7.6.1 and below into
 * JSON encoded data for 7.6.2 and above.
 * 
 * NOTE: This upgrader should be run on an environment with an encoding that is
 * the same as the system in which the serializing took place, otherwise
 * unserialize may not work as expected all the time.
 *
 * NOTE: This upgrader has a preflight mechanism that is consumed by HealthCheck.
 * It is designed to detect inconsistencies in system encoding as well issues
 * with unserializable data.
 */
class SugarUpgradePAUpdateSerializedVariables extends UpgradeScript
{
    /**
     * {@inheritdoc }
     * @var int
     */
    public $order = 9010;

    /**
     * {@inheritdoc }
     * @var int
     */
    public $type = self::UPGRADE_DB;

    /**
     * Listing of methods that are run during the unserialize upgrade. At a
     * minimum this requires a task name that maps to a table name and columns.
     * @var array
     */
    protected $upgradeTasks = array(
        'lockedVariables' => array(
            'table' => 'pmse_bpm_process_definition',
            'cols' => array('pro_locked_variables'),
            'encode' => true,
        ),
        'casDataVariables' => array(
            'table' => 'pmse_bpm_case_data',
            'cols' => array('cas_data'),
        ),
        'bpmFormActionTable' => array(
            'table' => 'pmse_bpm_form_action',
            'cols' => array('cas_data', 'cas_pre_data'),
        ),
        'dynamicFormTable' => array(
            'table' => 'pmse_bpm_dynamic_forms',
            'cols' => array('dyn_view_defs'),
            'functions' => array('base64_decode'),
        ),
        'bpmFlowTable' => array(
            'table' => 'pmse_bpm_flow',
            'cols' => array('cas_adhoc_actions'),
        ),
    );

    /**
     * Convert value from PHP serialized to JSON
     * @param $input
     * @param $encode
     * @return string
     */
    protected function convertSerializedData($input, $encode = false)
    {
        // Since we need to work on html decoded data, get that now
        $decoded = html_entity_decode($input);

        // Clean unserialize the input
        $unserialized = \Sugarcrm\Sugarcrm\Security\InputValidation\Serialized::unserialize($decoded);

        // If the unserialize failed for some reason, log it and return the
        // original data
        if ($unserialized === false) {
            $this->log("unserialize failed on:\n" . print_r($decoded, 1));
            return $input;
        }

        // We will always need this, so get it now
        $encoded = json_encode($unserialized);

        // If for some reason the json_encode failed, log that now and return the
        // original data
        if ($encoded === false) {
            $this->log("json_encode failed on:\n" . print_r($unserialized, 1));
            return $input;
        }

        // Return what is needed
        return $encode ? htmlentities($encoded) : $encoded;
    }

    /**
     * Handles the actual updating of tables with updated data
     * @param array $data Array of params that are used to handle the udpate
     */
    protected function handleUpdate($data)
    {
        // Define our table
        $table = $data['table'];

        // Log out current position in this upgrader
        $this->log("About to handle serialized data conversion for $table");

        // Define the column(s) we will be working with.
        // This is always an array.
        //$cols = array('cas_data', 'cas_pre_data');
        $cols = $data['cols'];

        // Builds a simple list of selectable columns
        $selectCols = implode(',', $cols);

        // Builds a list of not empty SQL bits. Also builds the update cols SQL
        // strings.
        $whereCols = $updateCols = array();
        foreach ($cols as $col) {
            $whereCols[] = $this->db->getNotEmptyFieldSQL($col);
            $updateCols[$col] = "$col = %s";
        }
        $whereNotEmpty = implode(' AND ', $whereCols);

        // Build the query and run it
        $select = "SELECT id, %s FROM %s WHERE %s";
        $sql = sprintf($select, $selectCols, $table, $whereNotEmpty);
        $result = $this->db->query($sql);

        // Keep this for logging
        $c = 0;

        // Loop and update now, making sure to send a false flag to fetchByAssoc
        // to ensure that the data in the row does not get html encoded on fetch
        while ($row = $this->db->fetchByAssoc($result, false)) {
            // Set and add the id to the info array
            $id = $this->db->quoted($row['id']);

            // Build the update SQL data
            foreach ($updateCols as $col => $colSql) {
                // Isolate the actual data to be handled
                $string = $row[$col];

                // If there are functions to apply to this data, do that now
                if (isset($data['functions'])) {
                    foreach ($data['functions'] as $function) {
                        $string = $function($string);
                    }
                }

                // Now set the new data, quoting it for our DB
                $newData = $this->db->quoted($this->convertSerializedData($string), !empty($data['encode']));

                // And update the column update SQL strings
                $updateCols[$col] = sprintf($colSql, $newData);
            }

            // Build the update SQL and run it
            $sql = sprintf("UPDATE %s SET %s WHERE id = %s", $table, implode(',', $updateCols), $id);
            if (!$this->db->query($sql)) {
                $this->log("Unserialized data conversion update failed DB update:\n$sql");
            }

            $c++;
        }

        $this->log("Total updates for $table unserialized data conversion: $c");
    }

    /**
     * Triggers the actual conversions
     */
    protected function handleConversions()
    {
        foreach ($this->upgradeTasks as $data) {
            $this->handleUpdate($data);
        }
    }

    /**
     * Determines whether this upgrader should run
     * @return boolean
     */
    protected function shouldRun()
    {
        // Only run this id source is 7.6.0.0 or 7.6.1.0 and the target is
        // greater than 7.6.1.0
        $from = version_compare($this->from_version, '7.6.0.0', '==')
                || version_compare($this->from_version, '7.6.1.0', '==');
        $to = version_compare($this->to_version, '7.6.1.0', '>');
        return $from && $to;
    }

    /**
     * {@inheritdoc }
     */
    public function run()
    {
        if ($this->shouldRun()) {
            $this->handleConversions();
        }
    }
}
