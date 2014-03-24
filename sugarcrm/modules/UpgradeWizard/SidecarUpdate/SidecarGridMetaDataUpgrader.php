<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

// This will need to be pathed properly when packaged
require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarAbstractMetaDataUpgrader.php';

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
     * File with basic definitions for the view
     * @var string
     */
    protected $defsfile;
    /**
     * File with template definitions for the view
     * @var string
     */
    protected $basic_defsfile;

    /**
     * Panel names in the new style, for the first two panels
     * 
     * @var array
     */
    protected $panelNames = array(
        array(
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
        ),
        array(
            'name' => 'panel_hidden',
            'label' => 'LBL_RECORD_SHOWMORE'
        ),
    );

    /**
     * Load current Sugar metadata for this module
     * @return array
     */
    protected function loadDefaultMetadata()
    {
        $client = $this->client == 'wireless' ? 'mobile' : $this->client;
        // The new defs array - this should contain OOTB defs for the module
        $newdefs = $viewdefs = array();

        $viewname = MetaDataFiles::getName($this->viewtype);
        if(!$viewname) {
            $viewname = $this->viewtype;
        }

        // Bug 55568 - new metadata was not included for custom metadata
        // conversion from pre-6.6 installations.
        // Grab the new metadata for this module. For undeployed modules we
        // need to get the metadata from the SugarObject type.
        // If there are defs for this module, grab them
        $this->defsfile = 'modules/' . $this->module . '/clients/' . $client . '/views/' . $viewname . '/' . $viewname . '.php';
        if (in_array($this->module, $GLOBALS['moduleList'])) {
            if (file_exists($this->defsfile)) {
                require $this->defsfile;
                if (isset($viewdefs[$this->module][$client]['view'][$viewname])) {
                    $newdefs = $viewdefs[$this->module][$client]['view'][$viewname];
                }
            }
        }

        // Fallback to the object type if there were no defs found
        // Bug 57216 - Upgrade wizard was dying on undeployed modules getType
        if (empty($newdefs) && $this->deployed) {
            require_once 'modules/ModuleBuilder/Module/StudioModuleFactory.php';
            $sm = StudioModuleFactory::getStudioModule($this->module);
            $moduleType = $sm->getType();
            $this->base_defsfile = 'include/SugarObjects/templates/' . $moduleType . '/clients/' . $client . '/views/' . $viewname . '/' . $viewname . '.php';
            if (file_exists($this->base_defsfile)) {
                require $this->base_defsfile;
            } else {
                $this->base_defsfile = 'include/SugarObjects/templates/basic/clients/' . $client . '/views/' . $viewname . '/' . $viewname . '.php';
                if (file_exists($this->base_defsfile)) {
                    require $this->base_defsfile;
                } else {
                    $this->logUpgradeStatus("Could not find base for module {$this->module} type $moduleType");
                }
            }
            // See if there are viewdefs defined that we can use
            if (isset($viewdefs['<module_name>'][$client]['view'][$viewname])) {
                $newdefs = $viewdefs['<module_name>'][$client]['view'][$viewname];
            }

            if($newdefs) {
                // If we used the template, create the basic one
                $this->logUpgradeStatus("Copying template defs {$this->base_defsfile} to {$this->defsfile}");
                mkdir_recursive(dirname($this->defsfile));
                $viewname = pathinfo($this->defsfile, PATHINFO_FILENAME);
                $export = var_export($newdefs, true);
                $data  = <<<END
<?php
/* Generated by SugarCRM Upgrader */
\$viewdefs['{$this->module}']['{$client}']['view']['{$viewname}'] = {$export};
END;
                sugar_file_put_contents($this->defsfile, $data);
            }
              }
        return $newdefs;
    }

    /**
     * Converts the legacy Grid metadata to Sidecar style
     *
     * Because there were additions to the grid metadata files, the upgrader will
     * actually not use the full legacy metadata but only the panel defs. The rest
     * of the metadata will come from the 6.6+ style metadata.
     */
    public function convertLegacyViewDefsToSidecar()
    {
        $client = $this->client == 'wireless' ? 'mobile' : $this->client;
        $this->logUpgradeStatus('Converting ' . $this->client . ' ' . $this->viewtype . ' view defs for ' . $this->module);

        // Leave the original legacy viewdefs in tact
        $defs = $this->legacyViewdefs;

        // Find out which panel key to use based on viewtype and client
        $panelKey = $this->panelKeys[$this->client.$this->viewtype];
        if (isset($defs[$panelKey])) {
            // Get the converted defs
            $fields = $this->handleConversion($defs, $panelKey);

            // If we still don't have new viewdefs then fall back onto the old
            // ones. This shouldn't happen, but we need to make sure we have defs
            $newdefs = $this->loadDefaultMetadata();
            if (empty($newdefs)) {
                $newdefs = $defs;
            }

            // Set the new panel defs from the fields that were just converted
            $paneldefs = array(array('label' => 'LBL_PANEL_DEFAULT', 'fields' => $fields));

            // Kill the data (old defs) and panels (new defs) elements from the defs
            unset($newdefs['data'], $newdefs['panels']);

            // Create, or recreate, the panel defs
            $newdefs['panels'] = $paneldefs;

            // Clean up the module name for saving
            $module = $this->getNormalizedModuleName();
            $this->logUpgradeStatus("Setting new $client:{$this->type} view defs internally for $module");
            // Setup the new defs
            $this->sidecarViewdefs[$module][$client]['view'][$this->viewtype] = $newdefs;
        }
    }

    /**
     * Handles the actual conversion of viewdefs. By default this method will 
     * only convert field defs without panel data to support original upgrading
     * from 6.5 -> 6.6 and to support portal and mobile conversion. 
     * 
     * @param array $defs The complete legacy viewdef
     * @param string $panelKey The viewdef key that contains panel data
     * @param boolean $full Flag that tells this method whether to return a 
     *                      single array of fields or a full conversion of defs
     * @return array
     */
    public function handleConversion($defs, $panelKey, $full = false)
    {
        $fields = $panels = array();
        if (isset($defs[$panelKey])) {
            $c = 0;
            
            // Necessary for setting the proper field array types
            $maxcols = isset($defs['templateMeta']['maxColumns']) ? intval($defs['templateMeta']['maxColumns']) : 2;
            foreach ($defs[$panelKey] as $label => $rows) {
                $cols = count($rows);
                // Assumption here is that Portal and Wireless will never have
                // more than 2 columns in the old setup
                if ($cols == 1) {
                    $displayParams = array('colspan' => $maxcols);
                    // Either a string field name or an instruction
                    if (is_string($rows[0])) {
                        if (!$this->isValidField($rows[0])) {
                            continue;
                        }
                        if ($maxcols == 1) {
                            $fields[] = $rows[0];
                        } else {
                            $fields[] = array('name' => $rows[0], 'displayParams' => $displayParams);
                        }
                    } else {
                        // Some sort of instruction set
                        if (is_array($rows[0])) {
                            if (isset($rows[0]['name'])) {
                                // Old style field now maps to name
                                $field = $rows[0]['name'];
                                if (!$this->isValidField($field)) {
                                    continue;
                                }
                                unset($rows[0]['name']);
                                $fields[] = array_merge(
                                    array('name' => $field),
                                    $rows[0],
                                    $maxcols == 1 ? array() : array('displayParams' => $displayParams)
                                );
                            } else {
                                // Fallback... take it as is
                                $fields[] = $rows[0];
                            }
                        }
                    }
                } else {
                    // We actually have the necessary col count
                    foreach ($rows as $row) {
                        foreach ($row as $field) {
                            if (is_string($field)) {
                                if (!$this->isValidField($field)) {
                                    continue;
                                }
                                $fields[] = $field;
                            } elseif (isset($field['name'])) {
                                if (!$this->isValidField($field['name'])) {
                                    continue;
                                }
                                $fields[] = $field['name'];
                            }
                        }
                    }
                }

                // For full conversion of metadata we need to group fields into 
                // their respective panels. This handles that here.
                if ($full) {
                    // Set the hidden flag for handling 'hide' property
                    $hidden = false;

                    // Handle panel naming and labeling
                    if (isset($this->panelNames[$c]['name'])) {
                        $panelName = $this->panelNames[$c]['name'];
                        $panelLabel = $this->panelNames[$c]['label'];

                        // Set the hide property?
                        if ($panelName == 'panel_hidden') {
                            $hidden = true;
                        }
                    } else {
                        $panelName = strtolower($label);
                        $panelLabel = strtoupper($label);
                    }

                    // Basic defs that should never change
                    $defs = array(
                        'name' => $panelName,
                        'label' => $panelLabel,
                        'columns' => $maxcols,
                        'labels' => true,
                        'labelsOnTop' => true,
                        'placeholders' => true,
                    );

                    // Handle the 'hide' property
                    if ($hidden) {
                        $defs['hide'] = true;
                    }

                    // Add in the fields array
                    $defs['fields'] = $fields;

                    // Build this panel's metadata
                    $panels[] = $defs;

                    // Reset fields array so they don't stack up inside of panels
                    $fields = array();
                }

                // Increment the counter that handles 
                $c++;
            }
        }

        // This is a full metadata conversion, so send back a complete metadata
        // collection
        if ($full) {
            return array(
                'templateMeta' => $defs['templateMeta'],
                'panels' => $panels
            );
        }

        // Return the default converted fields array
        return $fields;
    }
}
