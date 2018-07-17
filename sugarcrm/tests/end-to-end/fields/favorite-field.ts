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

/**
 * @class FavoriteField
 * @extends BaseField
 */
export default class FavoriteField extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '[field-name={{name}}]',
            field: {
                selector: '.fa.fa-favorite'
            },
            button: 'button',
        });
    }

    public async setValue(val:string): Promise<void> {

        let currValue = await (this.driver.getAttribute(this.$('button'), 'class') as any);

        const isFavorite = (currValue.indexOf("active") != -1);
        const myVal = val.trim().toLowerCase();
        const isTrueSet = (myVal === 'true');

        // Mark record as favorite
        if ( isTrueSet  && !isFavorite )
            await this.driver.click(this.$('field.selector'));

        // Remove record from favorites
        if (!isTrueSet && isFavorite)
            await this.driver.click(this.$('field.selector'));
    }
}
