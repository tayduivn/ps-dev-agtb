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
// position, file, function 
$hook_array['before_save'] = Array();
#$hook_array['before_save'][] = Array(1, 'updateSubscriptions', 'custom/si_logic_hooks/Orders/updateSubscriptions.php', 'updateSubscriptions', 'update');

$hook_array['after_save'] = Array();
//$hook_array['after_save'][] = Array(1, 'accountContactCheck', 'custom/si_logic_hooks/Orders/accountContactCheck.php','accountContactCheck', 'check');
//$hook_array['after_save'][] = Array(1, 'findOpportunity', 'custom/si_logic_hooks/Orders/findOpportunity.php', 'findOpportunity', 'find');
#$hook_array['after_save'][] = Array(1, 'updateContract', 'custom/si_logic_hooks/Orders/updateContract.php', 'updateContract', 'update');
$hook_array['after_save'][] = Array(1, 'updateContactAuthorizedSupport', 'custom/si_logic_hooks/Orders/updateContactAuthorizedSupport.php', 'updateContactAuthorizedSupport', 'update');
//$hook_array['after_save'][] = Array(1, 'setNameFromOrderId', 'custom/si_logic_hooks/Orders/setNameFromOrderId.php','setNameFromOrderId', 'set');



#$hook_array['after_relationship_add'][] = Array(1, 'updatePO', 'custom/si_logic_hooks/Documents/updatePO-test.php', 'updatePO', 'update');

