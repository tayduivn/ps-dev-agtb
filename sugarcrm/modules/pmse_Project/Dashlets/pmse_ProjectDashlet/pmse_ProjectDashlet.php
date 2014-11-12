<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/pmse_Project/pmse_Project.php');

class pmse_ProjectDashlet extends DashletGeneric { 
    function pmse_ProjectDashlet($id, $def = null) {
		global $current_user, $app_strings;
		require('modules/pmse_Project/metadata/dashletviewdefs.php');

        parent::DashletGeneric($id, $def);

        if(empty($def['title'])) $this->title = translate('LBL_HOMEPAGE_TITLE', 'pmse_Project');

        $this->searchFields = $dashletData['pmse_ProjectDashlet']['searchFields'];
        $this->columns = $dashletData['pmse_ProjectDashlet']['columns'];

        $this->seedBean = new pmse_Project();        
    }
}