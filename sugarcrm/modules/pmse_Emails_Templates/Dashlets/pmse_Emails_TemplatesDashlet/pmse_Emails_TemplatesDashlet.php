<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/Dashlets/DashletGeneric.php');
require_once('modules/pmse_Emails_Templates/pmse_Emails_Templates.php');

class pmse_Emails_TemplatesDashlet extends DashletGeneric { 
    function pmse_Emails_TemplatesDashlet($id, $def = null) {
		global $current_user, $app_strings;
		require('modules/pmse_Emails_Templates/metadata/dashletviewdefs.php');

        parent::DashletGeneric($id, $def);

        if(empty($def['title'])) $this->title = translate('LBL_HOMEPAGE_TITLE', 'pmse_Emails_Templates');

        $this->searchFields = $dashletData['pmse_Emails_TemplatesDashlet']['searchFields'];
        $this->columns = $dashletData['pmse_Emails_TemplatesDashlet']['columns'];

        $this->seedBean = new pmse_Emails_Templates();        
    }
}