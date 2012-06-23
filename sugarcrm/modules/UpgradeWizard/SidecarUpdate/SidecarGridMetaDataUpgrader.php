<?php
// This will need to be pathed properly when packaged
require_once 'SidecarAbstractMetaDataUpgrader.php';

class SidecarGridMetaDataUpgrader extends SidecarAbstractMetaDataUpgrader
{
    /**
     * The metadata array key for the panels/data section. Wireless calls them
     * panels, portal call them data.
     * 
     * @var array
     */
    protected $panelKeys = array(
        'portaledit'     => 'data',
        'portaldetail'   => 'data',
        'wirelessedit'   => 'panels',
        'wirelessdetail' => 'panels',
    );
    
    /**
     * Converts the legacy Grid metadata to Sidecar style
     */
    public function convertLegacyViewDefsToSidecar() {
        // Leave the original legacy viewdefs in tact
        $defs = $this->legacyViewdefs;
        
        // Find out which panel key to use based on viewtype and client
        $panelKey = $this->panelKeys[$this->client.$this->viewtype];
        if (isset($defs[$panelKey])) {
            $fields = array();
            
            // Necessary for setting the proper field array types
            $maxcols = isset($defs['templateMeta']['maxColumns']) ? intval($defs['templateMeta']['maxColumns']) : 2;
            foreach ($defs[$panelKey] as $row) {
                $cols = count($row);
                // Assumption here is that Portal and Wireless will never have 
                // more than 2 columns in the old setup
                if ($cols == 1) {
                    $displayParams = array('colspan' => $maxcols);
                    // Either a string field name or an instruction
                    if (is_string($row[0])) {
                        if ($maxcols == 1) {
                            $fields[] = $row[0];
                        } else {
                            $fields[] = array('name' => $row[0], 'displayParams' => $displayParams);
                        }
                    } else {
                        // Some sort of instruction set
                        if (is_array($row[0])) { 
                            if (isset($row[0]['field'])) {
                                // Old style field now maps to name
                                $field = $row[0]['field'];
                                unset($row[0]['field']);
                                $fields[] = array_merge(
                                    array('name' => $field), 
                                    $row[0], 
                                    $maxcols == 1 ? array() : array('displayParams' => $displayParams)
                                );
                            } else {
                                // Fallback... take it as is
                                $fields[] = $row[0];
                            }
                        }
                    }
                } else {
                    // We actually have the necessary col count
                    foreach ($row as $field) {
                        if (is_string($field)) {
                            $fields[] = $field;
                        } elseif (isset($field['field'])) {
                            $fields[] = $field['field'];
                        }
                    }
                    
                }
            }
            
            
            $paneldefs = array(array('label' => $this->viewtype == 'edit' ? 'LBL_EDIT' : 'LBL_DETAIL', 'fields' => $fields));
            unset($defs['data']);
            $defs['panels'] = $paneldefs;
            $this->sidecarViewdefs[$this->module][$this->client]['view'][$this->viewtype] = $defs;
        }
    }
}