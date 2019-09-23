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
            pipelineByStage: '.stageButton[data-pipeline="sales_stage"]',
            content: {
                $: '.main-content',
            },
            columnHeader: 'thead [data-original-title="{{columnName}}"]',
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
     * Check whether the column with specified name exists in Tile View
     *
     * @param {string} columnName
     * @returns {Promise<any>}
     */
    public async getColumnHeader(columnName) {

        let selector = this.$('columnHeader', {columnName} );
        return this.driver.isElementExist(selector);
    }
}
