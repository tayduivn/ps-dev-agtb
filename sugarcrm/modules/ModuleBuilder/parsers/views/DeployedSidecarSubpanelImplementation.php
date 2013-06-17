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
     * @param string $subpanelName
     * @param string $moduleName
     * @param string $packageName
     * @param string $client
     */
    public function __construct($subpanelName, $moduleName, $client = 'base')
    {
        $GLOBALS['log']->debug(get_class($this) . "->__construct($subpanelName , $moduleName)");
        $this->mdc = new MetaDataConverter();
        $this->_subpanelName = $subpanelName;
        $this->_moduleName = $moduleName;
        $this->bean = BeanFactory::getBean($moduleName);
        $this->setViewClient($client);
        if (empty($this->bean)) {
            throw new Exception("Bean was not provided for {$subpanelName}");
        }

        $this->historyPathname = 'custom/history/modules/' . $moduleName . '/clients/' . $this->getViewClient(
            ) . '/views/' . $subpanelName . '/' . self::HISTORYFILENAME;
        $this->_history = new History($this->historyPathname);

        if ($subpanelName != 'default' && !stristr($subpanelName, 'for')) {
            $subpanelName = 'For' . ucfirst($subpanelName);
        }
        $this->sidecarSubpanelName = $this->mdc->fromLegacySubpanelName($subpanelName);
        $subpanelFile = "modules/{$moduleName}/clients/" . $this->getViewClient(
            ) . "/views/{$this->sidecarSubpanelName}/{$this->sidecarSubpanelName}.php";

        $defaultSubpanelFile = "modules/{$moduleName}/clients/base/views/subpanel-list/subpanel-list.php";
        $this->loadedSupbanelName = $this->sidecarSubpanelName;

        // using includes because require_once causes an empty array
        if (file_exists('custom/' . $subpanelFile)) {
            include 'custom/' . $subpanelFile;
        } elseif (file_exists($subpanelFile)) {
            include $subpanelFile;
        } elseif (file_exists($defaultSubpanelFile)) {
            include $defaultSubpanelFile;
            $this->loadedSupbanelName = 'subpanel-list';
        } else {
            throw new Exception(sprintf("No file found for subpanel: %s", $subpanelName));
        }

        $this->sidecarFile = "custom/" . $subpanelFile;

        if (file_exists($this->historyPathname)) {
            // load in the subpanelDefOverride from the history file
            $GLOBALS['log']->debug(get_class($this) . ": loading from history");
            require $this->historyPathname;
        }
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
     * Get the correct viewdefs from the array in the file
     * @param array $viewDefs
     * @param string $moduleName
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
        write_array_to_file(
            "viewdefs['{$this->_moduleName}']['{$this->_viewClient}']['view']['{$this->sidecarSubpanelName}']",
            $this->_viewdefs,
            $this->sidecarFile
        );
    }

}
