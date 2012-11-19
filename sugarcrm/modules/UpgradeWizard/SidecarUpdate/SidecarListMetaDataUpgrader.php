<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
// This will need to be pathed properly when packaged
require_once 'SidecarAbstractMetaDataUpgrader.php';
require_once 'modules/ModuleBuilder/parsers/views/AbstractMetaDataParser.php';

class SidecarListMetaDataUpgrader extends SidecarAbstractMetaDataUpgrader
{
    /**
     * The actual legacy defs converter. For list it is simply taking the old 
     * def array, looping over it, lowercasing the field names, adding that to
     * each iteration and saving that into a 'fields' array inside of the panels
     * array.
     */
    public function convertLegacyViewDefsToSidecar() {
        $this->logUpgradeStatus('Converting ' . $this->client . ' list view defs for ' . $this->module);
        $newdefs = array();
        foreach ($this->legacyViewdefs as $field => $def) {
            $defs = array();
            $defs['name'] = strtolower($field);
            unset($def['name']); // Prevents old defs from overriding the new
            
            // Bug 57414 - Available fields of mobile listview shown under 
            //             default fields list after upgrade
            // For portal upgrades:
            //  - Default should be true by virtue of the field being in the viewdefs
            // For mobile upgrades: 
            //  - Default is true if default was set truthy before
            // For both platforms:
            //  - enabled is true if it was not set before, or if it was set to true
            if ($this->client == 'portal') {
                $defs['default'] = true;
            } else {
                if (isset($def['default'])) {
                    // If it was set in the mobile metadata, use that (bool) value
                    $defs['default'] = AbstractMetaDataParser::isTruthy($def['default']);
                    unset($def['default']); // remove to prevent overriding in merge
                } else {
                    // Was not set, so it is not default. This allows a field to
                    // be available without being default
                    $defs['default'] = false;
                }
            }
            
            // Enabled is almost always true by virtue of this field being in the defs
            if (!isset($def['enabled'])) {
                $defs['enabled'] = true;
            } else {
                // This will more than likely never run, but you never know
                // If somehow the field was marked enabled in a non truthy way...
                $defs['enabled'] = AbstractMetaDataParser::isTruthy($def['enabled']);
                unset($def['enabled']); // unsetting to prevent clash in merge
            }
            
            // Merge the rest of the defs
            $defs = array_merge($defs, $def);
            
            $newdefs[] = $defs;
        }
        $this->logUpgradeStatus("view defs converted, getting normalized module name");
        
        // This is the structure of the sidecar list meta
        $module = $this->getNormalizedModuleName();
        $this->logUpgradeStatus("module name normalized to: $module");
        
        // Clean up client to mobile for wireless clients
        $client = $this->client == 'wireless' ? 'mobile' : $this->client;
        $this->logUpgradeStatus("Setting new $client {$this->type} view defs internally for $module");
        $this->sidecarViewdefs[$module][$client]['view']['list'] = array(
            'panels' => array(
                array(
                    'label' => 'LBL_PANEL_DEFAULT',
                    'fields' => $newdefs,
                ),
            ),
        );
    }
}
