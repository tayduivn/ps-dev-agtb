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
'use strict';

import {Hashmap, When, seedbed, Then} from '@sugarcrm/seedbed';
import {TableDefinition} from 'cucumber';
import {chooseModule, closeAlert, closeWarning, getCurrentModule, parseInputArray} from './general_bdd';
import * as _ from 'lodash';
import FilterView from '../views/filter-view';

/**
 * Add or Cancel custom filter creation
 *
 * @example
 * When I add custom filter 'New Filter 1' on the Quotes list view with the following values:
 *  | fieldName                  | filter_operator | filter_value   |
 *  | quote_stage                | is any of       | On Hold, Draft |
 *  | date_quote_expected_closed | is equal to     | 10/20/2020     |
 */
When(/^I (add|cancel|add but do not save) custom filter '([^"]*)' on the (\w+) (list|tile) view with the following values:$/,
    async function (action: string, filterName: string, module: string, view: string, table: TableDefinition) {

        await chooseModule(module);
        let filterView = await seedbed.components[`${module}List`].FilterView;

        if (view === 'tile') {
            // navigate to tile view
            await filterView.toggleListViewMode('tileview');
        }

        // Click Create button
        await filterView.clickCreateButton();

        // Populate filter fields with data from the table
        await populateFilter(filterView, module, table);

        // Type in Filter name
        await filterView.typeFilterName(filterName);
        await this.driver.waitForApp();

        switch (action) {
            case 'add':
                // Save custom filter
                await filterView.performAction('filter-save');
                await this.driver.waitForApp();
                await closeAlert();
                break;
            case 'cancel':
                // Cancel custom filter creation
                await filterView.performAction('filter-close');
                break;
            case 'add but do not save' :
                // Do NOT save custom filter
                break;
            default:
                throw new Error('Invalid action name');
        }
    }, {waitForApp: true}
);

/**
 *  Save or cancel previously created but not saved custom filter
 *
 *  Note: This step does not provide any navigation to the filter.
 *  Page with the filter should already be opened before this step is invoked.
 *
 *  The custom filter should have a name typed in so it can be saved successfully
 *
 *  @example
 *  When I save custom filter on the Opportunities tile view
 */
When(/^I (save|cancel) custom filter on the (\w+) (list|tile) view$/,
    async function (action: string, module: string, view: string) {

        let filterView = await seedbed.components[`${module}List`].FilterView;

        switch (action) {
            case 'save':
                // Save custom filter
                await filterView.performAction('filter-save');
                await this.driver.waitForApp();
                await closeAlert();
                break;
            case 'cancel':
                // Cancel custom filter creation
                await filterView.performAction('filter-close');
                break;
            default:
                throw new Error('Invalid action name');
        }
    }, {waitForApp: true}
);


/**
 * Populate custom filter with values
 *
 * @param {FilterView} filterView
 * @param {string} module
 * @param {TableDefinition} table
 * @returns {Promise<void>}
 */
const populateFilter = async function (filterView: FilterView, module: string, table: TableDefinition) {
    const rows = table.rows();

    // List of filter operators where no new filter value is needed
    const filterOperators = [
        'is empty',
        'is not empty',
        'yesterday',
        'today',
        'tomorrow',
        'last 7 days',
        'next 7 days',
        'last 30 days',
        'next 30 days',
        'last month',
        'this month',
        'next month',
        'last year',
        'this year',
        'next year'
    ];

    // Populate filter with data from the table
    for (let i = 0; i < rows.length; i++) {

        // Check if new filter row is needed
        let isRowExist = await filterView.isFilterRowExist(i + 1);
        if (isRowExist === false) {
            // Add new filter row after default row is filled
            if (i !== 0) {
                await  filterView.addRow(i);
            }
        }

        let row = rows[i];
        let fieldName = row[0].trim();
        let filterOperator = row[1].toString();
        let filterValue = row[2].trim();
        const min = '_min';
        const max = '_max';

        // Get Field Label
        let argumentsArray = [];
        argumentsArray.push(fieldName, module);
        let fieldLabel = await seedbed.client.driver.execSync('getLabelByFieldName', argumentsArray);

        // Select field in field selector
        await filterView.setFieldValue(i + 1, 'field', fieldLabel.value);

        // Set Filter Operator
        await filterView.setFieldValue(i + 1, 'operator', filterOperator);

        // If filter value is needed
        if (filterOperators.indexOf(filterOperator) === -1) {
            // Set new filter value for the selected field
            let inputData = new Hashmap();

            // If more than one value specified for the filter
            let filterValues = filterValue.split(',');

            if (filterOperator !== 'is between') {
                for (let j = 0; j < filterValues.length; j++) {
                    inputData.push(fieldName, filterValues[j].trim());
                    await filterView.setFieldsValue(inputData);
                    await seedbed.client.driver.waitForApp();
                }
            } else if (filterValues.length === 2) {
                inputData.push(fieldName.concat(min), filterValues[0].trim());
                inputData.push(fieldName.concat(max), filterValues[1].trim());
                await filterView.setFieldsValue(inputData);
            }
        }
    }
};

/**
 * Hide, apply, or delete existing custom filter in the list view
 *
 *  @example
 *  When I hide custom filter 'New Filter 1' on the Quotes list view
 */
When(/^I (hide|apply|delete) custom filter '([^"]*)' on the (\w+) (list|tile) view$/,
    async function (action: string, filterName: string, module: string, view: string) {

        await chooseModule(module);

        // Get FilterView
        const filterView = await seedbed.components[`${module}List`].FilterView;

        if (view === 'tile') {
            // navigate to tile view
            await filterView.toggleListViewMode('TileView'.toLowerCase());
        }

        switch (action) {
            case 'hide':
                // Hide custom filter
                await filterView.hideCustomFilter();
                break;
            case 'apply':
                // Apply custom filter after it was hidden
                await filterView.selectCustomFilter(filterName);
                break;
            case 'delete':
                // Edit custom filter
                await filterView.editCustomFilter();
                await this.driver.waitForApp();

                // Delete custom filter
                await filterView.performAction('filter-delete');
                await this.driver.waitForApp();
                await closeWarning('confirm');
                await closeAlert();
                break;
            default:
                throw new Error('Invalid action name');
        }
    }, {waitForApp: true}
);

/**
 * Edit or Reset existing custom filter
 *
 *  @example
 *  When I edit custom filter 'New Filter 1' on the Accounts list view with the following values:
 *      | fieldName    | filter_operator | filter_value |
 *      | account_type | is any of       | Competitor   |
 *      | industry     | is any of       | Education    |
 */
When(/^I (edit|reset) custom filter '([^"]*)' on the (\w+) list view with the following values:$/,
    async function (action: string, filterName: string, module: string, table: TableDefinition) {

        await chooseModule(module);
        // Get FilterView
        const filterView = await seedbed.components[`${module}List`].FilterView;

        // Edit custom filter
        await filterView.editCustomFilter();
        await this.driver.waitForApp();

        // Reset Filter before editing
        if (action === 'reset') {
            await filterView.performAction('filter-reset');
            await this.driver.waitForApp();
        }

        // Populate filter fields with data from the table
        await populateFilter(filterView, module, table);

        // Save custom filter
        await filterView.performAction('filter-save');
        await this.driver.waitForApp();
        await closeAlert();
    }, {waitForApp: true}
);

/**
 *  Verify if record(s) present/not present on the list view
 *
 *  @example
 *  Then I should see [*Q_3, *Q_4] on Quotes list view
 *  And I should not see [*Q_1, *Q_2] on Quotes list view
 *
 */
Then(/^I should (not )?see (\[(?:\*\w+)(?:,\s*(?:\*\w+))*\]) on (\w+) list view$/,
    async function(not, inputIDs: string, module: string) {

        let currentModule = await getCurrentModule();
        if (currentModule !== module ) {
            await chooseModule(module);
        }
        // Get ListView
        const listView = await seedbed.components[`${module}List`].ListView;

        // Parse input array
        let recordIds = await parseInputArray(inputIDs);

        // Verify whether record exists on the list view or not
        for (let record of recordIds) {
            let listItem = listView.getListItem({id: record.id});

            let value = await listItem.isVisibleView();

            if (_.isEmpty(not) !== value) {
                throw new Error('Expected ' + (not || '') + ' to see list item (' + listItem.$() + ')');
            }
        }
    }, {waitForApp: true}
);
