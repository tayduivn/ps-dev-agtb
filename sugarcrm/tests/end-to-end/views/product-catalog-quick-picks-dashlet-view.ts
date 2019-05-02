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

import DashletView from './dashlet-view';

/**
 * Represents Preview view.
 *
 * @class ProductCatalogQuickPicksDashlet
 * @extends DashletView
 */
export default class ProductCatalogQuickPicksDashlet extends DashletView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.product-catalog-quick-picks',
            activeTab: '.dashlet-tab.active a',
            inactiveTab: '.dashlet-tab:not(.active) a',
            record: '.tab-content li a[data-record-id="{{RecordID}}"] .fa.fa-list-alt',
            tableRow: '.tab-content .recent-records-list a',
            pagination: {
                $: '.pagination',

                rightNavArrow: '.nav-next',
                leftNavArrow: '.nav-previous',

                leftChevron: '.previous-fav',
                pageButton: '.paginate-num-button[data-page-id="{{page_num}}"]',
                rightChevron: '.next-fav',

                rightEllipsis: '.right-ellipsis-icon',
                leftEllipsis: '.left-ellipsis-icon',
            }
        });
    }

    public async toggleTabs() {
        await this.driver.scroll(this.$(`inactiveTab`));
        await this.driver.click(this.$(`inactiveTab`));
    }

    public async clickRecordByID(id) {
        let selector = this.$('record', {RecordID: id});
        await this.driver.scroll(selector);
        await this.driver.click(selector);
    }

    public async getNumberOfRecords() {
        let rows = await this.driver.elements(this.$('tableRow'));
        return rows.value.length;
    }

    public async isRecordExists(id) {
        let selector = this.$('record', {RecordID: id});
        return await this.driver.isElementExist(selector);
    }

    public async clickChevron(page) {
        let selector = this.$(`pagination.` + page);
        await this.driver.click(selector);
    }

    public async clickPageByPageNum(page_num) {
        let selector = this.$(`pagination.pageButton`, {page_num});
        await this.driver.click(selector);
    }

    public async isControlActive(page) {
        let selector = this.$(`pagination.` + page ) + '.disabled';
        let value = await this.driver.isElementExist(selector);
        return value;
    }
}
