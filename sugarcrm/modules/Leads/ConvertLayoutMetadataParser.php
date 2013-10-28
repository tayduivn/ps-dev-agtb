<?php
//FILE SUGARCRM flav=pro ONLY
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
require_once 'modules/ModuleBuilder/parsers/views/GridLayoutMetaDataParser.php';
require_once 'modules/ModuleBuilder/parsers/views/History.php';

class ConvertLayoutMetadataParser extends GridLayoutMetaDataParser
{
    protected $pathMap = array(
        MB_BASEMETADATALOCATION => '',
        MB_CUSTOMMETADATALOCATION => 'custom/',
        MB_WORKINGMETADATALOCATION => 'custom/working/',
        MB_HISTORYMETADATALOCATION => 'custom/history/'
    );
    protected $fileName = "modules/Leads/clients/base/layouts/convert-main/convert-main.php";
    protected $_convertdefs; //lead convert metadata pulled out for convenience
    protected $defaultModuleDefSettings = array(
        'required' => false,
        'copyData' => false,
        'duplicateCheckOnStart' => false,
    );

    public function __construct($module)
    {
        $this->FILLER = array(
            'name' => MBConstants::$FILLER['name'],
            'label' => translate(MBConstants::$FILLER['label'])
        );
        $this->seed = BeanFactory::getBean($module);
        $this->_moduleName = $module;
        $this->_view = MB_EDITVIEW;
        $this->_fielddefs = $this->seed->field_defs;
        $this->loadViewDefs();
        $this->_history = new History($this->fileName);
    }

    public function getOriginalViewDefs()
    {
        $viewdefs = array();
        //load from the original file only
        include($this->fileName);
        return $viewdefs;
    }

    public function getLanguage()
    {
        return "";
    }

    public function getHistory()
    {
        return $this->_history;
    }

    /**
     * Override parent and noop - we don't use this for convert lead
     *
     * @param bool $populate
     */
    public function handleSave($populate = true)
    {
    }

    /**
     * Should take in an updated set of definitions, reorder and override the current panel set with the new one.
     *
     * @param array $data
     */
    public function updateConvertDef($data)
    {
        //First check if Opportunities is in the list
        $hasOpp = false;
        foreach ($data as $newDef) {
            if (!empty($newDef['module']) && $newDef['module'] === 'Opportunities') {
                $hasOpp = true;
                break;
            }
        }

        //Create the new convertdefs, replacing any properties in the modules with the ones from the request
        $final = array();
        foreach ($data as $newDef) {
            if (empty($newDef['module'])) {
                continue;
            }
            $existingDef = $this->getDefForModule($newDef['module']);
            if ($existingDef) {
                foreach ($existingDef as $key => $item) {
                    if (!isset($newDef[$key])) {
                        $newDef[$key] = $item;
                    }
                }
            }
            //if Opp is in the list, Account must be set to required
            if ($newDef['module'] === 'Accounts' && $hasOpp) {
                $newDef['required'] = true;
            }
            $final[] = $newDef;
        }
        $this->_convertdefs['modules'] = $final;
        $this->deploy();
    }

    /**
     * Remove given module from the viewdefs module list
     *
     * @param string $module
     */
    public function removeLayout($module)
    {
        $moduleDefs = array();
        $newModuleDefs = array();
        if (isset($this->_convertdefs['modules'])) {
            $moduleDefs = $this->_convertdefs['modules'];
        }

        foreach($moduleDefs as $moduleDef) {
            if ($moduleDef['module'] !== $module) {
                $newModuleDefs[] = $moduleDef;
            }
        }

        $this->_convertdefs['modules'] = $newModuleDefs;
        $this->deploy();
    }

    /**
     * Deploy the convert defs
     */
    protected function deploy()
    {
        // when we deploy get rid of the working file; we have the changes in the MB_CUSTOMMETADATALOCATION so no need for a redundant copy in MB_WORKINGMETADATALOCATION
        // this also simplifies manual editing of layouts. You can now switch back and forth between Studio and manual changes without having to keep these two locations in sync
        $workingFilename = $this->pathMap[MB_WORKINGMETADATALOCATION] . $this->fileName;
        if (file_exists($workingFilename)) {
            unlink($workingFilename);
        }

        $filename = $this->pathMap[MB_CUSTOMMETADATALOCATION] . $this->fileName;
        $GLOBALS['log']->debug(get_class($this) . "->deploy(): writing to " . $filename);
        $this->setConvertDef($this->_convertdefs);
        $this->_saveToFile($filename, $this->_viewdefs);
    }

    /**
     * Save the viewdefs to a file
     *
     * @param string $filename
     * @param array $defs
     */
    protected function _saveToFile($filename, $defs)
    {
        mkdir_recursive(dirname($filename));
        // create the new metadata file contents, and write it out
        if (!write_array_to_file('viewdefs', $defs, $filename)) {
            $GLOBALS ['log']->fatal(get_class($this) . ": could not write new viewdef file " . $filename);
        }
    }

    /**
     * Load convert lead viewdefs from custom file if exists, base file otherwise
     */
    protected function loadViewDefs()
    {
        $viewdefs = array();
        $viewDefFile = SugarAutoLoader::existingCustomOne($this->fileName);
        include($viewDefFile);
        $this->_viewdefs = $viewdefs;
        $this->_convertdefs = $this->getConvertDef($this->_viewdefs);
    }

    public function getDefForModules()
    {
        return $this->_convertdefs['modules'];
    }

    /**
     * Convert metadata contains array of modules, retrieve a specific module def
     *
     * @param $module module name
     * @param null $convertDefs
     * @return bool
     */
    public function getDefForModule($module, $convertDefs = null)
    {
        if (is_null($convertDefs)) {
            $convertDefs = $this->_convertdefs;
        }
        $moduleDef = false;
        foreach($convertDefs['modules'] as $def) {
            if ($def['module'] === $module) {
                $moduleDef = $def;
            }
        }

        return $moduleDef;
    }

    public function getDefaultDefForModule($module)
    {
        $originalViewDef = $this->getOriginalViewDefs();
        $originalConvertDef = $this->getConvertDef($originalViewDef);
        $defaultModuleDef = array_merge(array('module' => $module), $this->defaultModuleDefSettings);
        $moduleDef = $this->getDefForModule($module, $originalConvertDef);
        return $moduleDef ? $moduleDef : $defaultModuleDef;
    }

    /**
     * Get whether to use tabs in create form (we don't use tabs on lead convert)
     *
     * @return bool
     */
    public function getUseTabs()
    {
        return false;
    }

    /**
     * Set whether to use tabs in create form (leave false status - we don't use tabs on lead convert)
     *
     * @param bool $useTabs
     */
    public function setUseTabs($useTabs)
    {
    }

    /**
     * Retrieve the convert def from the viewdefs
     *
     * @param array $viewdefs
     * @return array
     */
    protected function getConvertDef($viewdefs)
    {
        if (isset($viewdefs['Leads']['base']['layout']['convert-main'])) {
            return $viewdefs['Leads']['base']['layout']['convert-main'];
        } else {
            return array();
        }
    }

    /**
     * Set the convert def at the appropriate location on the viewdef
     *
     * @param array $convertDef
     */
    protected function setConvertDef($convertDef)
    {
        $this->_viewdefs['Leads']['base']['layout']['convert-main'] = $convertDef;
    }
}
