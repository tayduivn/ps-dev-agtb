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
import DashableRecordDashlet from './dashable-record-dashlet-view';
import CommentLogDashlet from './comment-log-dashlet-view';
import RecordInteractionsDashlet from './record-interactions-dashlet-view';
import PlannedActivitiesDashlet from './planned-activities-dashlet-view';
import ActiveTasksDashlet from './active-tasks-dashlet-view';

/**
 * Represents Service Console view.
 *
 * @class ServiceConsoleView
 * @extends DashboardView
 */
export default class ServiceConsoleView extends DashboardView {

    public DashableRecordDashlet: DashletView;
    public RcPipelineDashlet: DashletView;
    public CommentLogDashlet: DashletView;
    public AccountInfoDashlet: DashletView;
    public CasesInteractionsDashlet: DashletView;
    public PlannedActivitiesDashlet: DashletView;
    public ActiveTasksDashlet: DashletView;

    constructor(options) {
        super(options);

        /*
         *    Service Console > Overview Tab dashlets
         */
        this.PlannedActivitiesDashlet = this.createComponent<PlannedActivitiesDashlet>(PlannedActivitiesDashlet, {
            module: options.module,
            position: '1',
        });

        this.ActiveTasksDashlet = this.createComponent<ActiveTasksDashlet>(ActiveTasksDashlet, {
            module: options.module,
            position: '2',
        });

        /*
         *    Service Console > Cases Tab dashlets
         */
        this.DashableRecordDashlet = this.createComponent<DashableRecordDashlet>(DashableRecordDashlet, {
            module: options.module,
            position: '0',
        });

        this.CommentLogDashlet = this.createComponent<CommentLogDashlet>(CommentLogDashlet, {
            module: options.module,
            position: '1',
        });

        this.AccountInfoDashlet = this.createComponent<DashableRecordDashlet>(DashableRecordDashlet, {
            module: options.module,
            position: '2',
        });

        this.CasesInteractionsDashlet = this.createComponent<RecordInteractionsDashlet>(RecordInteractionsDashlet, {
            module: options.module,
            position: '3',
        });
    }
}
