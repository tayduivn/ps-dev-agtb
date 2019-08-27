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
import DashableRecordDashlet from '../views/dashable-record-dashlet-view';
import CsCommentLogDashlet from '../views/cs-comment-log-dashlet-view';
import ListViewDashlet from '../views/list-view-dashlet-view';
import CsCasesInteractionsDashlet from '../views/cs-cases-interactions-dashlet-view';
import PlannedActivitiesDashlet from '../views/planned-activities-dashlet-view';

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
    public CsDashableRecordDashlet: DashletView;
    public CsCommentLogDashlet: DashletView;
    public CsAccountInfoDashlet: DashletView;
    public ListViewDashlet: DashletView;
    public CsCasesInteractionsDashlet: DashletView;
    public CsPlannedActivitiesDashlet: DashletView;

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

        this.CsDashableRecordDashlet = this.createComponent<DashableRecordDashlet>(DashableRecordDashlet, {
            module: options.module,
            position: '000',
            hasListView: true,
        });

        this.CsCommentLogDashlet = this.createComponent<CsCommentLogDashlet>(CsCommentLogDashlet, {
            module: options.module,
            position: '001',
        });

        this.CsAccountInfoDashlet = this.createComponent<DashableRecordDashlet>(DashableRecordDashlet, {
            module: options.module,
            position: '010',
        });

        this.CsCasesInteractionsDashlet = this.createComponent<CsCasesInteractionsDashlet>(CsCasesInteractionsDashlet, {
            module: options.module,
            position: '011',
        });

        this.CsPlannedActivitiesDashlet = this.createComponent<PlannedActivitiesDashlet>(PlannedActivitiesDashlet, {
            module: options.module,
            position: '001',
        });

    }
}
