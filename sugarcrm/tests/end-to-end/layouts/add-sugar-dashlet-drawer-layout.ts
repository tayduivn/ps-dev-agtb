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
import DrawerLayout from './drawer-layout';

/**
 * Represents a Add Sugar Dashlet drawer layout.
 *
 * @class AddSugarDashletDrawerLayout
 * @extends DrawerLayout
 */
export default class AddSugarDashletDrawerLayout extends DrawerLayout {

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            row: 'tr td span[field-value="{{dashletName}}"] a'
        });

        this.type = 'drawer';
    }

    /**
     * Select dashlet from the list of available dashlets using dashlet's name
     * @param {String} dashletName: Name of the dashlet
     * @returns {Promise<void>}
     */
    public async selectDashletByName(dashletName: String) {
        await this.driver.waitForApp();
        await this.driver.click(this.$('row', {dashletName}));
    }
}
