<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/pmse_Inbox/pmse_Inbox.php');

class pmse_InboxDashlet extends DashletGeneric { 
    function pmse_InboxDashlet($id, $def = null) {
		global $current_user, $app_strings;
		require('modules/pmse_Inbox/metadata/dashletviewdefs.php');

        parent::DashletGeneric($id, $def);

        if(empty($def['title'])) $this->title = translate('LBL_HOMEPAGE_TITLE', 'pmse_Inbox');

        $this->searchFields = $dashletData['pmse_InboxDashlet']['searchFields'];
        $this->columns = $dashletData['pmse_InboxDashlet']['columns'];

        $this->seedBean = array();//new pmse_Inbox();
    }
}