<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
require_once('include/entryPoint.php');

$itrs = array(
	array(
		'name' => 'Generate order_number so we can display it on the orderDetails() page',
		'description' => 'Write a method (maybe in Sugar_Util) that generates the next unique order_number.  The method should return the new order_number... and orderDetailsAction() in CartController.php should be updated to call this, and pass the order_number along to the $order array in gearman.  moofcart-si will need to be updated to accept the order_number and set it in the Orders module (also remove the auto_increment property to the existing column in moofcart-si)',
	),
	array(
		'name' => 'Check territory assignments',
		'description' => 'Let\'s doublecheck territory assignments... during the demo today, an Order somehow got assigned to a support rep.  The logic could be off, or our metadata could be off.  Or both!',
	),
	array(
		'name' => 'Check automatic discounts in Accounts/Opportunities',
		'description' => 'Are these working properly... you should be able to fully set up a discount, then login on the website and see it applied... on /cart/related-products, the discount will be pulled automatically and will be displayed in the summaries as "Automatic Discount: x" -- also, it looked like you could possibly modify some of the discount fields in Accounts/Opportunities, even after the discount had been approved.  Once it\'s approved, none of those fields should be editable.',
	),
	array(
		'name' => 'Verify that NetSuite Customer record has "Tax Item" set to "AVATAX" upon creation',
		'description' => '...',
	),
	array(
		'name' => 'In NetSuite, populate "Description" for all items in a Sales Order',
		'description' => 'For all products in the Items subtab of a Sales Order, set the "Description" field to the SKU of the product',
	),
	array(
		'name' => 'Fill out "Department" for every item in a Sales Order',
		'description' => 'In NetSuite, make sure the "Department" dropdown is populated for each line item ... you can hopefully re-use the Roles logic you developed for the automatic discounts to determine the department and set it.  The "Department" value should be based on who the Opportunity related to the Order is assigned to.',
	),
);

foreach ($itrs as $itr) {
$guid = create_guid();
$assigned_user_id = 'e6c7d4e5-dd51-9b70-daeb-4b4cf394a683';
$name = $itr['name'];
$description = $itr['description'];

$str = "INSERT INTO itrequests SET id = '{$guid}', date_entered = NOW(), date_modified = NOW(), modified_user_id = '290dddad-c592-87ad-a837-414790743628', assigned_user_id = '{$assigned_user_id}', created_by = '290dddad-c592-87ad-a837-414790743628', team_id = 1, deleted = 0, name = 'MoofCart: {$name}', status = 'Assigned', priority = 'p3', description = '{$description}', system_id = 1, target_date = '2010-10-11', development_time = 2, team_set_id = 1";

$str2 = "INSERT INTO itrequests_cstm SET id_c = '{$guid}', escalation_c = 0, project_c = 'SI_Proj_MoofCart', department_c = 'internal', department_category_c = 'IS_SugarInternal'";

$GLOBALS['db']->query($str);
$GLOBALS['db']->query($str2);

echo $guid . "\n";
}
