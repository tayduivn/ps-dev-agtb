<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
// position, file, function 
$hook_array['before_save'] = Array(); 
$hook_array['after_delete'][] = Array(1, 'generateCampaignList', 'custom/si_logic_hooks/Campaigns/CampaignHooks.php','CampaignHooks', 'generateCampaignList'); 
$hook_array['after_save'][] = Array(1, 'generateCampaignList', 'custom/si_logic_hooks/Campaigns/CampaignHooks.php','CampaignHooks', 'generateCampaignList'); 
$hook_array['before_save'][] = Array(1, 'generateCampaignList', 'custom/si_logic_hooks/Campaigns/CampaignHooks.php','CampaignHooks', 'generateCampaignList'); 
$hook_array['before_save'][] = Array(1, 'workflow', 'include/workflow/WorkFlowHandler.php','WorkFlowHandler', 'WorkFlowHandler'); 



?>
