<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once('include/connectors/sources/ext/rest/rest.php');
class ext_rest_insideview extends ext_rest {
	protected $_enable_in_wizard = false;
	protected $_enable_in_hover = false;
    protected $_enable_in_admin_properties = false;
    protected $_enable_in_admin_mapping = false;
    protected $_enable_in_admin_search = false;
	protected $_has_testing_enabled = false;

    protected $orgId;
    protected $orgName;
    protected $userId;
    public static $allowedModuleList;
    
    public function __construct() {
        
        global $app_list_strings;
        $this->allowedModuleList = array('Accounts' => $app_list_strings['moduleList']['Accounts'],
                                         'Contacts' => $app_list_strings['moduleList']['Contacts'],
                                         'Opportunities' => $app_list_strings['moduleList']['Opportunities'],
                                         'Leads' => $app_list_strings['moduleList']['Leads']);

        parent::__construct();
    }

    public function filterAllowedModules( $moduleList ) {
        // InsideView currently has no ability to talk to modules other than these four
        $outModuleList = array();
        foreach ( $moduleList as $module ) {
            if ( !in_array($module,$this->allowedModuleList) ) {
                continue;
            } else {
                $outModuleList[$module] = $module;
            }
        }
        return $outModuleList;
    }

    public function saveMappingHook($mapping) {

        $removeList = array();
        foreach ($this->allowedModuleList as $module_name=>$display_name) {
            $removeList[$module_name] = $module_name;
        }

        if ( is_array($mapping['beans']) ) {
            foreach($mapping['beans'] as $module => $ignore) {
                unset($removeList[$module]);
                
                check_logic_hook_file($module, 'after_ui_frame', array(1, $module. ' InsideView frame', 'modules/Connectors/connectors/sources/ext/rest/insideview/InsideViewLogicHook.php', 'InsideViewLogicHook', 'showFrame') );
            }
        }

        foreach ( $removeList as $module ) {
            remove_logic_hook($module, 'after_ui_frame', array(1, $module. ' InsideView frame', 'modules/Connectors/connectors/sources/ext/rest/insideview/InsideViewLogicHook.php', 'InsideViewLogicHook', 'showFrame') );
        }

        return parent::saveMappingHook($mapping);
    }

    

	public function getItem($args=array(), $module=null){}
	public function getList($args=array(), $module=null) {}


    public function ext_allowInsideView( $request ) {
        $GLOBALS['current_user']->setPreference('allowInsideView',1,0,'Connectors');
        return true;
    }
}
