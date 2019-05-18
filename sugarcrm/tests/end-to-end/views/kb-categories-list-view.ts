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
/*
 Represents List view PageObject.
 */

import BaseView from './base-view';
import * as _ from 'lodash';
import KbListItemView from './kb-categories-list-item-view';
import {KeyCodes} from '../step_definitions/steps-helper';

/**
 * @class KbCategoriesListView
 * @extends BaseListView
 */
export class KbCategoriesListView extends BaseView {

    private globalSelectors: any;
    public listItems: KbListItemView[] = [];
    private diabledContextMenu: string;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.jstree',
            listItem: 'li[data-id*="{{id}}"]',
            row: 'li',
            contextMenu: '.btn.jstree-contextmenu',

        });

        this.globalSelectors = {
            edit: 'a[rel="Edit"]',
            moveup: 'a[rel="moveup"]',
            movedown: 'a[rel="movedown"]',
            moveto: 'a[rel="moveto"]',
            'delete': 'a[rel="delete"]',
        };
        this.diabledContextMenu = '.jstree-contextmenu-disabled';
    }

    /**
     * Create New Category
     *
     * @param {string} categoryName name of the category to create
     * @returns {Promise<any>}
     */
    public async createNewCategory(categoryName: string): Promise<any> {
        await this.driver.keys(categoryName);
        // Press <enter>
        await this.driver.keys(KeyCodes.ENTER);
        await this.driver.waitForApp();
    }

    /**
     * Open context menu of specified Category
     *
     * @param id
     * @returns {Promise<void>}
     */
    public async openContextMenu(id) {
        await this.driver.click(this.$('contextMenu', {id}));
        await this.driver.waitForApp();
    }

    /**
     * Edit specific category name in KB Categories drawer
     *
     * @param {number} id
     * @param {string} val
     * @returns {Promise<void>}
     */
    public async editCategory(id, val: string) {

        await this.openContextMenu(id);

        // Edit specified category name
        await this.driver.click(this.globalSelectors['edit']);
        await this.driver.keys(val);
        // Press enter
        await this.driver.keys(KeyCodes.ENTER);
    }

    /**
     * Move specific category up or down in the list
     *
     * @param id
     * @param {string} action
     * @returns {Promise<void>}
     */
    public async moveCategory(id, action: string) {

        await this.openContextMenu(id);

        // Check if menu item is not disabled (is enabled)
        let selector = `${this.diabledContextMenu} ${this.globalSelectors[action]}`;
        let isElementExist = await this.driver.isElementExist(selector);

        if ( !isElementExist ) {
            // Move specified category
            await this.driver.click(this.globalSelectors[action]);
            await this.driver.waitForApp();
        } else {
            throw new Error(`Element ${selector} is not active`);
        }
    }

    /**
     *  Return number of records in Categories
     *
     * @returns {Promise<void>}
     */
    public async getNumberOfRecords() {
        let rows = await this.driver.elements(this.$('row'));
        return rows.value.length;
    }

    /**
     * Returns a list item or creates one if it does not exist
     *
     * @param {Object} conditions The record ID or other conditions of the list item to return
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
     */
    public createListItem(conditions) {

        if (!(conditions || conditions.id)) {
            return null;
        }

        let listViewItem = this.createComponent<KbListItemView>(KbListItemView, {
            id: conditions.id,
            module: 'Categories',
        });

        this.listItems.push(listViewItem);

        return listViewItem;
    }
}
