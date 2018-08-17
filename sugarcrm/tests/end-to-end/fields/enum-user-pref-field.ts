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
                selector: ``,
            },
            options: 'option',
            option: 'option[value={{value}}]',
        });
    }

    public async setValue(value): Promise<any> {
        let select = this.$();
        let option = this.$('option', {
            name: this.name,
            value: value.replace(' ', '_').toUpperCase() || '',
        });

        await this.driver.click(option);
        await this.driver.pause(1000);
    }
}

export default Edit;
