<?php
//FILE SUGARCRM flav=ent ONLY
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
/**
 * Change locked fields to be a relate collection
 */
class SugarUpgradeLockedFieldsToRelateCollection extends UpgradeScript
{
    public $order = 9011;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if (version_compare($this->from_version, '7.8.0.0', '<')) {
            // We need this as the primary bean for the query
            $pd = \BeanFactory::getBean('pmse_BpmProcessDefinition');

            // We need this to join on for the record check
            $flow = \BeanFactory::newBean('pmse_BpmFlow');

            //Grab all records that have locked fields
            $q = new \SugarQuery();
            $q->select(
                array(
                    array('pd.id', 'pd_id'),
                    array('flow.cas_sugar_object_id', 'bean_id'),
                    array('flow.cas_sugar_module', 'bean_module'),
                )
            );

            $q->joinTable($flow->getTableName(), array('alias' => 'flow'))
                ->on()
                ->equalsField('flow.pro_id', 'pd.id');

            $q->from($pd, array('alias' => 'pd'));

            $q->where()
                ->notIn('flow.cas_flow_status', array('CLOSED', 'TERMINATED'))
                ->isNotEmpty('pd.pro_locked_variables');

            $rows = $q->execute('array', true);

            // Loop through and add the relationship
            foreach ($rows as $row) {
                $recordBean = BeanFactory::getBean($row['bean_module'], $row['bean_id']);
                $pd->retrieve($row['pd_id']);
                if ($recordBean->load_relationship('locked_fields_link')) {
                    if (!$recordBean->locked_fields_link->add($pd)) {
                        $this->log('Failed to create relationship for record: ' + $recordBean->id + ' pd: ' + $pd->id);
                    }
                }
            }
        }
    }
}
