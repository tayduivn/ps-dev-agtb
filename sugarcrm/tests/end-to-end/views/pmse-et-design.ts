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

import RecordView from './record-view';

/**
 * Represents list field .
 *
 * @class PmseEtDesign
 * @extends BaseView
 */
export default class PmseEtDesign extends RecordView {
    constructor(options) {
        super(options);
        this.selectors = this.mergeSelectors({
            $: '.record',
            buttons: {
                'subject_gear': '[data-fieldname="subject"] a[data-name="subject"]',
                'content_gear': '[data-fieldname="body_html"] .mce-container.mce-last.mce-flow-layout-item.mce-btn-group [aria-label="Fields Selector"]',
                'content_link': '[data-fieldname="body_html"] .mce-container.mce-last.mce-flow-layout-item.mce-btn-group [aria-label="Record Link Selector"]',
            },
        });
    }
    public async getField (name, type){
        if (name === 'body_html') {
            type = 'pmse_htmleditable_tinymce';
        }
        return await super.getField(name, type);
    }
}
