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
import AddSugarDashletDrawerLayout from '../layouts/add-sugar-dashlet-drawer-layout';
import DashletView from '../views/dashlet-view';
import DashboardView from "../views/dashboard-view";
import DashboardLayout from "../layouts/dashboard-layout";
import {TableDefinition} from 'cucumber';

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
            PlannedActivities:'Planned Activities',
        };

        const name = dashletNamesMap[shortDashletName.toString()];
        if (!name) {
            throw new Error(`Dashlet with the name ${shortDashletName} is not found!`);
        }

        await dashboard.DashboardView.clickButton('NewRow');
        await this.driver.waitForApp();

        await dashboard.DashboardView.clickButton('AddDashlet');
        await this.driver.waitForApp();

        await seedbed.components.AddSugarDashletDrawer.selectDashletByName(name);

        if (data.hashes.length > 1) {
            throw new Error('One line data table entry is expected');
        }
        let inputData = stepsHelper.getArrayOfHashmaps(data)[0];

        await seedbed.components.AddSugarDashletDrawer.setFieldsValue(inputData);
        await this.driver.waitForApp();

        await seedbed.components.AddSugarDashletDrawer.HeaderView.clickButton('save');

    }, {waitForApp: true});

/**
 * Click dashlet's cog button
 *
 * @example I click Cog in #AccountsDashboard.DashletView
 */
When(/^I click (Cog) in (#\S+)$/,
    async function (btn, view: DashletView ): Promise<void> {
        await view.clickButton(btn);
    }, {waitForApp: true});
