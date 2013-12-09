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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

/**
 * Removes fields from the Opportunity Record View and SubPanel that are hidden in Enterprise
 */
class SugarUpgradeOpportunityRemoveHiddenEntFields extends UpgradeScript
{
    /**
     * When to run the upgrade task
     *
     * @var int
     */
    public $order = 7010;

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
        if (version_compare($this->from_version, '7.0.0', '<') && $this->fromFlavor('ent')) {
            $fields = array(
                'sales_stage',
                'probability',
                'commit_stage'
            );

            require_once('modules/ModuleBuilder/parsers/ParserFactory.php');
            $this->log('Processing Opportunity RecordView');
            $recordViewDefsParser = ParserFactory::getParser(MB_RECORDVIEW, 'Opportunities', null, null, 'base');
            if ($this->removeFields($recordViewDefsParser, $fields)) {
                $recordViewDefsParser->handleSave(false);
            }

            $modules =  array(
                'Accounts',
                'Contacts',
                'Campaigns',
                'Documents',
            );

            global $modInvisList;
            if (array_search('Project', $modInvisList)) {
                $modules[] = 'Project';
            }

            foreach ($modules as $module) {
                $this->log('Processing Opportunity SubPanel for ' . $module . ' module');
                $pf = ParserFactory::getParser(MB_LISTVIEW, $module, null, 'opportunities');
                if ($this->removeFields($pf, $fields)) {
                    $pf->handleSave(false);
                }
            }
        }
    }

    /**
     * Utility method to to make it easier to determine if we should save or not.
     *
     * @param AbstractMetaDataParser $parser
     * @param array $fields
     * @return bool
     */
    protected function removeFields($parser, $fields)
    {
        $shouldSave = false;
        foreach ($fields as $field) {
            if ($parser->removeField($field)) {
                $shouldSave = true;
            }
        }

        return $shouldSave;
    }
}
