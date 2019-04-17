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

import PipelineItemView from './pipeline-item-view';
import BaseListView from './baselist-view';

/**
 * Represents Pipeline View
 *
 * @class PipelineView
 * @extends BaseListView
 */
export default class PipelineView extends BaseListView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '',
            pipelineByTime: '.stageButton[data-pipeline="date_closed"]',
            pipelineByStatus: '.stageButton[data-pipeline="sales_status"]',
            content: {
                $: '.main-content',
            },
            columnHeader: 'thead th:nth-child({{index}})',

        });
    }

    /**
     * Select tab by name in Opportunities Pipeline View
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
     * Create Pipeline Item View component
     *
     * @param conditions
     * @returns {any}
     */
    public createListItem (conditions) {

        if (!(conditions || conditions.id)) {
            return null;
        }

        let pipelineViewItem = this.createComponent<PipelineItemView>(PipelineItemView, {
            id: conditions.id,
            module: this.module,
        });

        this.listItems.push(pipelineViewItem as any);
        return pipelineViewItem as any;
    }

    /**
     * Get column name in the Pipeline View
     *
     * @param index
     * @returns {Promise<any>}
     */
    public async getColumnHeader(index) {

        let selector = this.$('columnHeader', {index} );
        return this.driver.getText(selector);
    }
}

