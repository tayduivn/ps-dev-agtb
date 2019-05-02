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

import BaseView from './base-view';

/**
 * Represents DashboardHeaderView view.
 *
 * @class DashboardHeaderView
 * @extends BaseView
 */
export default class DashboardHeaderView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.preview-headerbar',
            buttons: {
                create: '.btn-toolbar a[name="add_button"]',
                save: '.btn-toolbar a[name="create_button"]',
                cancel: '.btn-toolbar a[name="create_cancel_button"]',
                actions: '.actions:not([style*="display: none"]) a.btn.dropdown-toggle',
                edit: 'a[name="edit_button"]',
                delete: 'a[name="delete_button"]',
                edit_save: 'a[name="save_button"]',
                edit_cancel: 'a[name="cancel_button"]',
            }
        });
    }
}
