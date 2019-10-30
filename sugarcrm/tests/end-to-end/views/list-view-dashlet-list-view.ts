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

import BaseListView from './baselist-view';
import RecordInteractionsListItemView from './record-interactions-list-item-view';
import * as _ from 'lodash';
import ListViewDashletListItemView from './list-view-dashlet-list-item-view';

/**
 * @class RecordInteractionsListView represents list view in Cases Interactions dashlet
 * @extends BaseListView
 */
export default class ListViewDashletListView extends BaseListView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.table.table-striped.dataTable tbody',
            tableRow: 'tr',
        });
    }

    /**
     * Returns a list item or creates one if it does not exist
     *
     * @param {Object} conditions The record ID or other conditions of the list item to return
     * @return {Object} listViewItem
     */
    public getListItem(conditions) {
        let keys = _.keys(conditions);
        let listItems;
        let listViewItem;

        if (keys.length !== 1 || !_.includes(['id', 'index', 'current'], keys[0])) {
            return null;
        }

        listItems = _.filter(this.listItems, conditions);
        listViewItem = listItems.length ? listItems[0] : null;

        if (!listViewItem) {
            listViewItem = this.createListItem(conditions);
        }
        return listViewItem;
    }

    /**
     * Creates and returns a list item based on conditions
     *
     * @param {Object} conditions The record ID or other conditions of the list item to return
     * @return {RecordInteractionsListItemView} listViewItem
     */
    public createListItem(conditions) {

        if (!(conditions || conditions.id)) {
            return null;
        }

        let listViewItem = this.createComponent<ListViewDashletListItemView>(ListViewDashletListItemView, {
            id: conditions.id,
        });

        this.listItems.push(listViewItem);
        return listViewItem;
    }

    /**
     *  Calculate number of records in List View dashlet
     *
     *  @return {number} number of records in the list view
     */
    public async getNumberOfRecords() {
        let driver: any = this.driver;
        let rows = await driver.elements(this.$('tableRow'));
        return rows.value.length;
    }
}
