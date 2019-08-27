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

import DashletView from './dashlet-view';
import CsCasesInteractionsListView from './cs-cases-interactions-list-view';
import PlannedActivitiesListView from './planned-activities-list-view';

/**
 * Represents Planned Activities dashlet
 *
 * @class PlannedActivitiesDashlet
 * @extends DashletView
 */
export default class PlannedActivitiesDashlet extends DashletView {


    public ActivitiesList: PlannedActivitiesListView;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: `.dashlet-container[name=dashlet_${options.position}]`,
            header: {
                $: '.dashlet-header',
                plusButton: '.fa.fa-plus',
                menuItems: {
                    log_call: 'li a[name=log_call]',
                    schedule_meeting: 'li a[name=schedule_meeting]',
                },
            },
            tabs: {
                $: '.dashlet-tabs',
                tab: 'a[data-index="{{index}}"]',
                activeTab: '.dashlet-tab.active a[data-index="{{index}}"]',
                record_count: 'a[data-index="{{index}}"] .count',
            },

            count: '.dashlet-tabs a[data-index="{{index}}"] .count',
            filter: '.btn-group.dashlet-group .btn[value={{filterName}}]',
            visibility: '[value="{{visibilityName}}"]',
        });

        this.ActivitiesList = this.createComponent<PlannedActivitiesListView>(PlannedActivitiesListView);
    }
}
