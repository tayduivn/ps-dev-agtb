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
 * @class HtmleditableTinymceField
 * @extends BaseField
 */

export default class HtmleditableTinymceField extends BaseField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: ''
            },
            iframe:'iframe',
           });
    }

    public async getText(selector: string): Promise<string> {
        let iframeSelector = this.$('iframe');

        await this.driver.waitForVisible(iframeSelector);
        let id = await this.driver.getAttribute(iframeSelector,'id');
        //Execute the client script within the context of the TinyMce iframe
        await this.driver.frame(id);
        const obj = await seedbed.client.driver.execSync('getValueForTinyMCE');
        await this.driver.frame(null);

        return (obj.value).toString().trim();
    }

    public async setValue(val: any): Promise<void> {
        await seedbed.client.driver.execSync('setValueForTinyMCE', [val]);
    }
}
