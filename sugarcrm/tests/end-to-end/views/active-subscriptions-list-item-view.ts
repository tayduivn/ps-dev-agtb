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
import BaseListItemView from './list-item-view';

/**
 * @class ActiveSubscriptionsListItemView
 * @extends BaseListItemView
 */
export default class ActiveSubscriptionsListItemView extends BaseListItemView {

    public id: string;
    public index: number;
    public current: boolean;

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: '.subscription-entry[data-id="{{id}}"]',
            item: {
                    $: '.content-row:nth-child({{rowIndex}})',
                    name: '.row-cell.secondary',
                    quantity: 'div.row-cell:not(.pull-right)',
                    date: '.pull-right .date',
                    total: '.currency-field',
                },
        });

        this.id = options.id;
        this.index = options.index;
        this.current = !this.id && !this.index;
    }

    /**
     * Get Active Subscription item info from the specified field
     *
     * @param {string} fieldName name of the field
     * @return {string} field value
     */
    public async getRecordInfo(fieldName: string) {
        let rowIndex = "1";
        rowIndex = await this.driver.isElementExist(this.$(`item.${fieldName}`,{rowIndex})) ? "1" : "2";
        let selector = this.$(`item.${fieldName}`, {rowIndex});

        await this.driver.getText(selector);
        return await this.driver.getText(selector);
    }

    /**
     * Click on the record in the active subscriptions dashlet
     *
     */
    public async selectRecord() {
        let rowIndex = "1";
        let selector = this.$('item.name', {id: this.id, rowIndex});
        await this.driver.click(selector);
    }
}
