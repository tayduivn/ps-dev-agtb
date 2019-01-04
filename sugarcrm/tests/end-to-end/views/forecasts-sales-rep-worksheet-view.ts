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
import ForecastsFilterView from './forecasts-filter-view';
import ForecastsTopInfoBarView from './forecasts-top-info-bar-view';
import ForecastsHeaderView from '../views/record-header-view';
import ForecastsListView from '../views/forecasts-list-view';
import ForecastsFooterView from '../views/forecasts-footer';
import BaseView from '../views/base-view';

/**
 * Represents Forecasts Sales Representative Worksheet view
 *
 * @class  SalesRepWorksheet
 * @extends BaseView
 */
export default class SalesRepWorksheet extends BaseView {

    public type = 'list';
    public Filter: ForecastsFilterView;
    public ListView: ForecastsListView;
    public defaultView: ForecastsListView;
    public HeaderView: ForecastsHeaderView;
    public Footer: ForecastsFooterView;
    public TopInfoBar: ForecastsTopInfoBarView;

    constructor(options) {
        super(options);

        this.Filter = this.createComponent<ForecastsFilterView>(ForecastsFilterView, { module: options.module });
        this.HeaderView = this.createComponent<ForecastsHeaderView>(ForecastsHeaderView);
        this.defaultView = this.ListView = this.createComponent<ForecastsListView>(ForecastsListView, { module: options.module, default: true });
        this.Footer = this.createComponent<ForecastsFooterView>(ForecastsFooterView);
        this.TopInfoBar = this.createComponent<ForecastsTopInfoBarView>(ForecastsTopInfoBarView);
    }
}
