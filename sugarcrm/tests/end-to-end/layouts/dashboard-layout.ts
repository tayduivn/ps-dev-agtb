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
import HeaderView from '../views/dashboard-header-view';
import DashboardView from '../views/dashboard-view';
import DashletView from '../views/dashlet-view';
import ProductCatalogQuickPicksDashlet from '../views/product-catalog-quick-picks-dashlet-view';
import RecordsMarkedForErasureDashlet from '../views/records-marked-for-erasure-dashlet';
import ForecastsBarChartDashlet from '../views/forecasts-bar-chart-dashlet';
import ListViewDashlet from '../views/list-view-dashlet-view';
import InactiveTasksDashlet from '../views/inactive-tasks-dashlet-view';
import HistoryDashlet from '../views/history-dashlet-view';
import ActiveSubscriptionsDashlet from '../views/active-subscriptions-dashlet-view'

/**
 * Represents a Sugar Dashboard layout.
 *
 * @class DashboardLayout
 * @extends BaseView
 */
export default class DashboardLayout extends BaseView {

    public HeaderView: HeaderView;
    public defaultView: DashboardView;
    public DashboardView: DashboardView;
    public ForecastsBarChartDashlet: DashletView;
    public ProductCatalogQuickPicksDashlet: DashletView;
    public RecordsMarkedForErasureDashlet: DashletView;
    public ListViewDashlet: DashletView;
    public InactiveTasksDashlet: DashletView;
    public HistoryDashlet: DashletView;
    public ActiveSubscriptionsDashlet: DashletView;

    protected type: string;

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: '',
        });

        this.type = 'dashboard';

        this.defaultView = this.DashboardView = this.createComponent<DashboardView>(DashboardView, {
            module: options.module,
            default: true
        });

        this.ForecastsBarChartDashlet = this.createComponent<ForecastsBarChartDashlet>(ForecastsBarChartDashlet, {
            module: options.module,
        });

        this.HeaderView = this.createComponent<HeaderView>(HeaderView, {
            module: options.module,
        });

        this.ProductCatalogQuickPicksDashlet = this.createComponent<ProductCatalogQuickPicksDashlet>(ProductCatalogQuickPicksDashlet, {
            module: options.module,
        });

        this.RecordsMarkedForErasureDashlet = this.createComponent<RecordsMarkedForErasureDashlet>(RecordsMarkedForErasureDashlet, {
            module: options.module,
        });

        this.ListViewDashlet = this.createComponent<ListViewDashlet>(ListViewDashlet, {
            module: options.module,
        });

        this.InactiveTasksDashlet = this.createComponent<InactiveTasksDashlet>(InactiveTasksDashlet, {
            module: options.module,
            position: '0',
        });

        this.HistoryDashlet = this.createComponent<HistoryDashlet>(HistoryDashlet, {
            module: options.module,
            position: '0',
        });

        this.ActiveSubscriptionsDashlet = this.createComponent<ActiveSubscriptionsDashlet>(ActiveSubscriptionsDashlet, {
            module: options.module,
            position: '0',
        });
    }
}
