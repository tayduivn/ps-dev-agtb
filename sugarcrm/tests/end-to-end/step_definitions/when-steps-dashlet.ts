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

import {When} from '@sugarcrm/seedbed/cucumber/step-definition';
import DashletView from '../views/dashlet-view';
import PlannedActivitiesListView from '../views/planned-activities-list-view';
import PlannedActivitiesDashlet from '../views/planned-activities-dashlet-view';
import ActiveTasksDashlet from '../views/active-tasks-dashlet-view';

/**
 * Click dashlet's cog button
 *
 * @example I click Cog in #AccountsDashboard.DashletView
 */
When(/^I click (Cog) in (#\S+)$/,
    async function (btn: string, view: DashletView): Promise<void> {
        await view.clickButton(btn);
    }, {waitForApp: true});


/**
 *  Navigate to specific tab in Planned Activities dashlet
 *
 *      @example
 *      When I navigate to Calls tab in #Dashboard.CsPlannedActivitiesDashlet
 */
When(/^I navigate to (Calls|Meetings|Due Now|Upcoming|To Do) tab in (#\S+)$/,
    async function(tabName: string, view: DashletView) {

        // check tab name as well as dashlet type
        if ((tabName === 'Meetings' && view instanceof PlannedActivitiesDashlet) ||
            (tabName === 'Due Now' && view instanceof ActiveTasksDashlet)) {
            await view.navigateToTab('0');
        } else if ((tabName === 'Calls' && view instanceof PlannedActivitiesDashlet) ||
            (tabName === 'Upcoming' && view instanceof ActiveTasksDashlet)) {
            await view.navigateToTab('1');
        } else if (tabName === 'To Do' && view instanceof ActiveTasksDashlet) {
            await view.navigateToTab('2');
        } else {
            throw new Error('Invalid module specified!');
        }
    }, {waitForApp: true});

/**
 *  When I set time filter (today vs future) in the dashlet
 *
 *      @example
 *      When I set filter as Today in #Dashboard.CsPlannedActivitiesDashlet
 */
When(/^I set filter as (Today|Future) in (#\S+)$/,
    async function(filterName: string, view: DashletView) {
        await view.setFilter(filterName.toLowerCase());
    }, {waitForApp: true});

/**
 *  When I set visibility (user vs group) in the dashlet
 *
 *      @example
 *      When I set visibility as 'group' in #Dashboard.CsPlannedActivitiesDashlet
 */
When(/^I set visibility as '(user|group)' in (#\S+)$/,
    async function(visibility: string, view: DashletView) {
        await view.setVisibility(visibility);
    }, {waitForApp: true});

/**
 *  Mark meeting or Call record as held, accepted, tentative or declined
 *
 *      @example
 *      When I mark record *M_1 as Tentative in #Dashboard.CsPlannedActivitiesDashlet.ActivitiesList
 */
When(/^I mark record (\*[a-zA-Z](?:\w|\S)*) as (Held|Accepted|Tentative|Declined|Completed) in (#\S+)$/,
    async function(record: { id: string }, action: string, view: PlannedActivitiesListView) {
        let listItem = view.getListItem({id: record.id});
        await listItem.selectAction(action.toLowerCase());
    }, {waitForApp: true});
