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
import CsCommentLogDashlet from '../views/cs-comment-log-dashlet-view';
import CsCasesInteractionsDashlet from '../views/cs-cases-interactions-dashlet-view';
import CsCasesInteractionsListView from '../views/cs-cases-interactions-list-view';

/**
 *  Select specified tab in Service Console
 *
 *  @example
 *  When I select Cases tab in #ServiceConsoleView
 */
When(/^I select (Overview|Cases) tab in (#\S+)$/,
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
 *  When I click Edit button in #Dashboard.CsDashableRecordDashlet
 */
When(/^I click (Edit|Save|Cancel) button in (#\S+)$/,
    async function(button: string, view: DashableRecordDashlet) {
        await view.clickButton(button.toLowerCase());
    }, {waitForApp: true});

/**
 *  Switch tab in Dashable Record dashlet
 *
 *  @example
 *  When I switch to Tasks tab in #Dashboard.CsDashableRecordDashlet
 */
When(/^I switch to (Cases|Tasks|Contacts|Documents) tab in (#\S+)$/,
    async function(tabName: string, view: DashableRecordDashlet) {
        await view.selectTab(tabName);
    }, {waitForApp: true});

/**
 *  Add a new comment inside Comment Log dashlet
 *
 *  @example
 *  When I add the following comment into #Dashboard.CsCommentLogDashlet:
 *      | value          |
 *      | My new comment |
 */
When(/^I add the following comment into (#\S+):$/,
    async function(view: CsCommentLogDashlet, data: TableDefinition) {
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
 *  When I Schedule Meeting in #Dashboard.CsCasesInteractionsDashlet
 */
When(/^I (Compose Email|Log Call|Schedule Meeting|Create Note or Attachment) in (#\S+)$/,
    async function(action: string, view: CsCasesInteractionsDashlet) {
        await view.clickButton(action.replace(/ /g, '_').toLowerCase());
    }, {waitForApp: true});

/**
 *  Expand or collapse expanded-content block for specified record in Cases Interactions dashlet
 *
 *  @example
 *  When I expand record *M_1 in #Dashboard.CsCasesInteractionsDashlet.CsCasesInteractionsList
 */
When(/^I (expand|collapse) record (\*[a-zA-Z](?:\w|\S)*) in (#\S+)$/,
        async function(action: string, record: { id: string }, view: CsCasesInteractionsListView) {
            let listItem = view.getListItem({id: record.id});
            await listItem.expandOrCollapseRecord(action);
    }, {waitForApp: true});

/**
 *  Click show more/less in the dashable record dashlet
 *
 *  @example
 *  When I click show less button in #Dashboard.CsAccountInfoDashlet
 */
When(/^I click show (more|less) button in (#\S+)$/,
    async function(action: string, view: DashableRecordDashlet) {
        await view.expandCollapseRecord(action);
    }, {waitForApp: true});
