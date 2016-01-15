<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Include the Tab Controller since we will need this
 */
require_once 'modules/MySettings/TabController.php';

/**
 * Add new modules to the megamenu (tab controller)
 */
class SugarUpgradeAddNewModulesToMegamenu extends UpgradeScript
{
    public $order = 4001;
    public $type = self::UPGRADE_DB;

    /**
     * Array of arrays of defs for all new modules. Each array must define at
     * least a modules array. Additionally, the def array can define one of the
     * following attributes:
     *  - fromFlavor
     *  - toFlavor
     *  - fromVersion (this MUST be an array which specifies a version and evaluator)
     *  - toVersion (this MUST be an array which specifies a version and evaluator)
     * 
     * These attributes will be checked to confirm that the upgrade criteria are
     * met before adding the modules to the tab controller.
     *
     * @var array
     */
    public $newModuleDefs = array(
        array(
            'name' => 'Tags Module',
            'fromVersion' => array('7.7.0', '<'),
            'modules' => array(
                'Tags',
            ),
        ),
        array(
            'name' => 'PMSE Modules',
            'toFlavor' => 'ent',
            'fromVersion' => array('7.6.0', '<'),
            'modules' => array(
                'pmse_Project',
                'pmse_Inbox',
                'pmse_Business_Rules',
                'pmse_Emails_Templates',
            ),
        ),
    );

    public function run()
    {
        foreach ($this->newModuleDefs as $def) {
            // Build our boolean criteria check
            $check = $this->buildCheckCriteria($def);

            // If we are good to go, then go
            if (!$check) {
                continue;
            }

            // Get the tab controller object
            $tc = $this->getTabController();

            // Get the newly added modules mixed with existing tabs
            $tabs = $this->getModifiedTabs($tc, $def);

            // Save the module list
            $this->saveModifiedTabs($tc, $tabs);

            // Get the log message
            $logMessage = $this->getMessageToLog($def);

            // Log it and be done
            $this->log($logMessage);
        }
    }

    /**
     * Builds a conditional evaluation for checking if the criteria for the upgrade
     * is met based on the defs of the new module(s)
     *
     * @param Array $def New module def
     * @return boolean
     */
    public function buildCheckCriteria(Array $def)
    {
        // First check is to ensure the modules array
        if (!isset($def['modules'])) {
            return false;
        }

        // Initialize our criteria to true, since what is required is already
        // passed
        $check = true;

        // Handle the froms and tos
        if (isset($def['fromFlavor'])) {
            $check = $check && $this->fromFlavor($def['fromFlavor']);
        }

        if (isset($def['toFlavor'])) {
            $check = $check && $this->toFlavor($def['toFlavor']);
        }

        // From and To version criteria MUST specify a version and evaluator
        if (isset($def['fromVersion'])) {
            $check = $check && version_compare($this->from_version, $def['fromVersion'][0], $def['fromVersion'][1]);
        }

        if (isset($def['toVersion'])) {
            $check = $check && version_compare($this->to_version, $def['toVersion'][0], $def['toVersion'][1]);
        }

        return $check;
    }

    /**
     * Gets a new TabController object, encapsulated into its own method for
     * testability.
     *
     * @return TabController
     */
    public function getTabController()
    {
        return new TabController();
    }

    /**
     * Gets the existing tabs with the new modules added into them
     *
     * @param TabController $tc TabController object
     * @param Array $def New module definition
     */
    public function getModifiedTabs(TabController $tc, Array $def)
    {
        // Get the existing tabs
        $tabs = $tc->get_system_tabs();

        // Add in our new modules
        foreach ($def['modules'] as $m) {
            $tabs[$m] = $m;
        }

        return $tabs;
    }

    /**
     * Saves the modified tab list
     *
     * @param TabController $tc TabController object
     * @param Array $tabs Array of new modules to be saved to the tab list
     */
    public function saveModifiedTabs(TabController $tc, Array $tabs)
    {
        $tc->set_system_tabs($tabs);
    }

    /**
     * Gets a log message based on the def
     *
     * @param array $def New modules array def
     * @return string
     */
    public function getMessageToLog($def)
    {
        // Build a log message, sort of abstract at first...
        $logMessage = 'Megamenu module list updated';

        // But if there is a name for this addition, a little less abstract
        if (isset($def['name'])) {
            $logMessage .= " with $def[name]";
        }

        return $logMessage;
    }
}
