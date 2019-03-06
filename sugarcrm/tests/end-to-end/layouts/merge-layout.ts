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
import DrawerLayout from './drawer-layout';

/**
 * Represents Merge Records layout drawer
 *
 * @class MergeLayout represents Merge Records layout drawer
 * @extends DrawerLayout
 */
export default class MergeLayout extends DrawerLayout {

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: '.drawer.active',
            primary: {
                $: '.col.primary-edit-mode',
                primaryField:  'input[name={{fieldName}}]',
                primaryRadio: 'input[name=copy_{{fieldName}}]',
                primaryClose: '.fa-times-circle'
            },
            secondary: {
                $: '.col:not(.primary-edit-mode)',
                secondaryRadio: 'input[name=copy_{{fieldName}}]',
                secondaryClose: '.fa-times-circle'
            },
        });

        this.type = 'drawer';
    }

    /**
     * Update value of primary field based on the field name
     *
     * @param {string} fieldName field name to update before merge
     * @param {string} fieldValue new field value
     * @returns {Promise<any>}
     */
    public async setNewPrimaryFieldValue(fieldName: string, fieldValue: string) {
        let selector = this.$(`primary.primaryField`, {fieldName: fieldName});
        return this.driver.setValue(selector, fieldValue);
    }

    /**
     * Select specific field from secondary record to be primary field
     *
     * @param {string} fieldName field name to update
     * @returns {Promise<any>}
     */
    public async changePrimaryField(fieldName: string) {
        let selector = this.$(`secondary.secondaryRadio`, {fieldName: fieldName});
        return this.driver.click(selector);
    }

    /**
     * Remove record from the list of records to be merged
     *
     * @param {string} action
     * @returns {Promise<any>}
     */
    public async removeRecord(action: string) {
        let selector = this.$(`${action}.${action}Close`);
        return this.driver.click(selector);
    }
}
