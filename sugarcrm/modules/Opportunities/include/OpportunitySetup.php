<?php
//FILE SUGARCRM flav=ent ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Class OpportunitySetup
 */
abstract class OpportunitySetup
{
    /**
     * @var Opportunity
     */
    protected $bean;

    /**
     * Field Vardef setup
     *
     * @var array
     */
    protected $field_vardef_setup = array();

    /**
     * Where is the applications extension folder at
     *
     * @var string
     */
    protected $appExtFolder = 'custom/Extension/application/Ext';

    /**
     * Opportunity Extension Folder
     *
     * @var string
     */
    protected $moduleExtFolder = 'custom/Extension/modules/Opportunities/Ext';

    /**
     * RevenueLineItem Extension Folder
     *
     * @var string
     */
    protected $rliModuleExtFolder = 'custom/Extension/modules/RevenueLineItems/Ext';

    /**
     * Dupe Check Extension File Name
     *
     * @var string
     */
    protected $dupeCheckExtFile = 'dupe_check.ext.php';

    /**
     * RevenueLineItem Module Extension unhide file name
     *
     * @var string
     */
    protected $rliModuleExtFile = 'rli_unhide.ext.php';

    /**
     * RevenueLineItem Module Extension vardef dictionary change
     *
     * @var string
     */
    protected $rliModuleExtVardefFile = 'rli_vardef.ext.php';

    /**
     * What is the file name for the extends to disable the stock Opportunity Dependencies
     *
     * @var string
     */
    protected $oppModuleDependencyFile = 'opp_disable_dep.ext.php';

    /**
     * Where should we put the studio file at for RevenueLineItems
     *
     * @var string
     */
    protected $rliStudioFile = 'custom/modules/RevenueLineItems/metadata/studio.php';

    public function __construct()
    {
        SugarAutoLoader::load('modules/ModuleBuilder/parsers/ParserFactory.php');
        $this->bean = BeanFactory::getBean('Opportunities');
    }

    /**
     * Process the field vardefs as setup by the extending classes
     */
    protected function processFields()
    {
        // get the get_widget helper and the StandardField Helper
        SugarAutoLoader::load('modules/DynamicFields/FieldCases.php');
        SugarAutoLoader::load('modules/ModuleBuilder/parsers/StandardField.php');

        foreach ($this->field_vardef_setup as $field => $new_defs) {

            // get the field defs
            $field_defs = $this->bean->getFieldDefinition($field);
            // load the field type up
            $f = get_widget($field_defs['type']);

            // populate the row from the vardefs that were loaded and the new_defs
            $f->populateFromRow(array_merge($field_defs, $new_defs));

            // now lets save, since these are OOB field, we use StandardField
            $df = new StandardField($this->bean->module_name);
            $df->setup($this->bean);
            $f->module = $this->bean;
            $f->save($df);
        }
    }

    /**
     * Convert the Opportunity Module to be Using Opps w/ RLIs or Opps w/o RLI's
     *
     * @return mixed
     */
    public function doMetadataConvert()
    {
        // process the fields so we have all the vardefs changes first
        $this->processFields();

        // fix the dupe check as it changes the vardefs as well
        $this->fixOpportunityModule();

        // r&r the opp module
        $this->runRepairAndRebuild();

        // regenerate the Opportunity Vardefs
        VardefManager::loadVardef(
            $this->bean->module_dir,
            $this->bean->object_name,
            true,
            array('bean' => $this->bean)
        );

        $this->bean->clearLoadedDef($this->bean->object_name);

        $this->bean = BeanFactory::getBean('Opportunities');

        $rnr_modules = $this->fixRevenueLineItemModule();

        // lets fix the workflows module
        $this->processWorkFlows();

        // r&r the rli + related modules
        $this->runRepairAndRebuild($rnr_modules);

        register_shutdown_function(array('SugarAutoLoader', 'buildCache'));
    }

    /**
     * Utility method to run repair and rebuild on a set of modules.
     *
     * @param array $modules The list of modules
     */
    private function runRepairAndRebuild(array $modules = array('Opportunities'))
    {
        SugarAutoLoader::load('modules/Administration/QuickRepairAndRebuild.php');
        $rac = new RepairAndClear();
        $rac->show_output = false;
        $rac->module_list = $modules;
        $rac->clearVardefs();
        $rac->rebuildExtensions($modules);
    }

    /**
     * Add and Remove fields from the Record View
     *
     * @param array $fieldMap
     */
    protected function fixRecordView(array $fieldMap)
    {
        /* @var $gridDefParser SidecarGridLayoutMetaDataParser */
        $gridDefParser = ParserFactory::getParser(MB_RECORDVIEW, 'Opportunities', null, null, 'base');

        // no matter what we are going to add everything to the first panel at the end, SidecarGridLayoutMetaDataParser
        // doesn't have position capabilities...grrrr

        $fields = $gridDefParser->getAvailableFields();

        foreach ($fieldMap as $fieldName => $fieldAction) {
            if ($fieldAction === true) {
                // lets make sure the field is Available
                foreach ($fields as $k => $val) {
                    if ($val['name'] == $fieldName) {
                        $gridDefParser->addField($val);
                        break;
                    }
                }
            } else {
                if ($fieldAction === false) {
                    $gridDefParser->removeField($fieldName);
                }
            }
        }

        $gridDefParser->handleSave(false);
    }

    /**
     * Add and Remove fields from all the list views
     *
     * @param array $fieldMap
     */
    protected function fixListViews(array $fieldMap)
    {
        // get the views for the module
        $mm = MetadataManager::getManager();
        $views = $mm->getModuleViews('Opportunities');

        // fix the selected-list view
        $this->processSelectedListView($fieldMap);

        // fix the dupecheck-list view
        $this->processDupeCheckListView($fieldMap);

        // get the generic list view
        $this->processListView($fieldMap);

        $subpanel_modules = array('Opportunities');
        // look for anything that starts with 'subpanel-for-' and process it
        $look_for = 'subpanel-for-';
        foreach (array_keys($views) as $view) {
            if (strpos($view, $look_for) === 0) {
                // split on -
                $tmp = explode('-', $view);
                // we need to have at least 3 tiems.
                if (count($tmp) >= 3) {
                    // item 3 is the module name
                    $module = $this->findModuleName($tmp[2]);
                    // success we found a valid module name with the proper case
                    if ($module) {
                        // if we have 4 items, use the fourth as the link name, otherwise default to `opportunities`
                        $link = (isset($tmp[3])) ? $tmp[3] : 'opportunities';
                        // process that subpanel list view!
                        $this->processListView($fieldMap, $module, $link);
                        $subpanel_modules[] = $module;
                    }
                }
            }
        }

        // run repair and rebuild for all the modules that were touched
        $this->runRepairAndRebuild($subpanel_modules);

    }

    /**
     * Since the module name is lowercase, we need to find it in the keys of lowercase
     *
     * @param String $moduleToLookFor What module are we trying to find.
     * @return bool|String
     */
    protected function findModuleName($moduleToLookFor)
    {
        global $beanList;

        // do this here so we don't have to do it over and over again
        $moduleToLookFor = strtolower($moduleToLookFor);

        // find the correct bean module name
        foreach ($beanList as $beanModule => $beanName) {
            if (strtolower($beanModule) === $moduleToLookFor) {
                return $beanModule;
                break;
            }
        }

        // module not found;
        return false;
    }

    /**
     * Fix the `selected-list` view
     *
     * @param array $fieldMap
     */
    protected function processSelectedListView(array $fieldMap)
    {
        /* @var $listDefsParser SidecarListLayoutMetaDataParser */
        $listDefsParser = ParserFactory::getParser(MB_SIDECARPOPUPVIEW, 'Opportunities', null, null, 'base');
        $this->processList($fieldMap, $listDefsParser->_paneldefs, $listDefsParser);
    }

    /**
     * Fix the `dupecheck-list` view
     *
     * @param array $fieldMap
     */
    protected function processDupeCheckListView(array $fieldMap)
    {
        /* @var $listDefsParser SidecarListLayoutMetaDataParser */
        $listDefsParser = ParserFactory::getParser(MB_SIDECARDUPECHECKVIEW, 'Opportunities', null, null, 'base');
        $this->processList($fieldMap, $listDefsParser->_paneldefs, $listDefsParser);
    }

    /**
     * Fix the normal list view + any subpanels
     *
     * @param array $fieldMap
     * @param string $module
     * @param null $subpanel_name
     */
    protected function processListView(array $fieldMap, $module = 'Opportunities', $subpanel_name = null)
    {
        /* @var $listDefsParser SidecarListLayoutMetaDataParser */
        $listDefsParser = ParserFactory::getParser(MB_LISTVIEW, $module, null, $subpanel_name, 'base');
        $this->processList($fieldMap, $listDefsParser->_paneldefs, $listDefsParser);

    }

    /**
     * Process the ListView to set the fields correctly
     *
     * @param array $fieldMap
     * @param $current_fields
     * @param ListLayoutMetaDataParser $listParser
     */
    private function processList(array $fieldMap, $current_fields, ListLayoutMetaDataParser $listParser)
    {
        // make sure the list is reset
        $listParser->resetPanelFields();

        // process the fields
        foreach ($current_fields as $panel_id => $panel) {
            if (is_array($panel['fields'])) {
                foreach ($panel['fields'] as $field) {
                    $name = $field['name'];
                    $addField = true;
                    $additionalDefs = $field;
                    if (isset($fieldMap[$name])) {
                        if ($fieldMap[$name] !== false) {
                            // we have the field, so get it's defs
                            $defs = $this->bean->getFieldDefinition($fieldMap[$name]);
                            if ($defs) {
                                // set the name variable to the new field name.
                                $name = $fieldMap[$name];
                                // reset the additionDefs since we have a new field
                                $additionalDefs = array();
                            } else {
                                // we didn't find any defs for the new field, so error on caution and remove the old one
                                $addField = false;
                            }
                        } else {
                            // instead of a name being passed in, false was, so we should remove that field.
                            $addField = false;
                        }

                        unset($fieldMap[$name]);
                    }

                    if ($addField) {
                        $listParser->addField($name, $additionalDefs);
                    }
                }
            }
        }

        // make sure that the field map is empty, if it's not process any remaining fields
        if (!empty($fieldMap)) {
            foreach($fieldMap as $field => $trigger) {
                if($trigger === true) {
                    $defs = $this->bean->getFieldDefinition($field);
                    if ($defs) {
                        $listParser->addField($field, array());
                    }
                }
            }
        }

        $listParser->handleSave(false);
    }

    /**
     * Refresh the metadata cache for a given list of modules
     *
     * @param array $modules Which modules to refresh, if left empty it wil only do `Opportunities`
     */
    protected function refreshMetadataCache(array $modules = array())
    {
        // if empty, default it to Opportunities
        if (empty($modules)) {
            $modules[] = $this->bean->module_name;
        }
        MetaDataManager::refreshModulesCache($modules);
    }

    /**
     * Utility Method to know if forecasts is setup or not
     *
     * @return bool
     */
    protected function isForecastSetup()
    {
        $settings = Forecast::getSettings();

        return ($settings['is_setup'] == 1);
    }

    /**
     * Reset the forecast data.
     *
     * @param string $forecast_by What are we going to be forecasting by now
     */
    protected function resetForecastData($forecast_by)
    {
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'forecast_by', $forecast_by, 'base');

        SugarAutoLoader::load('modules/Forecasts/include/ForecastReset.php');
        $forecast_reset = new ForecastReset();
        $forecast_reset->truncateForecastData();
        $forecast_reset->setDefaultWorksheetColumns($forecast_by);

        // reload the settings
        Forecast::getSettings(true);
    }

    /**
     * Hide or show the navigation tab.
     *
     * @param bool $show Should we show the tab or not, defaults to `true`
     */
    protected function setRevenueLineItemModuleTab($show = true)
    {
        $this->setRevenueLineItemTab($show);
        // for ths one, we have to reverse show, since if we want to show it, it needs not be in the list
        // and if we want to hide it, it needs to not be in the list
        $this->setConfigSetting('hide_subpanels', 'revenuelineitems', !$show);

        sugar_cache_clear('admin_settings_cache');
    }

    protected function setRevenueLineItemTab($show)
    {
        SugarAutoLoader::load('modules/MySettings/TabController.php');
        $newTB = new TabController();

        //grab the existing system tabs
        $tabs = $newTB->get_system_tabs();

        if ($show) {
            $tabs['RevenueLineItems'] = 'RevenueLineItems';
        } else {
            unset($tabs['RevenueLineItems']);
        }

        //now assign the modules to system tabs
        $newTB->set_system_tabs($tabs);
    }

    /**
     * @param $setting
     * @param $value
     * @param bool $show
     */
    protected function setConfigSetting($setting, $value, $show = true)
    {
        $db = DBManagerFactory::getInstance();
        $sql = "SELECT value FROM config
                WHERE category = 'MySettings'
                AND name = '" . $setting . "'
                AND (platform = 'base' OR platform IS NULL OR platform = '')";
        $results = $db->query($sql);

        while ($row = $db->fetchRow($results)) {
            $tabArray = unserialize(base64_decode($row['value']));
            // find the key
            $key = array_search($value, $tabArray);
            if ($key === false && $show === true) {
                $tabArray[] = $value;
            } elseif ($key !== false & $show === false) {
                unset($tabArray[$key]);
            }

            $sql = "UPDATE config
                SET value = '" . base64_encode(serialize($tabArray)) . "'
                WHERE category = 'MySettings'
                AND name = '" . $setting . "'
                AND (platform = 'base' OR platform IS NULL OR platform = '')";
            $db->query($sql);
            $db->commit();
        }
    }

    /**
     * Add or Remove the RevenueLineItems Module to the Parent Type dropdown List
     *
     * @param bool $add Defaults to `true`
     */
    protected function setRevenueLineItemInParentRelateDropDown($add = true)
    {
        $rli = BeanFactory::getBean('RevenueLineItems');

        // get the default system language
        $default_lang = SugarConfig::getInstance()->get('default_language');

        // get the default app_list_strings and the default language for Revenue Line Items
        $app_list_stings = return_app_list_strings_language($default_lang);
        $module_lang = return_module_language($default_lang, 'RevenueLineItems');

        // What lists need updating
        $listsToUpdate = array(
            'moduleList',
            'parent_type_display'
        );

        // load the Dropdown parser so it can easily be saved
        SugarAutoLoader::load('modules/ModuleBuilder/parsers/parser.dropdown.php');
        $dd_parser = new ParserDropDown();

        foreach($listsToUpdate as $list_key) {
            $list = $app_list_stings[$list_key];
            $hasRLI = isset($list[$rli->module_name]);

            if ($add && !$hasRLI) {
            // get the translated value
                $list[$rli->module_name] = $module_lang['LBL_MODULE_NAME'];
                $GLOBALS['app_list_strings'][$list_key][$rli->module_name] = $module_lang['LBL_MODULE_NAME'];
            } elseif (!$add && $hasRLI) {
                unset($GLOBALS['app_list_strings'][$list_key][$rli->module_name]);
                unset($list[$rli->module_name]);
            }

            // the parser need all the values to be in their own array with the key first then the value
            $new_list = array();
            foreach($list as $k => $v) {
                $new_list[] = array($k, $v);
            }

            $params = array(
                'dropdown_name' => $list_key,
                'dropdown_lang' => $default_lang,
                'list_value' => json_encode($new_list),
                'view_package' => 'studio',
            );
            // for some reason, the ParserDropDown class uses $_REQUEST vs getting it from what
            // was passed in.
            $_REQUEST['view_package'] = 'studio';

            $dd_parser->saveDropDown($params);

            // clean up the request object
            unset($_REQUEST['view_package']);
        }
    }

    protected function toggleRevenueLineItemQuickCreate($enable = false)
    {
        SugarAutoLoader::load('modules/Administration/views/view.configureshortcutbar.php');
        $cscb = new ViewConfigureshortcutbar();

        $modules = $cscb->getQuickCreateModules();

        $enModules = array();
        foreach ($modules['enabled'] as $module => $def) {
            $enModules[$module] = $def['order'];
        }

        $hasRLI = isset($enModules['RevenueLineItems']);
        if ($enable === true && $hasRLI === false) {
            $enModules['RevenueLineItems'] = count($enModules);
        } elseif ($enable === false && $hasRLI === true) {
            unset($enModules['RevenueLineItems']);
        }

        $cscb->saveChangesToQuickCreateMetadata($modules['enabled'], $modules['disabled'], $enModules);
    }

    /**
     * Process WorkFlows
     *
     * This will mark any WorkFlows based on the Opportunity Module as Inactive so they don't run and potentially blow
     * up after the convert.
     *
     * @throws SugarQueryException
     */
    protected function processWorkFlows()
    {
        $this->markWorkFlowsWithOppActionShellsInactive();
        $this->markWorkFlowsWithOppAlertShellsInactive();
        $this->markWorkFlowsWithOppTriggerShellsInactive();

        // mark all WorkFlows with their base of opportunities as status '0' (Inactive)
        /* @var $workFlow WorkFlow */
        $workFlow = BeanFactory::getBean('WorkFlow');
        $sq = new SugarQuery();
        $sq->select(array('id'));
        $sq->from($workFlow);
        $sq->where()
            ->equals('status', 1)
            ->equals('base_module', $this->bean->module_name);

        $rows = $sq->execute();

        // now mark all the WorkFlows that were found as In-Active (status = 0)
        foreach ($rows as $row) {
            $workFlow->retrieve($row['id']);
            $workFlow->status = 0;
            $workFlow->save(false);
            $workFlow->write_workflow();
        }
    }

    /**
     * Find any Action Shells for the Opportunity Module and mark it's related workflow inactive
     *
     * @throws SugarQueryException
     */
    private function markWorkFlowsWithOppActionShellsInactive()
    {
        // get the action shells
        $actionShells = BeanFactory::getBean('WorkFlowActionShells');

        $sq = new SugarQuery();
        $sq->select(array('id', 'parent_id'));
        $sq->from($actionShells);
        $sq->where()
            ->queryOr()
                ->equals('rel_module', 'opportunities')
                ->equals('action_module', 'opportunities');

        $rows = $sq->execute();

        foreach ($rows as $row) {
            $actionShells->retrieve($row['id']);
            $workflow = $actionShells->get_workflow_object();
            $workflow->status = 0;
            $workflow->save();
            $workflow->write_workflow();
        }
    }

    /**
     * Find any Alert Shells for the Opportunity Module and Mark it's related workflow inactive
     *
     * @throws SugarQueryException
     */
    private function markWorkFlowsWithOppAlertShellsInactive()
    {
        // get the action shells
        $alertShells = BeanFactory::getBean('WorkFlowAlertShells');

        $sq = new SugarQuery();
        $sq->select(array('id', 'parent_id'));
        $sq->from($alertShells);
        $sq->where()
            ->queryOr()
            ->equals('rel_module2', 'opportunities')
            ->equals('rel_module2', 'opportunities');

        $rows = $sq->execute();

        foreach ($rows as $row) {
            $alertShells->retrieve($row['id']);
            $workflow = $alertShells->get_workflow_object();
            $workflow->status = 0;
            $workflow->save();
            $workflow->write_workflow();
        }
    }

    /**
     * Find any Trigger Shells for the Opportunity Module and Mark it's related workflow inactive
     *
     * @throws SugarQueryException
     */
    private function markWorkFlowsWithOppTriggerShellsInactive()
    {
        // get the action shells
        $triggerShells = BeanFactory::getBean('WorkFlowTriggerShells');

        $sq = new SugarQuery();
        $sq->select(array('id', 'parent_id'));
        $sq->from($triggerShells);
        $sq->where()
            ->equals('rel_module', 'opportunities');

        $rows = $sq->execute();

        foreach ($rows as $row) {
            $triggerShells->retrieve($row['id']);
            $workflow = $triggerShells->get_workflow_object();
            $workflow->status = 0;
            $workflow->save();
            $workflow->write_workflow();
        }
    }

    protected function toggleRevenueLineItemsLinkInWorkFlows($show = false)
    {
        // make sure all the links are visible in workflows
        /* @var $rli_bean = RevenueLineItem */
        $rli_bean  = BeanFactory::getBean('RevenueLineItems');
        $rli_links = $rli_bean->get_linked_fields();

        $rnr_modules = array();

        foreach($rli_links as $name => $link) {
            if ($rli_bean->load_relationship($name) && $rli_bean->$name instanceof Link2) {
                $bean = BeanFactory::getBean($rli_bean->$name->getRelatedModuleName());
                $rel_name = $rli_bean->$name->getRelatedModuleLinkName();

                // if for some reason we didn't find a rli_name on the other side of the link
                // we should just ignore it
                if (empty($rel_name)) {
                    continue;
                }

                $file = 'rli_link_workflow.php';
                $folder = "custom/Extension/modules/{$bean->module_dir}/Ext";

                SugarAutoLoader::ensureDir($folder . '/Vardefs');

                if ($show === true) {
                    $file_contents = <<<EOL
<?php
\$dictionary['{$bean->object_name}']['fields']['{$rel_name}']['workflow'] = true;
EOL;

                    sugar_file_put_contents($folder . '/Vardefs/' . $file, $file_contents);
                } else {
                    if (SugarAutoLoader::fileExists($folder . '/Vardefs/' . $file)) {
                        // since we don't what to show it, just remove the file as it defaults
                        // to false out of the box.
                        SugarAutoLoader::unlink($folder . '/Vardefs/' . $file);
                    }
                }

                $rnr_modules[] = $bean->module_name;
            }
        }

        return $rnr_modules;
    }

    abstract public function doDataConvert();

    abstract protected function fixRevenueLineItemModule();

    /**
     * Any Custom Logic for the Opportunity Module
     */
    abstract protected function fixOpportunityModule();
}
