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

import DashboardView from './dashboard-view';
import DashletView from './dashlet-view';
import Top10RLIsDashlet from './top-10-rlis-dashlet';
import PipelineDashlet from './pipeline-dashlet-view';
import DashableRecordDashlet from './dashable-record-dashlet-view';
import CommentLogDashlet from './comment-log-dashlet-view';
import RecordsInteractionsDashlet from './record-interactions-dashlet-view';
import ActiveSubscriptionsDashlet from './active-subscriptions-dashlet-view';

/**
 * Represents Renewals Console view.
 *
 * @class RenewalsConsoleView
 * @extends DashboardView
 */
export default class RenewalsConsole extends DashboardView {

    public Top10RLIsDashlet: DashletView;
    public PipelineDashlet: DashletView;
    public DashableRecordDashlet: DashletView;
    public CommentLogDashlet: DashletView;
    public AccountsInteractionsDashlet: DashletView;
    public ActiveSubscriptionsDashlet: DashletView;
    public AccountInfoDashlet: DashletView;
    public OpportunityInteractionsDashlet: DashletView;

    constructor(options) {
        super(options);

        // Overview Tab dashlets
        this.Top10RLIsDashlet = this.createComponent<Top10RLIsDashlet>(Top10RLIsDashlet, {
            module: options.module,
            position: '110',
        });

        this.PipelineDashlet = this.createComponent<PipelineDashlet>(PipelineDashlet, {
            module: options.module,
            position: '100',
        });

        // Accounts Tab dashlets
        this.DashableRecordDashlet = this.createComponent<DashableRecordDashlet>(DashableRecordDashlet, {
            module: options.module,
            position: '000',
        });

        this.CommentLogDashlet = this.createComponent<CommentLogDashlet>(CommentLogDashlet, {
            module: options.module,
            position: '001',
        });

        this.ActiveSubscriptionsDashlet = this.createComponent<ActiveSubscriptionsDashlet>(ActiveSubscriptionsDashlet, {
            module: options.module,
            position: '010',
        });

        this.AccountsInteractionsDashlet = this.createComponent<RecordsInteractionsDashlet>(RecordsInteractionsDashlet, {
            module: options.module,
            position: '011',
        });

        // Opportunities Tab dashlets
        this.AccountInfoDashlet = this.createComponent<DashableRecordDashlet>(DashableRecordDashlet, {
            module: options.module,
            position: '010',
        });

        this.OpportunityInteractionsDashlet = this.createComponent<RecordsInteractionsDashlet>(RecordsInteractionsDashlet, {
            module: options.module,
            position: '011',
        });
    }
}
