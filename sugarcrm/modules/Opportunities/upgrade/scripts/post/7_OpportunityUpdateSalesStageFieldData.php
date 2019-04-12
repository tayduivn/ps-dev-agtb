<?php
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
 * Update fields that have been modified to be calculated.
 */
class SugarUpgradeOpportunityUpdateSalesStageFieldData extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if (!$this->toFlavor('ent') || !version_compare($this->from_version, '9.1.0', '<')) {
            return;
        }

        $settings = Opportunity::getSettings();
        if ($settings['opps_view_by'] !== 'RevenueLineItems') {
            $this->log('Not using Revenue Line Items; Skipping Upgrade Script');
            return;
        }

        $this->fixSalesStageField();
    }

    /**
     * Process all opportunities that which have a sales_status of New, In Progress
     * and set the sales_stage based on the rli's.
     */
    protected function fixSalesStageField()
    {
        global $app_list_strings;
        $salesStageOptions = $app_list_strings['sales_stage_dom'];

        $opportunitySettings = Opportunity::getSettings();
        $forecastSettings = Forecast::getSettings();

        $closedWon = [Opportunity::STATUS_CLOSED_WON];
        $closedLost = [Opportunity::STATUS_CLOSED_LOST];

        if ($forecastSettings['is_setup'] === 1) {
            $closedWon = $forecastSettings['sales_stage_won'];
            $closedLost = $forecastSettings['sales_stage_lost'];
        }

        //Filter out opportunities that are dead or closed
        $sql = "SELECT id FROM opportunities where sales_status in ('New','In Progress')";
        $results = $this->db->query($sql);

        while ($row = $this->db->fetchRow($results)) {
            $opp = BeanFactory::getBean('Opportunities', $row['id']);
            $salesStage = '';

            $totalRli = count($opp->get_linked_beans('revenuelineitems', 'RevenueLineItems'));

            $wonRli = count(
                $opp->get_linked_beans(
                    'revenuelineitems',
                    'RevenueLineItems',
                    array(),
                    0,
                    -1,
                    0,
                    "sales_stage in ('" . join("', '", $closedWon) . "')"
                )
            );

            $lostRli = count(
                $opp->get_linked_beans(
                    'revenuelineitems',
                    'RevenueLineItems',
                    array(),
                    0,
                    -1,
                    0,
                    "sales_stage in ('" . join("', '", $closedLost) . "')"
                )
            );

            if ($totalRli > 0) {
                if ($lostRli === $totalRli) {
                    $salesStage = Opportunity::STAGE_CLOSED_LOST;
                } elseif ($lostRli + $wonRli === $totalRli) {
                    $salesStage = Opportunity::STAGE_CLOSED_WON;
                } else {
                    //Need to determine the latst sales stage based on dropdown options for sales_stage dom
                    $latestSalesStageIndex = 0;
                    $latestSalesStageKey = '';

                    $rli = BeanFactory::newBean('RevenueLineItems');
                    $sq = new SugarQuery();
                    $sq->select('sales_stage');
                    $sq->from($rli)
                        ->where()->equals('opportunity_id', $opp->id);
                    $sq->where()->queryAnd()->addRaw("sales_stage not in ('" . join("', '", $closedLost) . "')");
                    $sq->where()->queryAnd()->addRaw("sales_stage not in ('" . join("', '", $closedWon) . "')");
                    $sq->groupBy('sales_stage');

                    if (count($rlis = $sq->execute()) > 0) {
                        foreach ($rlis as $rli) {
                            $stage = $rli['sales_stage'];
                            $nextSalesStageOption = array_search(
                                $stage,
                                array_keys($salesStageOptions)
                            );

                            if ($nextSalesStageOption >= $latestSalesStageIndex) {
                                $latestSalesStageIndex = $nextSalesStageOption;
                                $latestSalesStageKey =  $rli['sales_stage'];
                            }
                        }
                    }

                    $salesStage = $latestSalesStageKey;
                }

                //update the opp with the current sales stage
                $sql = sprintf(
                    'UPDATE opportunities SET sales_stage = %s WHERE id = %s',
                    $this->db->quoted($salesStage),
                    $this->db->quoted($opp->id)
                );
                $this->db->query($sql);
            }
        }
    }
}
