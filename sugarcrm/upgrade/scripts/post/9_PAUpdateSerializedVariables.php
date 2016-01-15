<?php
//FILE SUGARCRM flav=ent ONLY
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
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
     * Convert value form PHP serialized to JSON
     * @param $input
     * @param $encode
     * @return string
     */
    protected function convertSerializedData($input, $encode = false)
    {
        try {
            $decoded = \Sugarcrm\Sugarcrm\Security\InputValidation\Serialized::unserialize(html_entity_decode($input));
        } catch (Exception $e) {
            $this->log('Cannot unserialize value: \'' . $input . '\'. Error thrown: ' . $e->getMessage());
        }
        $converted = ($decoded != false) ? ($encode ? htmlentities(json_encode($decoded)) : json_encode($decoded)) : '';
        return $converted;
    }

    /**
     * Convert Serialized variables on Locked Fields
     */
    protected function fixLockedVariables()
    {
        $lockedFieldsQuery = "SELECT id, pro_locked_variables FROM pmse_bpm_process_definition";
        $result = $this->db->query($lockedFieldsQuery);
        while ($row = $this->db->fetchByAssoc($result)) {
            $bean = BeanFactory::getBean('pmse_BpmProcessDefinition', $row['id']);
            $bean->pro_locked_variables = $this->convertSerializedData($row['pro_locked_variables'], true);
            $bean->save();
        }
    }

    /**
     * Convert Serialized variables on cas_data of pmse_bpm_case_data table
     */
    protected function fixCasDataVariables()
    {
        $query = "SELECT id, cas_data FROM pmse_bpm_case_data";
        $result = $this->db->query($query);
        while ($row = $this->db->fetchByAssoc($result)) {
            $bean = BeanFactory::getBean('pmse_BpmCaseData', $row['id']);
            $bean->cas_data = $this->convertSerializedData($row['cas_data']);
            $bean->save();
        }
    }

    /**
     * Convert Serialized variables of pmse_bpm_form_action table
     */
    protected function fixBpm_form_actionTable()
    {
        $query = "SELECT id, cas_data, cas_pre_data FROM pmse_bpm_form_action";
        $result = $this->db->query($query);
        while ($row = $this->db->fetchByAssoc($result)) {
            $bean = BeanFactory::getBean('pmse_BpmFormAction', $row['id']);
            $bean->cas_data = $this->convertSerializedData($row['cas_data']);
            $bean->cas_pre_data = $this->convertSerializedData($row['cas_pre_data']);
            $bean->save();
        }
    }


    /**
     * Convert Serialized variables on cas_data of pmse_bpm_case_data table
     */
    protected function fixPADynamicFormTable()
    {
        $query = "SELECT id, dyn_view_defs FROM pmse_bpm_dynamic_forms";
        $result = $this->db->query($query);
        while ($row = $this->db->fetchByAssoc($result)) {
            $bean = BeanFactory::getBean('pmse_BpmDynaForm', $row['id']);
            $bean->dyn_view_defs = $this->convertSerializedData(base64_decode($row['dyn_view_defs']));
            $bean->save();
        }
    }

    /**
     * Convert Serialized variables on cas_data of pmse_bpm_case_data table
     */
    protected function fixPABpmFlowTable()
    {
        $query = "SELECT id, cas_adhoc_actions FROM pmse_bpm_flow";
        $result = $this->db->query($query);
        while ($row = $this->db->fetchByAssoc($result)) {
            $bean = BeanFactory::getBean('pmse_BpmFlow', $row['id']);
            $bean->cas_adhoc_actions = $this->convertSerializedData($row['cas_adhoc_actions']);
            $bean->save();
        }
    }

    /**
     * {@inheritdoc }
     */
    public function run()
    {
        // Only run this id source is 7.6.0.0 or 7.6.1.0 and the target is greater than 7.6.1.0
        if ((version_compare($this->from_version, '7.6.0.0', '==')
            || version_compare($this->from_version, '7.6.1.0', '=='))
            && version_compare($this->to_version, '7.6.1.0', '>')
        ) {
            $this->fixLockedVariables();
            $this->fixCasDataVariables();
            $this->fixBpm_form_actionTable();
            $this->fixPADynamicFormTable();
            $this->fixPABpmFlowTable();
        }
    }
}
