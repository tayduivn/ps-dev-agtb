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
import {TableDefinition} from 'cucumber';
import RecordLayout from '../layouts/record-layout';
import QliRecord from '../views/qli-record';
import CommentRecord from '../views/comment-record';
import GroupRecord from '../views/group-record';
import QliTable from "../views/qli-table";
import QliTableRecord from "../views/qli-table-record";

/**
 * Select All Records in the QLI table of Quote record view
 *
 * @example I toggle all items in #Quote_3Record.QliTable
 */
When(/^I toggle all items in (#\S+)$/,
    async function (view: QliTable) {
        await view.toggleAllItems();
    }, {waitForApp: true});

/**
 * Select mass update action in the QLI table of Quote record view
 *
 * @example I choose GroupSelected from #Quote_3Record.QliTable
 */
When(/^I choose (DeleteSelected|GroupSelected) from (#\S+)$/,
    async function (actionName, view: QliTable) {
        await view.toggleMassUpdateMenu();
        await view.clickMassUpdateMenuItem(actionName);
    }, {waitForApp: true});

/**
 * Open QLI table + (plus) menu
 *
 * @example "I open QLI actions menu in #Quote_3Record.QliTable and check:"
 */
When(/^I choose (createLineItem|createComment|createGroup) on QLI section on (#\S+) view$/, async function (itemName, layout: RecordLayout) {
    await layout.QliTable.openMenu();
    await this.driver.waitForApp();
    await layout.QliTable.clickMenuItem(itemName);
}, {waitForApp: true});


/**
 * Open QLI table mass update menu and check whether 'Group Selected' and 'Delete Selected' actions are active or not
 *
 * @example "I open QLI actions menu in #Quote_3Record.QliTable and check:"
 */
When(/^I open QLI actions menu in (#\S+) and check:?$/,
    async function(view: QliTable, data: TableDefinition) {

        await view.openQliActionsMenuAndCheck( true, data);

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

    await view.openInlineMenu(itemName);
    await this.driver.waitForApp();
    await view.clickMenuItem(itemName);
}, {waitForApp: true});

When(/^I toggle (#[a-zA-Z](?:\w|\S)*)$/, async function (view:QliTableRecord) {

    await view.toggleRecord();

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
