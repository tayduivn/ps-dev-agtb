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
            $: '[field-name={{name}}]',
            field: {
                selector: 'textarea.htmleditable'
            },
            iframe: 'iframe',
        });
    }

    public async getText(selector: string): Promise<string> {
        let iframeSelector = this.$('iframe');
        await this.driver.waitForVisible(iframeSelector);
        let id = await this.driver.getAttribute(iframeSelector, 'id');
        id = id.substring(0, id.length - 4);
        const obj = await seedbed.client.driver.execSync('getValueForTinyMCE', [id]);
        return (obj.value).toString().trim();
    }

    public async setValue(val: any): Promise<void> {
        let argumentsArray = [];
        let iframeSelector = this.$('iframe');
        await this.driver.waitForVisible(iframeSelector);
        let id = await this.driver.getAttribute(iframeSelector, 'id');
        id = id.substring(0, id.length - 4);
        argumentsArray.push(id);
        argumentsArray.push(val);
        await seedbed.client.driver.execSync('setValueForTinyMCE', argumentsArray);
    }
}

export class Detail extends HtmleditableTinymceField {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            field: {
                selector: 'iframe.htmleditable'
            },
        });
    }

    public async getText(selector: string): Promise<string> {
        let iframeSelector = this.$('iframe');
        await this.driver.waitForVisible(iframeSelector);
        let name = await this.driver.getAttribute(iframeSelector, 'name');
        let value = await seedbed.client.driver.execSync('getiFrameValue', [name]);
        return value.value;
    }
}

