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

import {stepsHelper, Utils, When, seedbed} from '@sugarcrm/seedbed';
import {TableDefinition} from 'cucumber';
import KBViewCategoriesDrawer from '../layouts/kb-view-categories-layout';
import KBSettingsLayout from '../layouts/kb-settings-layout';
import RecordLayout from '../layouts/record-layout';
import RecordView from '../views/record-view';
import {KbCategoriesListView} from '../views/kb-categories-list-view';
import {closeAlert} from './general_bdd';


/**
 *  Create new category
 *
 *  @example
 *  When I create new category for #KBViewCategoriesDrawer view
 *      | *    | name |
 *      | KB_1 | KB_1 |
 */
When(/^I create new category for (#\S+) view$/,
    async function (view: KbCategoriesListView, data: TableDefinition): Promise<void> {

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
            module: 'Categories',
        };

        await view.createNewCategory(inputData.hash.name);

    }, {waitForApp: true});


/**
 *  Click Templates button in KB create drawer
 *
 *  @example
 *  When I click Templates button on #KBContentsDrawer.RecordView
 */
When(/^I click Templates button on (#\S+)$/, async function (view: RecordView) {
    await view.clickTemplatesButton();
    await this.driver.waitForApp();
}, {waitForApp: false});


/**
 *  Select existing template for the new article in KB create drawer
 *
 *  @example
 *  When I use existing template *KBT_2 for the new article on #KBContentsDrawer view
 */
When(/^I use existing template (\*[a-zA-Z](?:\w|\S)*) for the new article on (#\S+) view$/,
    async function (record: { id: string }, recordLayout: RecordLayout): Promise<void> {

        const controlName = 'radio';
        const module = recordLayout.module;

        // Click Templates button
        let view = seedbed.components[`${module}Drawer`].RecordView;
        await view.clickTemplatesButton();
        await this.driver.waitForApp();

        // Select radiobutton in Search And Select Drawer
        let listItem = seedbed.components[`${module}SearchAndSelect`].ListView.getListItem({id: record.id});
        await listItem.clickItem(controlName);

    }, {waitForApp: true});

/**
 *  Add new language in KB settings
 *
 *  @example
 *  When I add a new language on #KBSettingsDrawer
 *      | language_code | language_label | primary |
 *      | PO            | Polish         | false   |
 *
 */
When(/^I add a new language on (#\S+)$/, async function (layout: KBSettingsLayout, table: TableDefinition) {

    if (table.hashes.length !== 1) {
        throw new Error('One line data table entry is expected');
    }

    const rows = table.rows();
    for (let i = 0; i < 10; i++) {

        // Find index of 'Add Item' button
        let isAddItemButtonVisible = await layout.isButtonVisible(i);

        if (isAddItemButtonVisible) {
            let row = rows[0];
            let languageCode = row[0];
            let languageValue = row[1];
            let primary = row[2].toLowerCase();

            // Add new language in KB settings
            await layout.addSupportedLanguage(languageCode, languageValue, primary, i);
            break;
        }
    }

    // Click Save Buton
    await layout.HeaderView.clickButton('save');
    await this.driver.waitForApp();

    // Close Alert
    await closeAlert();

}, {waitForApp: true});

/**
 *  Edit existing KB category
 *
 *  @example
 *   When I edit *KBCategory_1 on #KBViewCategoriesDrawer.KBCategoriesList view
 *       | name          |
 *       | KBCategory_1a |
 *
 */
When(/^I edit (\*[a-zA-Z](?:\w|\S)*) on (#\S+) view$/,
    async function ( record: { id: string }, view: KbCategoriesListView, data: TableDefinition): Promise<void> {

        if (data.hashes.length > 1) {
            throw new Error('One line data table entry is expected');
        }

        let inputData = stepsHelper.getArrayOfHashmaps(data)[0];
        await view.editCategory(record.id, inputData.hash.name);

    }, {waitForApp: true});

/**
 *  Move or delete existing KB category
 *
 *  @example
 *  When I moveUp *KBCategory_1 on #KBViewCategoriesDrawer.KBCategoriesList view
 *
 */
When(/^I (moveUp|moveDown|delete) (\*[a-zA-Z](?:\w|\S)*) on (#\S+) view$/,
    async function (action: string, record: { id: string }, view: KbCategoriesListView): Promise<void> {

        await view.moveCategory(record.id, action.toLowerCase());

    }, {waitForApp: true});
