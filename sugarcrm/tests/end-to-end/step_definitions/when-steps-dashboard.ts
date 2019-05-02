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

import {whenStepsHelper, stepsHelper, Utils, When, seedbed} from '@sugarcrm/seedbed';
import BaseView from '../views/base-view';
import DashletView from '../views/dashlet-view';
import DashboardLayout from '../layouts/dashboard-layout';
import {TableDefinition} from 'cucumber';
import {closeAlert, closeWarning} from '../step_definitions/general_bdd';
import {openMenuAndCheck} from './when-steps-record-header';

/**
 * Click to add new row or add dashlet
 *
 * @example I click NewRow in #AccountsDashboard.RecordView
 */
When(/^I click (NewRow|AddDashlet) in (#\S+)$/,
    async function (btn, view: BaseView): Promise<void> {
        await view.clickButton(btn);
    }, {waitForApp: true});

/**
 * Add dashlet to the dashboard
 *
 * @example "I add KBArticles dashlet to #Dashborad"
 */
When(/^I add ([a-zA-Z](?:\w|\S)*) dashlet to (#\S+)?$/,
    async function (shortDashletName: String, dashboard: DashboardLayout, data: TableDefinition): Promise<void> {
        const dashletNamesMap = {
            ListView: 'List View',
            KBArticles: 'Most Useful Published Knowledge Base Articles',
            MyActivityStream: 'My Activity Stream',
            ProductCatalog: 'Product Catalog',
            RSSFeed: 'RSS Feed',
            SalesPipelineChart: 'Sales Pipeline Chart',
            SavedReportsChart: 'Saved Reports Chart Dashlet',
            Top10Sales: 'Top 10 Sales',
            Twitter: 'Twitter',
            WebPage: 'Web Page',
            ActiveTasks: 'Active Tasks',
            History: 'History',
            InactiveTasks: 'Inactive Tasks',
            NotesAndAttachments: 'Notes & Attachments',
            PlannedActivities: 'Planned Activities',
            ProductCatalogQuickPicks: 'Product Catalog Quick Picks'
        };

        const name = dashletNamesMap[shortDashletName.toString()];
        if (!name) {
            throw new Error(`Dashlet with the name ${shortDashletName} is not found!`);
        }

        // Open Dashboard dropdown and select edit
        await openMenuAndCheck(dashboard, false);
        await dashboard.HeaderView.clickButton('edit');

        // Add New Row
        await dashboard.DashboardView.clickButton('NewRow');
        await this.driver.waitForApp();

        // Click + to add a new dashlet
        await dashboard.DashboardView.clickButton('AddDashlet');
        await this.driver.waitForApp();

        // Select Dashlet
        await seedbed.components.AddSugarDashletDrawer.selectDashletByName(name);

        if (data.hashes.length > 1) {
            throw new Error('One line data table entry is expected');
        }
        let inputData = stepsHelper.getArrayOfHashmaps(data)[0];

        // Inout dashlet name
        await seedbed.components.AddSugarDashletDrawer.setFieldsValue(inputData);
        await this.driver.waitForApp();

        // Save a new dashlet
        await seedbed.components.AddSugarDashletDrawer.HeaderView.clickButton('save');
        await this.driver.waitForApp();

        // Save Dashboard
        await dashboard.HeaderView.clickButton('edit_save');
        await this.driver.waitForApp();
        await closeAlert();

    }, {waitForApp: true});

/**
 * Click dashlet's cog button
 *
 * @example I click Cog in #AccountsDashboard.DashletView
 */
When(/^I click (Cog) in (#\S+)$/,
    async function (btn, view: DashletView): Promise<void> {
        await view.clickButton(btn);
    }, {waitForApp: true});

/**
 * Create or cancel creation of new dashboard
 *
 * @example When I create new dashboard
 */
When(/^I (create|cancel creation of) new dashboard$/,
    async function (action: string, data: TableDefinition): Promise<void> {
        let dashboard = await seedbed.components[`Dashboard`];
        let dashboardHeaderView = await seedbed.components[`Dashboard`].HeaderView;

        await dashboard.HeaderView.clickButton('create');
        await this.driver.waitForApp();

        if (data.hashes.length > 1) {
            throw new Error('One line data table entry is expected');
        }

        let inputData = stepsHelper.getArrayOfHashmaps(data)[0];

        // check for * marked column and cache the record and view if needed
        let uidInfo = Utils.computeRecordUID(inputData);

        seedbed.cucumber.scenario.recordsInfo[uidInfo.uid] = {
            uid: uidInfo.uid,
            originInput: JSON.parse(JSON.stringify(inputData)),
            input: inputData,
            module: dashboardHeaderView.module,
        };

        await dashboardHeaderView.setFieldsValue(inputData);

        switch (action ) {
            case 'create':
                await dashboard.HeaderView.clickButton('save');
                await this.driver.waitForApp();
                await closeAlert();
                break;
            case 'cancel creation of':
                await dashboard.HeaderView.clickButton('cancel');
                await this.driver.waitForApp();
                break;
            default:
                throw new Error('Invalid selection detected...');
        }

    }, {waitForApp: true});

/**
 * Delete dashboard
 *
 * @example When I delete dashboard
 */
When(/^I delete dashboard$/,
    async function (): Promise<void> {
        let dashboard = await seedbed.components[`Dashboard`];

        await openMenuAndCheck(dashboard, false);
        await dashboard.HeaderView.clickButton('edit');
        await dashboard.HeaderView.clickButton('delete');
        await this.driver.waitForApp();

        await closeWarning('confirm');
        await closeAlert();
    }, {waitForApp: true});
