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

/*
 Represents Mass Update view on ListView Layout.
 */

import BaseView from './base-view';

/**
 * @class MassUpdateView
 * @extends BaseView
 */
export default class MassupdateView extends BaseView {

    protected fieldToUpdateItem: string;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.extend.list',
            filterHeader: {
                $: '.filter-header',
                'cancel': '.cancel_button',
                'update': '.btn.btn-primary',
            },
            filterBody: {
                $: '.filter-body:nth-child({{rowNum}})',
                fieldToUpdate: '.select2-container.select2',
                buttons: {
                    $: '.filter-actions.btn-group',
                    addRow: '.fa.fa-plus',
                    removeRow: '.fa.fa-minus',
                }
            }
        });

        this.fieldToUpdateItem = '.select2-result-label=';
    }

    /**
     * Set 'Parent' field value
     *
     * @param {string} pValue parent Field Value to Set
     * @param {number} rowNum row number
     * @return {Promise<void>}
     */
    public async setParentFieldValue(pValue, rowNum) {
        // Set 'Parent' field value
        await this.driver.click(this.$(`filterBody.fieldToUpdate`, {rowNum}));
        await this.driver.waitForApp();
        await this.driver.click(`${this.fieldToUpdateItem}${pValue}`);
        await this.driver.waitForApp();
    }

    /**
     * Perform Update or Cancel action in mass update
     *
     * @param {string} btnName
     * @returns {Promise<void>}
     */
    public async performAction(btnName) {
        await this.driver.click(this.$(`filterHeader.` + btnName));
    }

    /**
     * Add new row to Mass Update
     *
     * @param {number} rowNum
     * @returns {Promise<void>}
     */
    public async addRow(rowNum) {
        await this.driver.click(this.$(`filterBody.buttons.addRow`, {rowNum}));
        await this.driver.waitForApp();
    }

    /**
     * Delete specified row in Mass Update
     *
     * @param {number} rowNum
     * @returns {Promise<void>}
     */
    public async deleteRow(rowNum) {
        await this.driver.click(this.$(`filterBody.buttons.removeRow`, {rowNum}));
        await this.driver.waitForApp();
    }
}
