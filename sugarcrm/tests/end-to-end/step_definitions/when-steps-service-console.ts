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

import {seedbed, stepsHelper, TableDefinition, When} from '@sugarcrm/seedbed';
import ServiceConsoleView from '../views/service-console-view';
import DashableRecordDashlet from '../views/dashable-record-dashlet-view';
import MultilineListView from '../views/multiline-list-view';
import CommentLogDashlet from '../views/comment-log-dashlet-view';
import RecordsInteractionsListView from '../views/record-interactions-list-view';
import DashableRecordDashletConfig from '../views/dashable-record-dashlet-config-view';
import DashletView from '../views/dashlet-view';

/**
 *  Select specified tab in Service Console
 *
 *  @example
 *  When I select Cases tab in #ServiceConsoleView
 */
When(/^I select (Overview|Cases|Accounts|Opportunities) tab in (#\S+)$/,
    async function(choice: string, view: ServiceConsoleView) {
        await view.switchTab(choice);
    }, {waitForApp: true});

/**
 *  Close side drawer in Service Console > Cases tab
 *
 *  @example
 *  When I close side drawer in #ServiceConsoleView
 */
When(/^I close side drawer in (#\S+)$/,
    async function(view: ServiceConsoleView) {
        await view.closeSideDrawer();
    }, {waitForApp: true});

/**
 *  Select action from record actions dropdown in Service Console Cases tab
 *
 *  @example
 *  When I choose "Edit in New Tab" action for *C_1 in #CasesList.MultilineListView
 */
When(/^I choose "(Edit in New Tab|Copy Record URL|Open in New Tab)" action for (\*[a-zA-Z](?:\w|\S)*) in (#\S+)$/,
    async function(action: string, record: { id: string }, view: MultilineListView) {
        let listItem = view.getListItem({id: record.id});
        await listItem.chooseAction(action);
        await this.driver.pause(3000);
    }, {waitForApp: true});


/**
 *  Click button in the header of the Dashable Record dashlet
 *
 *  @example
 *  When I click Save button in #RenewalsConsoleView.DashableRecordDashlet
 */
When(/^I click (Edit|Save|Cancel) button in (#\S+)$/,
    async function(button: string, view: DashableRecordDashlet) {
        await view.clickButton(button.toLowerCase());
    }, {waitForApp: true});

/**
 *  Switch tab in Dashable Record dashlet
 *
 *  @example
 *  When I switch to Tasks tab in #RenewalsConsoleView.DashableRecordDashlet
 */
When(/^I switch to (\S+) tab in (#\S+)$/,
    async function(tabName: string, view: DashableRecordDashlet) {
        if (!await view.selectTab(tabName)) {
            throw new Error(`Error! Specified tab '${tabName}' is not found.`);
        }
    }, {waitForApp: true});

/**
 *  Add a new comment inside Comment Log dashlet
 *
 *  @example
 *  When I add the following comment into #RenewalsConsoleView.CommentLogDashlet:
 *      | value          |
 *      | My new comment |
 */
When(/^I add the following comment into (#\S+):$/,
    async function(view: CommentLogDashlet, data: TableDefinition) {
        if (data.hashes.length > 1) {
            throw new Error('One line data table entry is expected');
        }

        let comment = stepsHelper.getArrayOfHashmaps(data)[0];
        await view.addNewComment(comment.pop());
}, {waitForApp: true});

/**
 *  Select action from actions (aka +) dropdown in Cases Interactions dashlet
 *
 *  @example
 *  When I Schedule Meeting in #ServiceConsoleView.RecordInteractionsDashlet
 */
When(/^I (Compose Email|Log Call|Schedule Meeting|Create Note or Attachment|Create Task|Create Archived Email) in (#\S+)$/,
    async function(action: string, view: DashletView) {
        await view.clickButton(action.replace(/ /g, '_').toLowerCase());
    }, {waitForApp: true});

/**
 *  Expand or collapse expanded-content block for specified record in Cases Interactions dashlet
 *
 *  @example
 *  When I expand record *M_1 in #ServiceConsoleView.RecordsInteractionsDashlet.RecordInteractionsList
 */
When(/^I (expand|collapse) record (\*[a-zA-Z](?:\w|\S)*) in (#\S+)$/,
        async function(action: string, record: { id: string }, view: RecordsInteractionsListView) {
            let listItem = view.getListItem({id: record.id});
            await listItem.expandOrCollapseRecord(action);
    }, {waitForApp: true});

/**
 *  Click show more/less in the dashable record dashlet
 *
 *  @example
 *  When I click show less button in #ServiceConsoleView.AccountInfoDashlet
 */
When(/^I click show (more|less) button in (#\S+)$/,
    async function(action: string, view: DashableRecordDashlet) {
        await view.expandCollapseRecord(action);
    }, {waitForApp: true});


/**
 *   Move to specified tab inside configuration section of Dashable Record dashlet
 *
 *   @example
 *   When I move to Tasks tab in #DashableRecordConfig view
 */
When(/^I move to (\S+) tab in (#\S+) view$/,
    async function(tabName: string, view: DashableRecordDashletConfig) {
        await view.navigateToTab(tabName);
    }, {waitForApp: true});

/**
 *   Add or remove modules as tabs in configuration section of dashable
 *   record dashlet
 *
 *   @example
 *       When I add following modules in #DashableRecordConfig view
 *          | tab_list |
 *          | Calls    |
 *          | Notes    |
 *          | Account  |
 */
When(/^I (add|remove) the following modules as tabs in (#\S+) view:$/,
    async function(action: string, view: DashableRecordDashletConfig, data: TableDefinition) {

        let rows = data.rows();
        for (let i = 0; i < rows.length; i++) {
            let tabName = rows[i][0];
            if (action === 'add') {
                await view.addTab(tabName);
            } else if (action === 'remove') {
                await view.closePill(tabName);
            } else {
                throw new Error(`Error: The following action ${action} is not supported!`);
            }
        }
    }, {waitForApp: true});

/**
 *  Navigate to and update value of fields in one of the tabs of Dashable Record configuration screen
 *
 *  @example
 *  When I update dashlet settings in Notes tab of #DashableRecordConfig view
 *      | limit | auto_refresh     |
 *      | 10    | Every 10 Minutes |
 */
When(/^I update dashlet settings in (\S+) tab of (#\S+) view$/,
    async function(tabName: string, view: DashableRecordDashletConfig, data: TableDefinition) {

        // Navigate to specified Tab
        await view.navigateToTab(tabName);

        if (data.hashes.length > 1) {
            throw new Error('One line data table entry is expected');
        }

        // Set values
        let inputData = stepsHelper.getArrayOfHashmaps(data)[0];
        let rec_view = await seedbed.components[`${tabName}Record`];
        await rec_view.setFieldsValue(inputData);
    }, {waitForApp: true});

/**
 *  Remove the field from list of fields displayed in the list View dashlet
 *
 *      @example
 *      When I remove the following fields from Tasks tab of #DashableRecordConfig view:
 *          | fields |
 *          | Status |
 */
When(/^I remove the following fields from (\S+) tab of (#\S+) view:$/,
    async function(tabName: string, view: DashableRecordDashletConfig, data: TableDefinition) {

        // Navigate to specified Tab
        await view.navigateToTab(tabName);

        // Remove field
        let rows = data.rows();
        for (let i = 0; i < rows.length; i++) {
            let fieldName = rows[i][0];
            if (!await view.closePill(fieldName)) {
                throw new Error(`Error! Specified field '${fieldName}' is not found.`);
            }
        }
    }, {waitForApp: true});
