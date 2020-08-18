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
 * Represents Records Marked For Erasure Dashlet in Data Privacy record view
 *
 * @class RecordsMarkedForErasureDashlet
 * @extends DashletView
 */
export default class RecordsMarkedForErasureDashlet extends DashletView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.dashlet-container[name="dashlet_0"]',
            header: {
                $: '.header[data-module="{{module}}"] td',
            },
            record: {
                $: '.record[data-record-id="{{id}}"]',
                recordFieldMarked: '.record-field-marked'
            },
        });
    }

    /**
     * Get the number of fields to erase based on record ID in Records Marked For Erasure dashlet
     *
     * @param recordId
     * @returns {Promise<number>}
     */
    public async getAmountOfFieldsToErase(recordId) {
        let selector = this.$(`record.recordFieldMarked`, {id: recordId});

        let value = await this.driver.getText(selector);

        return Number.parseInt(value.replace('(', '').replace(')', ''), 10);
    }

    /**
     * Get the number of records to erase based on Module name Records Marked For Erasure dashlet
     *
     * @param moduleName
     * @returns {Promise<number>}
     */
    public async getAmountOfRecordsToErase(moduleName) {
        let selector = this.$(`header`, {module: moduleName});

        let value = await this.driver.getText(selector);

        let numericSubStr = value.substring(
            value.lastIndexOf('(') + 1,
            value.lastIndexOf(')')
        );

        return Number.parseInt(numericSubStr, 10);
    }
}
