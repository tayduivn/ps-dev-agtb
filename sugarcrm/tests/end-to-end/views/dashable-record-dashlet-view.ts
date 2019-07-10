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
import ListViewDashletListView from './list-view-dashlet-list-view';

/**
 * Represents Dashable Record dashlet
 *
 * @class DashableRecordDashlet
 * @extends DashletView
 */
export default class DashableRecordDashlet extends DashletView {

    protected ListView: ListViewDashletListView;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: `.dashlet-container[name=dashlet_${options.position}]`,
            header: {
                $: '.dashlet-header',
                cancel: 'a[name="dashlet_cancel_button"]',
                save: 'a[name="dashlet_save_button"]',
                edit: 'a[name="edit_button"]',
            },
            tab: '.dashlet-tabs [data-module-name="{{tabName}}"]',
            tableRow: '.table.table-striped.dataTable tbody tr',
            more_less: '.btn[data-moreless={{action}}]',
        });

        if (options.hasListView) {
            this.ListView = this.createComponent<ListViewDashletListView>(ListViewDashletListView, {
                module: options.module,
            });
        }
    }

    /**
     *  Click button at the header of the Dashable Record dashlet
     *
     * @param {string} buttonName
     */
    public async clickButton(buttonName: string) {
        let selector = this.$(`header.${buttonName}`);
        await this.driver.click(selector);
    }

    /**
     * Select specified tab in the Dashable Record dashlet
     *
     * @param {string} tabName
     */
    public async selectTab(tabName: string) {
        let selector = this.$(`tab`, {tabName});
        await this.driver.click(selector);
    }

    /**
     * Shore more or show less inside dashable record dashlet
     *
     * @param {string} action
     */
    public async expandCollapseRecord(action: string) {
        let selector = this.$(`more_less`, {action});
        await this.driver.click(selector);
    }
}
