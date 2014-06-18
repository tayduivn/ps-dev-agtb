<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */


class SugarUpgradeOpportunityUpdateDateClosed extends UpgradeScript
{
    /**
     * When to run the upgrade task
     *
     * @var int
     */
    public $order = 7030;

    /**
     * Type of Upgrade Task
     *
     * @var int
     */
    public $type = self::UPGRADE_CUSTOM;

    /**
     * Upgrade Task to Run
     */
    public function run()
    {
        if (version_compare($this->from_version, '7.0.0', '<') && ($this->toFlavor('ent') || $this->toFlavor('ult'))) {

            $filename = 'custom/Extension/modules/Opportunities/Ext/Vardefs/sugarfield_date_closed.php';

            if(!is_file($filename)) {
                return;
            }

            require($filename);

            if (!empty($dictionary['Opportunity']['fields'])) {

                $fileString = file_get_contents($filename);

                // PAT-584, need to set the field Expected Close Date to false when upgrade because:
                // In 6.7, the field Expected Close Date is Required and no formula associated out of box.
                // In 7, the field Expected Close Date is Not Required and there's a formula associated out of box.
                // So use steps from PAT-584, it results in a Required field with a formula associated.
                if (isset($dictionary['Opportunity']['fields']['date_closed']['required']) &&
                    $dictionary['Opportunity']['fields']['date_closed']['required'] == true) {
                    $this->log("Change Opportunity field date_closed to not required");
                    $fileString = preg_replace('/(\$dictionary\[\'Opportunity\'\]\[\'fields\'\]\[\'date_closed\'\]\[\'required\'\]\s*=\s*)true\s*;/',
                        '${1}false;',
                        $fileString);

                    sugar_file_put_contents_atomic($filename, $fileString);
                }
            }
        }
    }
}
