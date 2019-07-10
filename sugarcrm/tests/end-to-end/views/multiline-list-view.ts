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
import MultilineListItemView from './multiline-list-item-view';
import * as _ from 'lodash';

/**
 * @class MultilineListView
 * @extends BaseListView
 */
export default class MultilineListView extends BaseListView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $ : '.multi-line-list-view',
        });
    }

    /**
     *  Create MultilineListItemView component based on the record ID.
     *
     * @param  {object} conditions
     * @return {object} listViewItem
     */
    public createListItem(conditions) {

        if (!(conditions || conditions.id)) {
            return null;
        }

        let listViewItem = this.createComponent<MultilineListItemView>(MultilineListItemView, {
            id: conditions.id,
            module: this.module,
        });

        this.listItems.push(listViewItem);
        return listViewItem;
    }

    /**
     * Get list item by specified conditions
     *
     * @param {object} conditions
     * @return {object} listViewItem
     */
    public getListItem(conditions) {
        let keys = _.keys(conditions);
        let listViewItem;
        let listItems;

        if (keys.length !== 1 || !_.includes(['id', 'index', 'current'], keys[0])) {
            return null;
        } else {
            listItems = _.filter(this.listItems, conditions),
            listViewItem = listItems.length ? listItems[0] : null;

            if (!listViewItem) {
                listViewItem = this.createListItem(conditions);
            }
            return listViewItem;
        }
    }
}
