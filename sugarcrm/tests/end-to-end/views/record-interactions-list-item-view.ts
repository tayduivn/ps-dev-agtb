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
 * @class RecordInteractionsListItemView
 * @extends ListItemView
 */
export default class RecordInteractionsListItemView extends BaseListItemView {

    public id: string;
    public index: number;
    public current: boolean;

    public basicActivityInfo = {
        name: '',
        status: '',
    };

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: '.timeline-entry[data-id*="{{id}}"]',
            row: {
                name: '.content-cell.primary',
                status: '.content-cell.secondary .row-cell:not(.pull-right)',
                expand: '.fa.fa-chevron-down',
                collapse: '.fa.fa-chevron-up'
            },
            expandedInfo: {
                $: '.expanded-contents',
                field: '.record-cell:nth-child({{i}}) .field-value',
            }

        });

        this.id = options.id;
        this.index = options.index;
        this.current = !this.id && !this.index;
    }

    /**
     * Expand or Collapse record info in Cases Interaction dashlet
     *
     * @param {string} action expand or collapse record info
     */
    public async expandOrCollapseRecord(action: string) {
        let selector = this.$(`row.${action}`);
        await this.driver.click(selector);
        await this.driver.waitForApp();
    }

    /**
     * Get name and status of item in the list view while item is not expanded
     *
     * @param {number} index
     * @return this.basicActivityInfo
     */
    public async getItemInfo(index: number) {
        let selector = this.$('row.name', {index});
        this.basicActivityInfo.name = await this.driver.getText(selector);
        selector = this.$('row.status', {index});
        this.basicActivityInfo.status = await this.driver.getText(selector);
        return this.basicActivityInfo;
    }

    /**
     * Get field value in the expanded-content block of the record based on the index of the field
     *
     * @param {number} i
     * @return {string} value of the field in the expanded record block
     */
    public async getRecordInfo(i: number) {
        let selector = this.$('expandedInfo.field', {i});
        return await this.driver.getText(selector);
    }
}
