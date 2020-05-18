<?php
//FILE SUGARCRM flav=ent ONLY
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
 * Will make 2 sets of changes:
 * 1. Swaps the side-drawer type to one which has capability to switch the drawer dashboard to edit mode.
 * 2. Will re-add the remove buttons to the activity timeline dashlets for the above dashboards.
 * The changes are necessary to be able to render the console dashboard multi line list side drawer dashboards
 * in edit mode.
 * This script is also supposed to take into account and client made modifications.
 */
class SugarUpgradeEnableConsoleDashboardEdits extends UpgradeScript
{
    public $order = 7560;
    public $type = self::UPGRADE_DB;

    private $consoleIDs = [
        'c108bb4a-775a-11e9-b570-f218983a1c3e', // Service Console
        'da438c86-df5e-11e9-9801-3c15c2c53980', // Renewals Console
    ];
    private $multiLineDashBoardIDs = [
        'c290ef46-7606-11e9-9129-f218983a1c3e', // Cases
        'd8f610a0-e950-11e9-81b4-2a2ae2dbcce4', // Accounts
        '069a1142-61bf-473f-8014-faca9aaf43cf', // Opportunities
    ];

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        $isUpgradeTo1010 = version_compare($this->from_version, '10.1.0', '<');
        if ($isUpgradeTo1010 && $this->toFlavor('ent')) {
            foreach ($this->consoleIDs as $id) {
                $this->changeSideDrawerType($id);
            }

            foreach ($this->multiLineDashBoardIDs as $id) {
                $this->addDashletRemoveButton($id);
            }

            $this->log('Updated console dashboards, their side-drawers should be editable.');
        } else {
            $this->log('Did not update console dashboards, their side-drawers will not be editable.');
        }
    }

    /**
     * On console dashboards changes the side drawer type to one that supports dashboard editing.
     *
     * @param {String} $id The id of a dashboard.
     */
    public function changeSideDrawerType($id)
    {
        // Read the Dashoard we want to modify
        $dashboardController = new Dashboard();
        $dashboardModel = $dashboardController->retrieve($id);
        $dashboard = json_decode($dashboardModel->metadata);

        // Iterate dashlets and find any tabs with side drawer layouts.
        // When found change the type to console-side-drawer.
        $hasChanges = false;
        foreach ($dashboard->tabs as $tab) {
            foreach ($tab->components as $component) {
                if (isset($component->layout)
                    && isset($component->layout->type)
                    && $component->layout->type === 'side-drawer') {
                        $hasChanges = true;
                        $component->layout->type = 'console-side-drawer';
                }
            }
        }

        // If we have changed the side drawer type, save it.
        if ($hasChanges) {
            $dashboardModel->metadata = json_encode($dashboard);
            $dashboardModel->save();
            $this->log('Updated a side drawer type to be console-side-drawer.');
        }
    }

    /**
     * Returns the metadata of dashlet remove button.
     *
     * @return {Object}
     */
    public function getRemoveDashletButtonMeta()
    {
        $removeButton = new StdClass();
        $removeButton->type = 'dashletaction';
        $removeButton->action = 'removeClicked';
        $removeButton->label = 'LBL_DASHLET_REMOVE_LABEL';
        $removeButton->name = 'remove_button';
        return $removeButton;
    }

    /**
     * In previous versions activity timeline dashlet on console dashboards was missing the remove.
     * In order to fully support the editing of the console dashboards multi line list view side drawer dashboard's
     * edit mode, we add back the remove buttons to all instances of activity timelines dashlets.
     */
    public function addDashletRemoveButton($id)
    {
        // Read the Dashoard we want to modify
        $dashboardController = new Dashboard();
        $dashboardModel = $dashboardController->retrieve($id);
        $dashboard = json_decode($dashboardModel->metadata);
        $dashboardRows = $dashboard->components[0]->rows;

        // Iterate dashlets and find any activity-timeline dashlets
        $hasChanges = false;
        foreach ($dashboardRows as $row) {
            foreach ($row as $dashlet) {
                $view = $dashlet->view;
                if (isset($view->type) && $view->type === 'activity-timeline') {
                    // Now we have an activity timelines dashlet. The next step is to check if it has
                    // the dashlet remove button and if not, add it.
                    if (isset($view->custom_toolbar) && isset($view->custom_toolbar->buttons)) {
                        foreach ($view->custom_toolbar->buttons as $buttonGroup) {
                            if (isset($buttonGroup->dropdown_buttons)) {
                                $lacksRemoveButton = true;
                                foreach ($buttonGroup->dropdown_buttons as $button) {
                                    if ($button->action === 'removeClicked') {
                                        $lacksRemoveButton = false;
                                        break;
                                    }
                                }
                                if ($lacksRemoveButton) {
                                    $hasChanges = true;
                                    array_push($buttonGroup->dropdown_buttons, $this->getRemoveDashletButtonMeta());
                                }
                            }
                        }
                    }
                }
            }
        }

        // If we have added the remove button to the metadata, save it.
        if ($hasChanges) {
            $dashboardModel->metadata = json_encode($dashboard);
            $dashboardModel->save();
            $this->log('Updated a dashboard so activity-timeline dashlet would have a remove button.');
        }
    }
}
