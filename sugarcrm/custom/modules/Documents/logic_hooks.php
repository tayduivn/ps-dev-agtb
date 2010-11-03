<?php
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 3
 * Sets the Order Name to the Order Id so triggers and search work.
 */

/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 68
 * Before Save Logic Hook for orders to upgrade subs when the opportunity is Additional or Renewal and the order is completed
*/


// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
#$hook_array['after_relationship_add'][] = Array(1, 'updatePO', 'custom/si_logic_hooks/Documents/updatePO.php', 'updatePO', 'update');
$hook_array['before_save'][] = Array(1, 'handleOrderRelationship', 'custom/si_logic_hooks/Documents/DocumentOrderHooks.php','DocumentOrderHooks', 'handleOrderRelationship'); 

$hook_array['after_save'][] = Array(1, 'handleOrderStatus', 'custom/si_logic_hooks/Documents/DocumentOrderHooks.php','DocumentOrderHooks', 'handleOrderStatus'); 