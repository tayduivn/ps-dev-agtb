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

import BaseView from '../views/base-view';
import ForecastsFilterView from '../views/forecast-filter-view';
import ForecastsHeaderView from '../views/record-header-view';
import ForecastsListView from '../views/forecasts-list-view';
import ForecastsFooterView from '../views/forecasts-footer';

/**
 * Represents Forecast Sales Rep layout
 *
 * @class ForecastsLayout
 * @extends BaseView
 */
export default class ForecastsLayout extends BaseView {

    public type = 'list';
    public FilterView: ForecastsFilterView;
    public ListView: ForecastsListView;
    public defaultView: ForecastsListView;
    public HeaderView: ForecastsHeaderView;
    public Footer: ForecastsFooterView;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.main-pane:not([style*="display: none"])'
        });

        this.FilterView = this.createComponent<ForecastsFilterView>(ForecastsFilterView, { module: options.module });
        this.HeaderView = this.createComponent<ForecastsHeaderView>(ForecastsHeaderView);
        this.defaultView = this.ListView = this.createComponent<ForecastsListView>(ForecastsListView, { module: options.module, default: true });
        this.Footer = this.createComponent<ForecastsFooterView>(ForecastsFooterView);
    }
}
