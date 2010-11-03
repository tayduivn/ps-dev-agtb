<?php
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 44
 * custom page to display the completed order then actually run the stuff for the completed order and redirect back to the Order
*/
require_once('custom/si_custom_files/MoofCartHelper.php');
$bean = new Orders();
$bean->retrieve($_REQUEST['record']);
// get'em outta here if they are here with the wrong status
if($bean->status != 'Queued' && $bean->status != 'pending_salesops') {
	header("Location:/index.php?module=Orders&action=DetailView&record={$_REQUEST['record']}");
	exit();
}

$run = false;
$display = true;

if(isset($_REQUEST['submit'])) {
	$run = true;
	$display = false;
}

$view = MoofCartHelper::completeOrder($bean,$run,$display);

if(isset($_REQUEST['submit'])) {
	header("Location:/index.php?module=Orders&action=DetailView&record={$_REQUEST['record']}");
	exit();
}
else {
	require_once('XTemplate/xtpl.php');
	$tpl= new XTemplate('custom/si_custom_files/tpls/moofcart_tpls/complete_order.tpl');
	foreach($view['messages'] AS $msg_array) {
		$tpl->assign('msg',$msg_array['msg']);
		if(isset($msg_array['custom'])) {
			foreach($msg_array['custom'] AS $custom_array) {
				$tpl->assign('c',$custom_array);
				$tpl->parse('main.message.custom.custom_row');
			}
			$tpl->parse('main.message.custom');
		}
		$tpl->parse('main.message');
	}
	foreach($view['emails'] AS $email_array) {
		$tpl->assign('e', $email_array);
		$tpl->parse('main.email');
	}
	$tpl->assign('record_id', $_REQUEST['record']);
	$tpl->parse('main.show_submit');
	$tpl->parse('main');
}
$tpl->out('main');
?>