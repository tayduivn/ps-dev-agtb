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

import TileViewItem from './tile-view-item';
import BaseListView from './baselist-view';

/**
 * Represents Tile View
 *
 * @class TileView
 * @extends BaseListView
 */
export default class TileView extends BaseListView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            opportunitiesByTime: '.stageButton[data-pipeline="date_closed"]',
            opportunitiesByStage: '.stageButton[data-pipeline="sales_stage"]',
            content: {
                $: '.main-content',
            },
            columnHeader: 'thead th:nth-child({{columnIndex}}) [data-original-title="{{columnName}}"]',
        });
    }

    /**
     * Select tab by name in Opportunities Tile View
     *
     * @param tabName
     * @returns {Promise<void>}
     */
    public async selectTab(tabName) {

        let isSelected = await this.driver.isElementExist(this.$(tabName) + '.selected');

        // Click on the specified tab if it is not currently selected
        if (!isSelected) {
            await this.driver.click(this.$(tabName));
        }
    }

    /**
     * Create TileViewItem component
     *
     * @param conditions
     * @returns {any}
     */
    public createListItem (conditions) {

        if (!(conditions || conditions.id)) {
            return null;
        }

        let tileViewItem = this.createComponent<TileViewItem>(TileViewItem, {
            id: conditions.id,
            module: this.module,
        });

        this.listItems.push(tileViewItem as any);
        return tileViewItem as any;
    }

    /**
     * Check whether the column with specified name exists in Tile View
     *
     * @columnName {string} columnName
     * @columnIndex {string} index of the column in Tile View starting from 1
     * @returns {Promise<any>}
     */
    public async getColumnHeader(columnName: string, columnIndex: string):Promise<boolean> {

        let selector = this.$('columnHeader', {columnName, columnIndex} );
        return await this.driver.isElementExist(selector);
    }
}
