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

import BaseView from './base-view';

/**
 * @class PipelineItemView
 * @extends BaseView
 */
export default class PipelineItemView extends BaseView {

    public id: string;
    public index: number;
    public current: boolean;

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: 'li[data-modelid*="{{id}}"]',
            listItem: {
                listItemName: '.pipeline-tile',
                buttons: {
                    delete: '.rowaction.btn.delete',
                },
                tileName: '.name',
                tileContent: '.tile-body span:nth-child({{tileContentRow}})'
            },
        });

        this.id = options.id;
        this.index = options.index;
        this.current = !this.id && !this.index;
    }

    /**
     * Click on list view item list element (name in most cases)
     *
     * @returns {*}
     */
    public async clickListItem() {

        let selector = this.$('listItem.tileName', {id: this.id});
        let rowSelector = this.$();

        return this.driver
            .execSync('scrollToSelector', [rowSelector])
            .click(selector);
    }

    /**
     * Check state of tile delete button
     *
     * @returns {Promise<any>}
     */
    public async isDeleteButtonDisabled() {

        let selector = this.$('listItem.buttons.delete', {id: this.id}) + '.disabled';
        let rowSelector = this.$('listItem.tileName');

        await this.driver.moveToObject(rowSelector);
        await this.driver.waitForApp();
        let isDisabled = await this.driver.isElementExist(selector);
        return isDisabled;
    }

    /**
     * Click on delete record button (top-right corner of each tile) in pipeline view
     *
     * @param itemName
     * @returns {Promise<void>}
     */
    public async clickDeleteButton(itemName) {

        let selector = this.$('listItem.buttons.' + itemName.toLowerCase(), {id: this.id});
        let rowSelector = this.$('listItem.tileName');

        await this.driver.moveToObject(rowSelector);
        await this.driver.waitForApp();
        await this.driver.click(selector);
        await this.driver.waitForApp();
    }

    /**
     * Checks if button is visible
     *
     * @param itemName
     * @returns {Promise<Client<boolean>>}
     */
    public async isVisible(itemName) {
        return this.driver.isVisible(this.$('listItem.buttons.' + itemName.toLowerCase()));
    }

    /**
     * Get field value in the tile content
     *
     * @param tileContentRow
     * @returns {Promise<any>}
     */
    public async getTileFieldValue(tileContentRow) {

        let selector = this.$('listItem.tileContent', {id: this.id, tileContentRow});
        await this.driver.scroll(selector);
        let val = await this.driver.getText(selector);
        return val;
    }

    /**
     * Get record name from tile title using record id.
     *
     * @returns {Promise<any>}
     */
    public async getTileName() {

        let selector = this.$('listItem.tileName', {id: this.id} );
        await this.driver.scroll(selector);
        return this.driver.getText(selector);
    }

    /**
     * Check column of specified opportunity record
     *
     * @param columnName
     * @returns {Promise<any>}
     */
    public async checkTileViewColumn (columnName) {
        // construct css part containing specifed column name
        let columnPart = `.column[data-column-name="${columnName}"]`;
        // Prepend record css with part containg column name
        let selector = `${columnPart} ${this.$()}`;
        // Check if css containing column name exists
        let value  = await this.driver.isElementExist(selector );
        return value;
    }
}
