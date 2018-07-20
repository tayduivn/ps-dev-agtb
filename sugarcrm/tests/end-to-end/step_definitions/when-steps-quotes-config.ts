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

import {whenStepsHelper, stepsHelper, Utils, When, seedbed} from '@sugarcrm/seedbed';
import QuotesConfigDrawerLayout from '../layouts/quotes-config-drawer-layout';
import {TableDefinition} from 'cucumber';
import Accordion from '../views/quotes-config-accordion';
import IntelligencePane from '../views/quotes-config-rhspane';

/**
 *  Search for the specified string in the intelligence pane
 *
 *  @example When I search for "Next Step" in #QuotesConfigDrawer.IntelligencePane
 */
When(/^I search for "([^"]*)" in (#\S+)$/,
    async function(value, view: IntelligencePane) {
        await view.setSearchField(value);
    }, {waitForApp: true});

/**
 * Select field in Intelligence Pane of quote config based on the field name
 *
 * @example When I select fields in #QuotesConfigDrawer.IntelligencePane
 *   | fieldName |
 *   | next_step |
 */
When(/^I select fields in (#\S+)$/,
    async function (view: IntelligencePane , data: TableDefinition): Promise<void> {

        if (data.hashes.length > 1) {
            throw new Error('One line data table entry is expected');
        }

        const fields = data.rows();
        for (let i = 0; i < fields.length; i++) {
            await view.checkFieldByName(data.rows()[i]);
        }
    }, {waitForApp: true});

/**
 * This step applicable to Quotes Config drawer which has 3 different sections: Summary Bar, Worksheet Columns, Grand Totals Footer
 *
 * @example When I expand Grand_Totals_Footer on #QuotesConfigDrawer.Accordion
 */
When(/^I expand (Summary_Bar|Worksheet_Columns|Grand_Totals_Footer) on (#\S+)$/, async function (panelName: string, view: Accordion) {

    await view.toggleAccordion(panelName.toLowerCase());
}, {waitForApp: true});

/**
 * Restore Default in quote config > accordion
 *
 * @example When I Restore Defaults on #QuotesConfigDrawer.Accordion
 */
When(/^I Restore Defaults on (#\S+)$/, async function (view: Accordion) {

    await view.restoreDefaults();
}, {waitForApp: true});
