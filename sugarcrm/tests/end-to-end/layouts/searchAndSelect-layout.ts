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
            reportRadio: 'td[data-column="Reports_select"]',
            reportName: 'tbody td[data-column="name"] .list .ellipsis_inline[title="{{name}}"]',
        });
        this.type = 'drawer';
    }

    /**
     * Select report from the list of available reports by report's name
     *
     * Note: This method will always select first report in the list of displayed reports.
     * In order to select the report you need, use filter-by-name control
     * to make sure that only one report is displayed in the list before selection is made.
     *
     * @param {string} name
     * @returns {Promise<void>}
     */
    public async selectReportByName(name: string) {

        let nameSelector = this.$('reportName', {name: name});
        let isFound = await this.driver.isVisible(nameSelector);

        // Select report from the list of reports
        if (isFound) {
            let selector = this.$('reportRadio');
            await this.driver.click(selector);
        } else {
            throw new Error (`Report named ${name} is not found!`);
        }
    }
}
