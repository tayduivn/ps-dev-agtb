<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 */

global $mod_strings, $app_strings, $sugar_config, $current_user;

if(is_admin($current_user)) $module_menu[] =Array("index.php?module=Score&action=AdminSettings", $mod_strings['LNK_CHANGE_SCORES'],"icon_Score");
if(ACLController::checkAccess('Campaigns', 'edit', true)) $module_menu[] = Array("index.php?module=Score&action=CampaignRescore&return_module=Score&return_action=AdminSettings", $mod_strings['LNK_CAMPAIGN_RESCORE'],"Campaigns");
if(is_admin($current_user)) $module_menu[] =Array("index.php?module=Score&action=ManualRescore", $mod_strings['LBL_MANUAL_RESCORE'],"icon_Score");
