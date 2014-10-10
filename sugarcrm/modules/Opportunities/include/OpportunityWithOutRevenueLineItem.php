<?php
//FILE SUGARCRM flav=ent ONLY
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

require_once 'OpportunitySetup.php';

/**
 * Class OpportunityWithOutRevenueLineItem
 *
 * This is used for when we want to convert from RLI back to just Opps
 */
class OpportunityWithOutRevenueLineItem extends OpportunitySetup
{
    protected $dateClosedMigration = 'max';

    /**
     * Mapping for the values of the vardefs
     *
     * @var array
     */
    protected $field_vardef_setup = array(
        'amount' => array(
            'required' => true,
            'audited' => true,
            'calculated' => false,
            'enforced' => false,
            'formula' => '',
            'readonly' => false,
            'massupdate' => true,
            'importable' => 'required',
        ),
        'best_case' => array(
            'calculated' => false,
            'enforced' => false,
            'formula' => '',
            'audited' => true,
            'readonly' => false,
            'massupdate' => true,
        ),
        'worst_case' => array(
            'calculated' => false,
            'enforced' => false,
            'formula' => '',
            'audited' => true,
            'readonly' => false,
            'massupdate' => true,
        ),
        'date_closed' => array(
            'calculated' => false,
            'enforced' => false,
            'formula' => '',
            'audited' => true,
            'importable' => 'required',
            'required' => true,
            'massupdate' => true,
        ),
        'commit_stage' => array(
            'massupdate' => true,
            'studio' => true,
            'reportable' => true,
            'workflow' => true
        ),
        'sales_stage' => array(
            'audited' => true,
            'required' => true,
            'studio' => true,
            'massupdate' => true,
            'reportable' => true,
            'workflow' => true,
        ),
        'probability' => array(
            'audited' => true,
            'studio' => true,
            'massupdate' => true,
            'reportable' => true,
        ),
        'sales_status' => array(
            'studio' => false,
            'reportable' => false,
            'audited' => false,
            'massupdate' => false,
        ),
        'date_closed_timestamp' => array(
            'formula' => 'timestamp($date_closed)'
        )
    );

    /**
     * Put any custom Convert Logic Here
     *
     * @return mixed|void
     */
    public function doMetadataConvert()
    {
        // always runt he parent first, since we need to fix the vardefs before doing the viewdefs
        parent::doMetadataConvert();

        // fix the record view first
        // only add the commit_stage field if forecasts is setup
        $this->fixRecordView(
            array(
                'commit_stage' => $this->isForecastSetup(),
                'sales_status' => false,
                'sales_stage' => true,
                'probability' => true
            )
        );

        // fix the various list views
        $this->fixListViews(
            array(
                'sales_status' => 'sales_stage',
                'probability' => false,
            )
        );
    }

    /**
     * Metadata Fixes for the Opportunity Module
     *
     * - Removes the duplicate check change
     * - Removes the dependency extension that turns off the default oob dependencies
     */
    protected function fixOpportunityModule()
    {
        if (SugarAutoLoader::fileExists($this->moduleExtFolder . '/Vardefs/' . $this->dupeCheckExtFile)) {
            SugarAutoLoader::unlink($this->moduleExtFolder . '/Vardefs/' . $this->dupeCheckExtFile);
        }

        if (SugarAutoLoader::fileExists($this->moduleExtFolder . '/Dependencies/' . $this->oppModuleDependencyFile)) {
            SugarAutoLoader::unlink($this->moduleExtFolder . '/Dependencies/' . $this->oppModuleDependencyFile);
        }
    }

    /**
     * Metadata fixes for the RLI Module
     *
     * - Removes the file that shows the RLI Module
     * - Removes the Studio File
     * - Hides the RLI module from the menu bar
     * - Removes the ACL Actions
     */
    protected function fixRevenueLineItemModule()
    {
        // cleanup on the current request
        $GLOBALS['modInvisList'][] = 'RevenueLineItems';
        if (isset($GLOBALS['moduleList']) && is_array($GLOBALS['moduleList'])) {
            foreach ($GLOBALS['moduleList'] as $key => $mod) {
                if ($mod === 'RevenueLineItems') {
                    unset($GLOBALS['moduleList'][$key]);
                }
            }
        }

        if (SugarAutoLoader::fileExists($this->appExtFolder . '/Include/' . $this->rliModuleExtFile)) {
            SugarAutoLoader::unlink($this->appExtFolder . '/Include/' . $this->rliModuleExtFile);
        }

        if (SugarAutoLoader::fileExists($this->rliStudioFile)) {
            SugarAutoLoader::unlink($this->rliStudioFile);
        }

        $this->setRevenueLineItemModuleTab(false);

        // disable the ACLs on RevenueLineItems
        ACLAction::removeActions('RevenueLineItems');
    }

    /**
     * Call this method to convert the data as well, this should be called after `doMetadataConvert`
     */
    public function doDataConvert()
    {
        $this->resetForecastData('Opportunities');
        $this->queueRevenueLineItemsForNotesOnOpportunities();
        $this->setOpportunityDataFromRevenueLineItems();
        $this->deleteRevenueLineItems();
    }

    /**
     * Delete all the RLI data, since it not needed any more
     */
    protected function deleteRevenueLineItems()
    {
        $rli = BeanFactory::getBean('RevenueLineItems');
        /* @var $db DBManager */
        $db = DBManagerFactory::getInstance();
        $db->query($db->truncateTableSQL($rli->getTableName()));

        $cstm_table = $rli->getTableName() . '_cstm';

        if ($db->tableExists($cstm_table)) {
            $db->query($db->truncateTableSQL($cstm_table));
        }
    }

    protected function queueRevenueLineItemsForNotesOnOpportunities()
    {
        /* @var $rli RevenueLineItem */
        $rli = BeanFactory::getBean('RevenueLineItems');

        $labels = array();

        $fields = array(
            'name',
            'sales_stage',
            'probability',
            'date_closed',
            'currency_id',
            'worst_case',
            'likely_case',
            'best_case',
            'opportunity_id',
            'next_step',
        );

        // for now use the default config
        $default_lang = $GLOBALS['sugar_config']['default_language'];
        $mod_strings = return_module_language($default_lang, $rli->module_name);
        $app_strings = return_application_language($default_lang);
        foreach ($fields as $field) {
            if ($field === 'currency_id') {
                $vname = 'LBL_CURRENCY';
            } else {
                $def = $rli->getFieldDefinition($field);
                $vname = $def['vname'];
            }
            if (isset($mod_strings[$vname])) {
                $labels[$field] = str_replace(':', '', $mod_strings[$vname]);
            } elseif (isset($app_strings[$vname])) {
                $labels[$field] = str_replace(':', '', $app_strings[$vname]);
            } else {
                $labels[$field] = $vname;
            }
        }

        // get all the rows
        $sq = new SugarQuery();
        $sq->select($fields);
        $sq->from($rli)
            ->orderBy('opportunity_id')
            ->orderBy('date_closed');

        $results = $sq->execute();

        $chunk = array();
        $max_chunk_size = 50;

        foreach ($results as $row) {
            if (!isset($chunk[$row['opportunity_id']])) {
                if (count($chunk) === $max_chunk_size) {
                    // schedule job here
                    $this->scheduleOpportunityRevenueLineItemNoteCreate($labels, $chunk);
                    $chunk = array();
                }
                $chunk[$row['opportunity_id']] = array();
            }
            // remove the fields added by the sorting in SugarQuery
            unset($row['revenue_line_items__opportunity_id']);
            unset($row['revenue_line_items__date_closed']);
            $chunk[$row['opportunity_id']][] = $row;
        }

        // schedule the last job here.
        $this->scheduleOpportunityRevenueLineItemNoteCreate($labels, $chunk);
    }

    private function scheduleOpportunityRevenueLineItemNoteCreate(array $labels, array $chunk)
    {
        /* @var $job SchedulersJob */
        $job = BeanFactory::getBean('SchedulersJobs');
        $job->name = "Create Revenue Line Items Note On Opportunities";
        $job->target = "class::SugarJobCreateRevenueLineItemNotes";
        $job->data = json_encode(array('chunk' => $chunk, 'labels' => $labels));
        $job->retry_count = 0;
        $job->assigned_user_id = $GLOBALS['current_user']->id;

        require_once('include/SugarQueue/SugarJobQueue.php');
        $jq = new SugarJobQueue();
        $jq->submitJob($job);
    }

    /**
     * Fix the Opportunity Data to have the correct data once we go back from having RLI's to only have Opps
     *
     * - Takes the lowest sales_stage from all the RLIs
     * - Takes the lowest date_closed from all the RLIs
     * - Sets commit_stage to empty
     * - Sets sales_status to empty
     *
     * This is all done via a Query since we delete all the RLI's and we didn't want to keep any of them around.
     *
     * @throws SugarQueryException
     */
    protected function setOpportunityDataFromRevenueLineItems()
    {
        // need to figure out the best way to roll this up before truncating the table.
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
        // get the sales_stage from the RLI module
        /* @var $rli RevenueLineItem */
        $rli = BeanFactory::getBean('RevenueLineItems');
        $def = $rli->getFieldDefinition('sales_stage');

        $db = DBManagerFactory::getInstance();
        $list_value = array();

        // get the `options` param so we make sure if they customized it to use their custom version
        $sqlCase = '';
        $list = $def['options'];
        if (!empty($list) && isset($app_list_strings[$list])) {
            $i = 0;
            $order_by_arr = array();
            foreach ($app_list_strings[$list] as $key => $value) {
                $list_value[$i] = $key;
                if ($key == '') {
                    $order_by_arr[] = "WHEN (sales_stage='' OR sales_stage IS NULL) THEN " . $i++;
                } else {
                    $order_by_arr[] = "WHEN sales_stage=" . $db->quoted($key) . " THEN " . $i++;
                }
            }
            $sqlCase = "min(CASE " . implode("\n", $order_by_arr) . " ELSE $i END)";
        }

        $sq = new SugarQuery();
        $sq->select(array('id', 'name', 'opportunity_id'))
            ->fieldRaw($sqlCase, 'sales_stage')
            ->fieldRaw($this->dateClosedMigration . '(date_closed)', 'date_closed')
            ->fieldRaw($this->dateClosedMigration . '(date_closed_timestamp)', 'date_closed_timestamp');
        $sq->from($rli);
        $sq->groupBy('opportunity_id');

        $results = $sq->execute();
        foreach ($results as $result) {
            $sql = 'UPDATE opportunities SET date_closed = ' . $db->quoted($result['date_closed']) . ',
                date_closed_timestamp = ' . $db->quoted($result['date_closed_timestamp']) . ',
                sales_stage = ' . $db->quoted($list_value[$result['sales_stage']]) . ',
                probability = ' . $db->quoted($app_list_strings['sales_probability_dom'][$list_value[$result['sales_stage']]]) . ',
                sales_status = "", commit_stage = ""
                WHERE id = ' . $db->quoted($result['opportunity_id']) . ';';

            $db->query($sql);
        }

        if ($this->isForecastSetup()) {
            SugarAutoLoader::load('include/SugarQueue/jobs/SugarJobUpdateOpportunities.php');
            SugarJobUpdateOpportunities::updateOpportunitiesForForecasting();
        }
    }

    public function setDateClosedMigrationParam($type)
    {
        $type = strtolower($type);
        if ($type === 'earliest') {
            $this->dateClosedMigration = 'min';
        } else {
            $this->dateClosedMigration = 'max';
        }
    }
}
