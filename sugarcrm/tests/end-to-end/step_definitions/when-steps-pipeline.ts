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

import {When} from '@sugarcrm/seedbed';
import PipelineView from '../views/pipeline-view';
import {closeAlert, closeWarning} from './general_bdd';
import TileViewSettings from '../views/tile-settings-view';
import {TableDefinition} from 'cucumber';

/**
 *  Select tab in Opportunities pipeline view
 *
 *  @example
 *  When I select pipelineByStage tab in #OpportunitiesPipelineView view
 */
When(/^I select (pipelineByTime|pipelineByStage) tab in (#\S+) view$/,
    async function (tabName: string, view: PipelineView): Promise<void> {

        await view.selectTab(tabName);

    }, {waitForApp: true});


/**
 *  Delete record in pipeline view
 *
 *  @example
 *  When I delete *Opp_1 in #OpportunitiesPipelineView
 */
When(/^I delete (\*[a-zA-Z](?:\w|\S)*) in (#\S+) view$/,
    async function (record: { id: string }, view: any) {
        let listItem = view.getListItem({id: record.id});
        await listItem.clickDeleteButton('delete');

        // Close Confirmation alert
        await closeWarning('Confirm');

        await closeAlert();
    }, {waitForApp: true});


/**
 *  Disable or enable Tile View for particular module in Admin Panel > Tile View Settings
 *
 *  @example
 *  When I hide "Opportunities" module in #TileViewSettings view
 *
 */
When(/^I hide "(Cases|Opportunities|Tasks)" module in (#\S+) view$/,
    async function (moduleName: string, view: TileViewSettings) {

        const urlHash = 'VisualPipeline/config';
        const saveButton = 'save';

        // Navigate to Tile View Config
        await this.driver.setUrlHash(urlHash);
        await this.driver.waitForApp();

        // Hide Tile View for specified module
        await view.hideModule(moduleName);
        await this.driver.waitForApp();

        // Click Save Button
        await view.HeaderView.clickButton(saveButton);
        await this.driver.pause(4000);
        await this.driver.waitForApp();

        // Close Alert
        await closeAlert();

}, {waitForApp: true});

/**
 *  Enable Tile View for particular module in Admin Panel > Tile View Settings
 *
 *  @example
 *  When I show "Opportunities" module in #TileViewSettings view with the following settings:
 *      | Table_Header | Tile_Options_Header | Tile_Options_Body                       | Records_Per_Column |
 *      | Sales Stage  | Name                | Account Name,Expected Close Date,Likely | 15                 |
 *
 */
When(/^I show "(Cases|Opportunities|Tasks)" module in (#\S+) view with the following settings:$/,
    async function (moduleName: string, view: TileViewSettings, data: TableDefinition) {

        if (data.hashes.length > 1) {
            throw new Error('One line data table entry is expected');
        }
        const urlHash = 'VisualPipeline/config';
        const saveButton = 'save';

        // Navigate to Tile View Config
        await this.driver.setUrlHash(urlHash);
        await this.driver.waitForApp();

        // Show Tile View for specified module and populate with values
        await view.showModule(moduleName);
        await this.driver.waitForApp();

        // Switch to the enabled module's tab
        await view.switchTab(moduleName);
        await this.driver.waitForApp();

        const rows = data.rows();
        const row = rows[0];

        // Select Tile View Table Header
        await view.selectValueFromDropdown(moduleName, 1, 1, row[0]);

        // Select Tile Header field
        await view.selectValueFromDropdown(moduleName, 3, 1, row[1]);

        // Select Tile Body field
        let tileBodyFields = rows[0][2].split(',');
        for (let j in tileBodyFields) {
            await view.selectValueFromDropdown(moduleName, 3, 2, tileBodyFields[j].trim());
        }

        // Select number of records per column
        await view.selectValueFromDropdown(moduleName, 4, 1, row[3]);

        // Click Save Button
        await view.HeaderView.clickButton(saveButton);
        await this.driver.pause(4000);
        await this.driver.waitForApp();

        // Close Alert
        await closeAlert();

    }, {waitForApp: true});

/**
 * Activate or disable columns by drag-n-drop items between white and black lists in Tile View settings
 *
 *      @example
 *      When I drag-n-drop column header items on "Cases" module in #TileViewSettings view:
 *          | sourceItem | destination |
 *          | New        | black_list  |
 *          | Closed     | black_list  |
 *          | Rejected   | black_list  |
 */
When(/^I drag-n-drop column header items on "(Cases|Opportunities|Tasks)" module in (#\S+) view:$/,
    async function (moduleName: string, view: TileViewSettings, data: TableDefinition) {

            const urlHash = 'VisualPipeline/config';
            const saveButton = 'save';

            // Navigate to Tile View Config
            await this.driver.setUrlHash(urlHash);
            await this.driver.waitForApp();

            // Switch to the enabled module's tab
            await view.switchTab(moduleName);
            await this.driver.waitForApp();

            // Perform darg-n-drop
            const rows = data.rows();
            for (let i in rows) {
                    let [source, to] = rows[i];
                    if ( to === 'white_list' || to === 'black_list' ) {
                            await  view.moveItem(moduleName, source, to);
                    } else {
                            throw new Error('Not a valid destination for drag-n-drop action !');
                    }
            }

            // Click Save button
            await view.HeaderView.clickButton(saveButton);
            await this.driver.pause(4000);
            await this.driver.waitForApp();

            // Close Alert
            await closeAlert();

    }, {waitForApp: true});

/**
 *  Drag and drop tile from column to column in Tile View
 *  Warning!!! This step definition does not work
 *
 *  @example
 *   When I drag *Opp_1 tile to "Prospecting" column in #OpportunitiesPipelineView view
 *
 */
When(/^I drag (\*[a-zA-Z](?:\w|\S)*) tile to "(\w+\s?\w+)" column in (#\S+) view$/,
    async function (record: { id: string }, columnName: string, view: any) {
            let listItem = view.getListItem({id: record.id});
            await listItem.dragAndDropTile(columnName);
            await closeAlert();

    }, {waitForApp: true});

