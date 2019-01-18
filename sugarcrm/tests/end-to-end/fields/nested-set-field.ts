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
import {BaseField} from './base-field';
import {seedbed} from '@sugarcrm/seedbed';

/**
 * @class NestedSetField represents Categories control in Knowledge Base module
 * @extends BaseField
 */
export default class NestedSetField extends BaseField {

    private listSelector: string;
    private newItemSelector: string;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: '.parenttree',
            },
            itemSelector: '.list .jstree li:nth-child({{index}})',
        });

        this.listSelector = '.jstree-focused a';
        this.newItemSelector = '.dropdown-menu .btn-link';
    }

    public async setValue(val: any): Promise<void> {

        let isFound = false;
        // Index has to be declared and initialized here for the case of no Categories (no jstree)
        // present in Categories drop-down so selector for new category can be constructed properly
        let index = 0;

        // Extend Categories drop-down control
        await this.driver.click(this.$('field.selector'));
        await this.driver.waitForApp();

        // Check if tree exists (aka. Categories drop-down is not empty)
        let isTreeExist = await this.driver.isElementExist(this.listSelector);
        if (isTreeExist) {

            // Get list of existing categories
            let itemsList: string | string[] = await this.driver.getText(this.listSelector);

            // Only one item in the tree exists
            if (!Array.isArray(itemsList)) {
                // If provided item name matches the existing item's name
                if (val === itemsList) {
                    let selector = this.$(`itemSelector`, {index: index + 1});
                    await this.driver.click(selector);
                    isFound = true;
                } else {
                    // Only one item exists in Categories drop-down
                    index++;
                }
            } else {
                // Select existing category if found
                for (index = 0; index < itemsList.length; index++) {
                    if (val === itemsList[index]) {
                        let selector = this.$(`itemSelector`, {index: index + 1});
                        await this.driver.click(selector);
                        isFound = true;
                        break;
                    }
                }
            }
        }
        await this.driver.waitForApp();

        // Create new Category if the category name is not found in the list
        if (!isFound) {
            await this.addNewCategory(index, val);
        }
    }

    /**
     * Add new KB Category
     *
     * @param {number} index
     * @param {string} val
     * @returns {Promise<void>}
     */
    public async addNewCategory(index: number, val: string): Promise<void> {
        // Click on New Category item
        await this.driver.click(`${this.newItemSelector}`);
        await this.driver.waitForApp();

        // Provide name for new category and click Enter
        await seedbed.components['KBViewCategoriesDrawer'].KBCategoriesList.createNewCategory(val);

        // Select just created category
        let selector = this.$(`itemSelector`, {index: index + 1});
        await this.driver.click(selector);
        await this.driver.waitForApp();
    }
}

export class Detail extends NestedSetField {
    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: 'a'
            }
        });
    }
}

export const Preview = Detail;
export const ListView = Detail;
