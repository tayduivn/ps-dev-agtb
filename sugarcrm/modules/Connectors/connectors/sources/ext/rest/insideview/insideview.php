<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement 
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.  
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may 
 *not use this file except in compliance with the License. Under the terms of the license, You 
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or 
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or 
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit 
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the 
 *Software without first paying applicable fees is strictly prohibited.  You do not have the 
 *right to remove SugarCRM copyrights from the source code or user interface. 
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and 
 * (ii) the SugarCRM copyright notice 
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer 
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.  
 ********************************************************************************/

require_once('include/connectors/sources/ext/rest/rest.php');
class ext_rest_insideview extends ext_rest {
	protected $_enable_in_wizard = false;
	protected $_enable_in_hover = false;
	protected $_has_testing_enabled = false;

    protected $orgId;
    protected $orgName;
    protected $userId;
    public static $allowedModuleList;
    
    public function __construct() {
        
        $this->allowedModuleList = array('Accounts' => 'Accounts', 'Contacts' => 'Contacts', 'Opportunities' => 'Opportunities', 'Leads' => 'Leads');

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

    // InsideView currently has no web service to control field mappings
    public function getMapping() {
        return;
    }

    public function saveMappingHook($mapping) {

        $removeList = $this->allowedModuleList;

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
