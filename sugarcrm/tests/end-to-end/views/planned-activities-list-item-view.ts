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
import BaseListItemView from './list-item-view';

/**
 * @class PlannedActivitiesListItemView
 * @extends ListItemView
 */
export default class PlannedActivitiesListItemView extends BaseListItemView {

    public id: string;
    public index: number;
    public current: boolean;

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: 'li[data-record-id*="{{id}}"]',
            row: {
                name: 'p a:not(.pull-left)',
                label: '.label.label-important',
            },

            // Actions available for each meeting or call record
            actions: {
                held: '.fa.fa-times-circle',
                accepted: '.fa.fa-check-circle',
                tentative: '.fa.fa-question-circle',
                declined: '.fa.fa-ban',
            }
        });

        this.id = options.id;
        this.index = options.index;
        this.current = !this.id && !this.index;
    }

    /**
     * Get record info based on specified field name
     *
     * @param {string} fieldName
     * @return {string} value of the field
     */
    public async getRecordInfo(fieldName: string) {
        let selector = this.$(`row.${fieldName}`);
        return await this.driver.getText(selector);
    }

    /**
     * Select specified action
     *
     * @param {string} action
     */
    public async selectAction(action: string) {
        let selector = this.$(`actions.${action}`);
        await this.driver.click(selector);
    }
}
