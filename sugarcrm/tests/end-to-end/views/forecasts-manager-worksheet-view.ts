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
import ForecastsHeaderView from '../views/record-header-view';
import ForecastsListView from '../views/forecasts-list-view';
import ForecastsManagerFooterView from '../views/forecasts-manager-footer-view';
import BaseView from '../views/base-view';
import ForecastsTopInfoBarView from '../views/forecasts-top-info-bar-view';

/**
 * Represents Forecasts Sales Manager Worksheet view
 *
 * @class  SalesManagerWorksheet
 * @extends BaseView
 */
export default class SalesManagerWorksheet extends BaseView {

    public type = 'list';
    public ListView: ForecastsListView;
    public defaultView: ForecastsListView;
    public HeaderView: ForecastsHeaderView;
    public Footer: ForecastsManagerFooterView;
    public TopInfoBar: ForecastsTopInfoBarView;

    constructor(options) {
        super(options);

        this.HeaderView = this.createComponent<ForecastsHeaderView>(ForecastsHeaderView);
        this.defaultView = this.ListView = this.createComponent<ForecastsListView>(ForecastsListView, { module: options.module, default: true });
        this.Footer = this.createComponent<ForecastsManagerFooterView>(ForecastsManagerFooterView);
        this.TopInfoBar = this.createComponent<ForecastsTopInfoBarView>(ForecastsTopInfoBarView);
    }
}
