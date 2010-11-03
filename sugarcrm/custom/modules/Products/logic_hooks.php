<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will
// be automatically rebuilt in the future.
 $hook_version = 1;
$hook_array = Array();
// position, file, function
$hook_array['before_save'] = Array();
/**
 * @author jwhitcraft
 * @project MoofCart
 * @tasknum 3
 * add the before_save logic hook to run before save
 */
$hook_array['before_save'][] = Array(1, 'updateCustomFieldsFromTemplate', 'custom/si_logic_hooks/Products/save_custom_fields.php','updateCustomFieldsFromTemplate', 'update');
/**
 * End MoofCart Customization
 */

$hook_array['after_save'] = Array();
