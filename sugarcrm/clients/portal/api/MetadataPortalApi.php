<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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

require_once 'clients/base/api/MetadataApi.php';

// An API to let the user in to the metadata
class MetadataPortalApi extends MetadataApi {
    /**
     * Gets configs
     * 
     * @return array
     */
    protected function getConfigs() {
        $configs = array();
        $admin = new Administration();
        $admin->retrieveSettings();
        foreach($admin->settings AS $setting_name => $setting_value) {
            if(stristr($setting_name, 'portal_')) {
                $key = str_replace('portal_', '', $setting_name);
                $configs[$key] = json_decode(html_entity_decode($setting_value),true);
            }
        }
        
        return $configs;
    }

    protected function loadMetadata() {
        $data = parent::loadMetadata();
        if (!empty($data['modules'])) {
            foreach($data['modules'] as $modKey => $modMeta) {
                if (!empty($modMeta['isBwcEnabled'])) {
                    // portal has no concept of bwc so get rid of it
                    unset($data['modules'][$modKey]['isBwcEnabled']);
                }
            }
        }
        return $data;
    }

    /**
     * Fills in additional app list strings data as needed by the client
     * 
     * @param array $public Public app list strings
     * @param array $main Core app list strings
     * @return array
     */
    protected function fillInAppListStrings(Array $public, Array $main) {
        $public['countries_dom'] = $main['countries_dom'];
        $public['state_dom'] = $main['state_dom'];
        
        return $public;
    }

}
