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

import ModuleMenuCmp from '../components/module-menu-cmp';
import {whenStepsHelper, stepsHelper, Utils, When, seedbed} from '@sugarcrm/seedbed';
import {TableDefinition} from 'cucumber';
import RecordView from '../views/record-view';
import RecordLayout from '../layouts/record-layout';
import ListView from '../views/list-view';
import QliRecord from '../views/qli-record';
import CommentRecord from '../views/comment-record';
import GroupRecord from '../views/group-record';
import RliTableRecord from '../views/rli-table';
import BaseView from '../views/base-view';

/**
 * Select module in modules menu
 *
 * If "itemName" is visible, it means that it can be located in main menu.
 * If not - trying to open modules dropdown menu and find this module there
 *
 * @example "I choose Accounts in modules menu"
 */
When(/^I choose (\w+) in modules menu$/,
    async function (itemName) {

        await this.driver.waitForApp();

        // TODO: it's a temporary solution, need to remove this 'pause' after SBD-349 is fixed
        await this.driver.pause(2000);

        let moduleMenuCmp = new ModuleMenuCmp({});

        let isVisible = await moduleMenuCmp.isVisible(itemName);

        if (isVisible) {
            await moduleMenuCmp.clickItem(itemName);

        } else {

            await moduleMenuCmp.showAllModules();
            await moduleMenuCmp.clickItem(itemName, true);
        }

        // TODO: it's a temporary solution, need to remove this 'pause' after SBD-349 is fixed
        await this.driver.pause(1000);

    }, {waitForApp: true});

/**
 * Select item from cached View
 */
When(/^I select (\*[a-zA-Z](?:\w|\S)*) in (#\S+)$/,
    async function (record: {id: string}, view: ListView) {
        let listItem = view.getListItem({id: record.id});
        await listItem.clickListItem();
    }, {waitForApp: true});

/**
 * Open the preview for the record
 *
 * @example I click on preview button on *Account_A in #AccountsList.ListView
 */
When(/^I click on preview button on (\*[a-zA-Z](?:\w|\S)*) in (#\S+)$/,
    async function (record: { id: string }, view: ListView) {
        let listItem = view.getListItem({id: record.id});
        await listItem.clickPreviewButton();
    }, {waitForApp: true});


When(/^I wait for (\d+) seconds$/,
    async function(delay: string): Promise<void> {
        await whenStepsHelper.waitStep(parseInt(delay, 10));
    });

When(/^I open ([\w,\/]+) view and login$/,
    async function(module: string): Promise<void> {
        await whenStepsHelper.setUrlHashAndLogin(module);
    }, {waitForApp: true});

    When(/^I go to "([^"]*)" url$/,
            async function(urlHash): Promise<void> {
            await this.driver.setUrlHash(urlHash);
            // TODO: it's a temporary solution, need to remove this 'pause' after SBD-349 is fixed
            await this.driver.pause(1500);

        }, {waitForApp: true});

// The step requires the view to be opened, it reformats the provided data to format valid for dynamic edit layoutd
When(/^I provide input for (#\S+) view$/,
    async function (view: RecordView, data: TableDefinition): Promise<void> {

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
            module: view.module,
        };

        await view.setFieldsValue(inputData);

    }, {waitForApp: true});

// The step requires the view to be opened, it reformats the provided data to format valid for dynamic edit layoutd
When(/^I provide input for (#\S+) view for (\d+) row$/,
    async function (view: any, index: number, data: TableDefinition): Promise<void> {

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
            module: view.module,
        };

        let rowView = view.getRowByIndex(index);

        await rowView.setFieldsValue(inputData);

    }, {waitForApp: true});

When(/^I click show more button on (#\S+) view$/, async function(layout: RecordLayout) {
    await layout.showMore();
}, {waitForApp: true});

When(/^I click show less button on (#\S+) view$/, async function(layout: RecordLayout) {
    await layout.showLess();
}, {waitForApp: true});

When(/^I toggle (Business_Card|Billing_and_Shipping|Quote_Settings|Show_More) panel on (#\S+) view$/, async function (panelName: string, view: RecordView) {

    await view.togglePanel(panelName);

}, {waitForApp: true});

/**
 * Click on a list view action button
 *
 * @example I click on edit button for *Account_A in #AccountsList.ListView
 */
When(/^I click on (\w+) button for (\*[a-zA-Z](?:\w|\S)*) in (#\S+)$/,
    async function(button, record: {id}, view: ListView) {
        let listItem = view.getListItem({id: record.id});

        let isVisible = await listItem.isVisible(button);

        if (isVisible) {
            await listItem.clickListButton(button);

        } else {

            await listItem.openDropdown();
            await listItem.clickListButton(button);
        }
    }, {waitForApp: true});

/**
 * Set field values from data
 *
 * @example I set values for *Account_A in #AccountsList.ListView
 */
When(/^I set values for (\*[a-zA-Z](?:\w|\S)*) in (#\S+)$/,
    async function(record: {id: string}, view: ListView, data: TableDefinition) {
        let listItem = view.getListItem({id: record.id});

        let row: any;

        for (row of data.hashes()) {
            let field = await listItem.getField(row.fieldName);
            await field.setValue(row.value);
        }

    }, {waitForApp: true});

When(/^I click (\S+) field on (#\S+) view$/,
    async function(fieldName, layout: any) {
        let view = layout.type ? layout.defaultView : layout;
        return view.clickField(fieldName);
    }, {waitForApp: true});

When(/^I click (\S+) field on (\*[a-zA-Z](?:\w|\S)*) record in (#\S+) view$/,
    async function(fieldName: string, record: { id: string }, listView: ListView) {

        let listItem = listView.getListItem({id: record.id});

        await listItem.clickField(fieldName);

    }, {waitForApp: true});

When(/^I choose (createLineItem|createComment|createGroup) on QLI section on (#\S+) view$/, async function (itemName, layout: RecordLayout) {
    await layout.QliTable.openMenu();
    await this.driver.waitForApp();
    await layout.QliTable.clickMenuItem(itemName);
}, {waitForApp: true});

When(/^I create new group on QLI section on (#\S+) view$/, async function (layout: RecordLayout, data: any) {

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
        module: 'ProductBundles',
    };

    await layout.QliTable.openMenu();
    await this.driver.waitForApp();
    await layout.QliTable.clickMenuItem('createGroup');

    await this.driver.waitForApp();

    await layout.QliTable.GroupRecord.setFieldsValue(inputData);

}, {waitForApp: true});

When(/^I choose (editLineItem|deleteLineItem|editGroup|deleteGroup) on (#[a-zA-Z](?:\w|\S)*)$/, async function (itemName, view:QliRecord) {

    await view.openLineItemMenu();
    await this.driver.waitForApp();
    await view.clickMenuItem(itemName);
}, {waitForApp: true});


When(/^I click on (save|cancel) button on QLI (#\S+) record$/, async function (buttonName, record: QliRecord) {
    await record.pressButton(buttonName);
}, {waitForApp: true});

When(/^I click on (save|cancel) button on Comment (#\S+) record$/, async function (buttonName, record: CommentRecord) {
    await record.pressButton(buttonName);
}, {waitForApp: true});

When(/^I click on (save|cancel) button on Group (#\S+) record$/, async function (buttonName, record: GroupRecord) {
    await record.pressButton(buttonName);
}, {waitForApp: true});

When(/^I choose (addRLI|removeRLI) on (#[a-zA-Z](?:\w|\S)*) view for (\d+) row$/, async function (buttonName, view: RliTableRecord, index)  {

    let rowView = view.getRowByIndex(index);

    await rowView.pressButton(buttonName);

},{waitForApp: true});


When(/^I dismiss alert$/, async function () {
    await this.driver.alertDismiss();
}, {waitForApp: true});
