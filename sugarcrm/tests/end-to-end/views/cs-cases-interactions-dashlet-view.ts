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

/**
 * Represents Cases Interactions dashlet
 *
 * @class CsCasesInteractionsDashletView
 * @extends DashletView
 */
export default class CsCasesInteractionsDashlet extends DashletView {

    public activity = {
        name:  '',
        status: '',
    };

    public CsCasesInteractionsList: CsCasesInteractionsListView;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: `.dashlet-container[name=dashlet_${options.position}]`,
            header: {
                $: '.dashlet-header',
                plusButton: '.fa.fa-plus',
                menuItems: {
                    compose_emil: '[data-dashletaction="composeEmail"]',
                    log_call: '.fa.fa-phone',
                    schedule_meeting: '.fa.fa-calendar',
                    create_note_or_attachment: '[data-dashletaction="createRecord"] .fa.fa-plus',
                },
            },
            activities: {
                $: '.activity-timeline',
                row: {
                    $: '.timeline-entry:nth-child({{index}})',
                    name: '.content-cell.primary',
                    status: '.content-cell.secondary .row-cell:not(.pull-right)',
                    expand: '.fa.fa-chevron-down',
                    collapse: '.fa.fa-chevron-up'
                }
            }
        });

        this.CsCasesInteractionsList = this.createComponent<CsCasesInteractionsListView>(CsCasesInteractionsListView);
    }

    /**
     *  Expand dashlet actions dropdown
     */
    public async expandPlusDropdown() {
        let selector = this.$('header.plusButton');
        await this.driver.click(selector);
    }

    /**
     * Select action from dashlet's actions (aka '+') dropdown
     *
     * @param {string} buttonName action to select
     */
    public async clickButton(buttonName: string) {
        await this.expandPlusDropdown();
        let selector = this.$(`header.menuItems.${buttonName}`);
        await this.driver.click(selector);
    }

    /**
     * Get subject and status of the activity by specified index
     *
     * @param {number} index of the activity
     * @return {object} activity
     */
    public async getActivityInfo(index: number) {
        let selector = this.$('activities.row.name', {index});
        this.activity.name = await this.driver.getText(selector);
        selector = this.$('activities.row.status', {index});
        this.activity.status = await this.driver.getText(selector);
        return this.activity;
    }
}
