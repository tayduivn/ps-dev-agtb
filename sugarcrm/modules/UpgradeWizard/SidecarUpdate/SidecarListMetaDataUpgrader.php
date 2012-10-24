<?php
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
            // Bug 57414 - Available fields of mobile listview shown under 
            //             default fields list after upgrade
            // For portal upgrades, enabled should be true by virtue of the filed being in the viewdefs
            // For mobile upgrades, enabled is true if it was not set before or if it was true before
            // For both platforms, default is true if it was not set before, or
            // if it was set to true
            $defs['default'] = !isset($def['default']) || AbstractMetaDataParser::isTruthy($def['default']);
            $defs['enabled'] = $this->client == 'portal' || !isset($def['enabled']) || AbstractMetaDataParser::isTruthy($def['enabled']);
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
