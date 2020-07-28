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

import ListLayout from './list-layout';

/**
 * Represents Search And Select layout.
 *
 * @class SearchAndSelectLayout
 * @extends ListLayout
 */
export default class SearchAndSelectLayout extends ListLayout {

    protected reportID: string;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.drawer.active',
            recordRadio: 'input[name={{module}}_select]',
            recordName: 'tbody td[data-column=name] .list .ellipsis_inline[title="{{name}}"]',
        });
    }

    /**
     * Select record from the list of available records by record's name
     *
     * Note: This method will always select first record in the list of displayed records.
     * In order to select the record you need, use filter-by-name control
     * to make sure that only one record is displayed in the list before selection is made.
     *
     * @param {string} name
     * @param {string} module
     * @returns {Promise<void>}
     */
    public async selectRecordByName(name: string, module: string) {

        let nameSelector = this.$('recordName', {name: name});
        let isFound = await this.driver.isVisible(nameSelector);

        // Select first record from the list of records
        if (isFound) {
            let selector = this.$('recordRadio', {module: module});
            await this.driver.click(selector);
        } else {
            throw new Error (`Record named ${name} is not found in ${module} Search And Select darwer`);
        }
    }
}
