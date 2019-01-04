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

import BaseView from './base-view';

/**
 * @class ForecastsFooter
 * @extends BaseView
 */
export default class ForecastsFooter extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $:  'tfoot',
            'Displayed Total':{
                $: '#forecastsWorksheetTotalsFilteredTotals',
                likely: '.tright.likely_case',
                best: '.tright.best_case',
                worst: '.tright.worst_case',
            },
            'Overall Total':{
                $: '#forecastsWorksheetTotalsOverallTotals',
                likely: '.tright.likely_case',
                best: '.tright.best_case',
                worst: '.tright.worst_case',
            },
        });
    }

    public async getFooterValue(totalType) {

        const likelyLocator = this.$(`${totalType}.likely`);
        const likely = await this.driver.getText(likelyLocator);

        const bestLocator = this.$(`${totalType}.best`);
        const best = await this.driver.getText(bestLocator);

        return {likely, best};
    }
}
