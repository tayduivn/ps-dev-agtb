<?php


class updateOppSalesStage
{
    public function checkSalesStageUpdate($bean, $event, $arguments)
    {
        if ($event != "before_save") return false;

        // make sure that the sales stage 
        if ($bean->sales_stage == "Sales Ops Closed" && $bean->fetched_row['sales_stage'] == "Closed Won") {
            $workload = array(
                'opportunity_id' => $bean->id,
                'order_id' => $bean->orders_opp69easorders_ida,
                'flag' => false,
            );

            require_once('custom/si_custom_files/MoofCartHelper.php');

            $servers = MoofCartHelper::getGearmanServers();

            $client = new GearmanClient();
            $client->addServers($servers);
            $client->doBackground('NSSetPendingSalesOps', serialize($workload));
        }

    }
}