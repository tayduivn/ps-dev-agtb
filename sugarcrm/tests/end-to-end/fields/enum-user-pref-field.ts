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

import EditBWC from './enum-field';

/**
 * @class EnumUserPrefField
 * @extends EditBWC
 */
export class Edit extends EditBWC {
    constructor(options: any) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: `select[name={{name}}]`,
            field: {
                selector: '',
            },
            option: 'option[value={{value}}]',
        });
    }

    public async setValue(value): Promise<any> {

        // Max number of licenses
        const MAX_NUM_OF_ITEMS = 3;
        // Value for Sugar Ent in License Types dropdown
        const SUGAR_ENT = "CURRENT";
        // Wait in milliseconds
        const WAIT_TIME = 100;

        let currentlySelectedItem: string;

        // Unselect all currently selected items
        for (let i = 1; i <= MAX_NUM_OF_ITEMS ; i++) {
            // Build selector to currently selected item
            currentlySelectedItem = `${this.$()} option:nth-child(${i})[selected]`;
            // if selected item is found, unselect it.
            if (await this.driver.isElementExist(currentlySelectedItem) ) {
                await this.driver.click(currentlySelectedItem);
                await this.driver.pause(WAIT_TIME);
            }
        }

        let values = value.split(',');

        for (let i = 0; i < values.length; i++) {
            // The Sugar Enterprise license is represented with value equals to 'current'
            if (values[i].toUpperCase().indexOf('ENT') !== -1) {
                values[i] = SUGAR_ENT;
            }

            // Build selectors to a new item to be selected
            let option = this.$('option', {
                name: this.name,
                value: values[i].trim().replace(' ', '_').toUpperCase() || '',
            });

            // Select first item
            await this.driver.click(option);
            await this.driver.pause(WAIT_TIME);
        }
    }
}

export default Edit;
