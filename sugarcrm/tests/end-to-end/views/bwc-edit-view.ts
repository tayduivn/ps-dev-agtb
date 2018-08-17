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

'use strict';
import BaseView from './base-view';

/**
 * Represents Edit view page PageObject for BWC pages
 * such as System Settings edit mode
 *
 * @class BWCEditView
 * @extends BaseView
 */
export default class BWCEditView extends BaseView {
    constructor(options) {
        super(options);
        this.selectors = this.mergeSelectors({
            buttons: {
                save: '#ConfigureSettings_save_button[name="save"]',
                cancel: '#ConfigureSettings_cancel_button[name="cancel"]',
            },
        });
    }
}
