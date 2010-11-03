<?php
/**
 * @author Jon Whitcraft
 * @project moofcart
 * Send The Order to NS if the flag is not true
 */

$order = new Orders();
$order->retrieve($_GET['record']);

if ($_GET['record'] == $order->id) {
    $arrWorkload = array();
    eval('$arrWorkload = ' . html_entity_decode($order->workload_c, ENT_QUOTES) . ';');

    if (!empty($arrWorkload)) {
        $workload = array(
            'order_id' => $order->id,
            'additional_info' => $arrWorkload['netsuite'],
        );

        require_once('custom/si_custom_files/MoofCartHelper.php');
        $server = MoofCartHelper::getGearmanServers();

        $client = new GearmanClient();
        $client->addServers($server);
        $client->doBackground('ns-send-order', serialize($workload), null);

    }

    header("Location: /index.php?module=Orders&action=DetailView&record=" . $_GET['record']);


}
