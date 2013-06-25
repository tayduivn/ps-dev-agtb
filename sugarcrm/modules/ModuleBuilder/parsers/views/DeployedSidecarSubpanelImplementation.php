<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'modules/ModuleBuilder/parsers/views/MetaDataImplementationInterface.php';
require_once 'modules/ModuleBuilder/parsers/views/AbstractMetaDataImplementation.php';
require_once 'modules/ModuleBuilder/parsers/constants.php';
require_once 'include/MetaDataManager/MetaDataConverter.php';

class DeployedSidecarSubpanelImplementation extends AbstractMetaDataImplementation implements MetaDataImplementationInterface
{

    const HISTORYFILENAME = 'restored.php';
    const HISTORYVARIABLENAME = 'viewdefs';

    /**
     * The constructor
     * @param string $linkName
     * @param string $loadedModule - Accounts
     * @param string $client - base
     */
    public function __construct($linkName, $loadedModule, $client = 'base')
    {
        $GLOBALS['log']->debug(get_class($this) . "->__construct($linkName , $loadedModule)");

        $this->mdc = new MetaDataConverter();
        $this->loadedModule = $loadedModule;
        $this->linkName = $linkName;
        $this->legacySubpanelName = 'For' . $loadedModule;
        // get the link and the related module name as the module we need the subpanel from
        $bean = BeanFactory::getBean($loadedModule);
        $link = new Link2($linkName, $bean);
        $moduleName = $link->getRelatedModuleName();
        $this->_moduleName = $moduleName;
        $this->bean = BeanFactory::getBean($moduleName);
        $this->setViewClient($client);
        $this->setUpSubpanelViewDefFileInfo();


        if (empty($this->bean)) {
            throw new Exception("Bean was not provided for {$this->sidecarSubpanelName}");
        }

        $this->historyPathname = 'custom/history/modules/' . $moduleName . '/clients/' . $this->getViewClient(
            ) . '/views/' . $this->sidecarSubpanelName. '/' . self::HISTORYFILENAME;
        $this->_history = new History($this->historyPathname);


        if (file_exists($this->historyPathname)) {
            // load in the subpanelDefOverride from the history file
            $GLOBALS['log']->debug(get_class($this) . ": loading from history");
            require $this->historyPathname;
        }


        if (empty($this->loadedSubpanelFileName)) {
            throw new Exception(sprintf("No valid file for subpanel '%s'", $this->sidecarSubpanelName));
        }

        @include $this->loadedSubpanelFileName;

        $this->_viewdefs = !empty($viewdefs) ? $this->getNewViewDefs($viewdefs) : array();
        $this->_fielddefs = $this->bean->field_defs;
        $this->_language = '';
        // don't attempt to access the template_instance property if our subpanel represents a collection, as it won't be there - the sub-sub-panels get this value instead
        if (isset($this->_viewdefs['type']) && $this->_viewdefs['type'] != 'collection') {
            $this->_language = $this->bean->module_dir;
        }
        // Make sure the paneldefs are proper if there are any
        $this->_paneldefs = isset($this->_viewdefs['panels']) ? $this->_viewdefs['panels'] : array();
    }

    /**
     * Sets up the class vars for the file information
     * @return bool
     * @throws Exception
     */
    protected function setupSubpanelViewDefFileInfo()
    {
        $this->sidecarSubpanelName = $this->mdc->fromLegacySubpanelName($this->legacySubpanelName);

        // check if there is an override
        $layoutFiles = array(
            "modules/{$this->loadedModule}/clients/" . $this->getViewClient() . "/layouts/subpanels/subpanels.php",
        );
        $layoutExtensionName = array(
            "sidecarsubpanel" . $this->getViewClient() . "layout",
        );

        if ($this->getViewClient() !== 'base') {
            $layoutFiles[] = "modules/{$this->loadedModule}/clients/base/layouts/subpanels/subpanels.php";
            $layoutExtensionName[] = "sidecarsubpanelbaselayout";
        }
        foreach ($layoutFiles as $file) {
            @include $file;
        }
        foreach ($layoutExtensionName as $extension) {
            $file = SugarAutoLoader::loadExtension($extension, $this->_moduleName);
            if ($file !== false) {
                @include $file;
            }
        }

        if(!empty($viewdefs[$this->loadedModule]['base']['layout']['subpanels']['components'])) {

            $components = $viewdefs[$this->loadedModule]['base']['layout']['subpanels']['components'];

            foreach ($components as $key => $component) {
                if (empty($component['override_subpanel_list_view'])) {
                    continue;
                }
                if ($component['override_subpanel_list_view']['link'] == $this->linkName) {
                    $this->sidecarSubpanelName = "subpanel-for-{$this->loadedModule}-{$this->linkName}";
                    $this->loadedSupbanelName = $component['override_subpanel_list_view']['view'];
                    $this->loadedSubpanelFileName = file_exists("custom/{$this->_moduleName}/clients/" . $this->getViewClient() . "/views/{$this->loadedSupbanelName}/{$this->loadedSupbanelName}.php") ? "custom/{$this->_moduleName}/clients/" . $this->getViewClient() . "/views/{$this->loadedSupbanelName}/{$this->loadedSupbanelName}.php" : "{$this->_moduleName}/clients/" . $this->getViewClient() . "/views/{$this->loadedSupbanelName}/{$this->loadedSupbanelName}.php";
                    $this->sidecarFile = "custom/{$this->_moduleName}/clients/" . $this->getViewClient() . "/views/{$this->sidecarSubpanelName}/{$this->sidecarSubpanelName}.php";
                    $this->overrideArrayKey = $key;
                    return true;
                }
            }
        }

        $subpanelFile = "modules/{$this->_moduleName}/clients/" . $this->getViewClient(
            ) . "/views/{$this->sidecarSubpanelName}/{$this->sidecarSubpanelName}.php";

        $defaultSubpanelFile = "modules/{$this->_moduleName}/clients/base/views/subpanel-list/subpanel-list.php";
        $this->loadedSupbanelName = $this->sidecarSubpanelName;

        // using includes because require_once causes an empty array
        if (file_exists('custom/' . $subpanelFile)) {
            $this->loadedSubpanelFileName = 'custom/' . $subpanelFile;
        } elseif (file_exists($subpanelFile)) {
            $this->loadedSubpanelFileName = $subpanelFile;
        } elseif (file_exists($defaultSubpanelFile)) {
            $this->loadedSubpanelFileName = $defaultSubpanelFile;
            $this->loadedSupbanelName = 'subpanel-list';
        } else {
            throw new Exception(sprintf("No file found for subpanel: %s", $this->loadedSupbanelName));
        }
        $this->sidecarFile = "custom/" . $subpanelFile;
    }

    /**
     * Get the correct viewdefs from the array in the file
     * @param array $viewDefs
     * @return array
     */
    public function getNewViewDefs(array $viewDefs)
    {
        if (isset($viewDefs[$this->_moduleName][$this->_viewClient]['view'][$this->loadedSupbanelName])) {
            return $viewDefs[$this->_moduleName][$this->_viewClient]['view'][$this->loadedSupbanelName];
        }

        return array();
    }

    /**
     * Getter for the fielddefs
     * @return array
     */
    public function getFieldDefs()
    {
        return $this->_fielddefs;
    }

    /**
     * Getter for the language
     * @return string
     */
    public function getLanguage()
    {
        return $this->_language;
    }

    /*
     * Save a definition that will be used to display a subpanel for $this->_moduleName
     * @param array defs Layout definition in the same format as received by the constructor
     */

    public function deploy($defs)
    {
        // first sort out the historical record...
        write_array_to_file(self::HISTORYVARIABLENAME, $this->_viewdefs, $this->historyPathname);
        $this->_history->append($this->historyPathname);
        $this->_viewdefs = $defs;

        if (!is_dir(dirname($this->sidecarFile))) {
            if (!mkdir(dirname($this->sidecarFile), 0755, true)) {
                throw new Exception(sprintf("Cannot create directory %s", $this->sidecarFile));
            }
        }

        // always set the type to subpanel-list for the client
        if (strpos($this->sidecarSubpanelName, 'subpanel-for-')) {
            $this->_viewdefs['type'] = 'subpanel-list';
        }

        write_array_to_file(
            "viewdefs['{$this->_moduleName}']['{$this->_viewClient}']['view']['{$this->sidecarSubpanelName}']",
            $this->_viewdefs,
            $this->sidecarFile
        );


    }

}
