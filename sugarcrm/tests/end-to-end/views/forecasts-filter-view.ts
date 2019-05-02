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

/*
 Represents Forecasts Filter view PageObject on ForecastListView Layout.
 */

import BaseView from './base-view';
import ForecastSearchFilterDropdownCmp from '../components/forecast-search-filter-dropdown-cmp';

/**
 * @class ForecastFilterView
 * @extends BaseView
 */
export default class ForecastFilterView extends BaseView {

    public forecastSearchFilterDropdownCmp: ForecastSearchFilterDropdownCmp;

    constructor(options) {
        super(options);

        this.forecastSearchFilterDropdownCmp = this.createComponent<ForecastSearchFilterDropdownCmp>(ForecastSearchFilterDropdownCmp);
    }
}
