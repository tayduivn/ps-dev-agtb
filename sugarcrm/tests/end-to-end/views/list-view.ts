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
/*
 Represents List view PageObject.
 */

import BaseListView from './baselist-view';

/**
 * @class SugarCukes.ListView
 * @extends SugarCukes.BaseListView
 */
export default class ListView extends BaseListView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $ : '.flex-list-view:not([style*="display: none"])',
                errorBox: '.error-box',
                unlink: '.icon-minus-circle',
                showMoreBottom: '.show-more-bottom-btn',
                showMoreTop: '.show-more-top-btn',
                toggleAll: '.fieldset.actions.actionmenu.list  .toggle-all',
                list : {
                    allRows : 'tr[name*="{{module}}"]',
                    row : 'tr[name*="{{module}}"]:nth-child({{index}})',
                    },
                tableRow: '.table.table-striped.dataTable tbody tr',
                actionsMenu: '.fieldset.actions.actionmenu .btn.dropdown-toggle',
                actions: {
                    'Delete Selected': 'a[name="delete_button"]:not(.hide)',
                    'Export': 'a[name="export_button"]:not(.hide)',
                    'Mass Update': 'a[name="massupdate_button"]:not(.hide)',
                    'Merge': 'a[name="merge_button"]:not(.hide)',
                    'Add To Target List': 'a[name="addtolist_button"]:not(.hide)',
                    'Recalculate Values': 'a[name="calc_field_button"]:not(.hide)',
                 }
            });
    }

    public async toggleAll() {
        await this.driver.click(this.$('toggleAll'));
    }

    public async getNumberOfRecords() {
        let driver: any = this.driver;
        let rows = await driver.elements(this.$('tableRow'));
        return rows.value.length;
    }

    public async toggleActionsDropdown() {
        await this.driver.click(this.$('actionsMenu'));
    }

    public async selectAction(action) {
        await this.toggleActionsDropdown();
        await this.driver.click(this.$('actions.' + action));
    }
}
