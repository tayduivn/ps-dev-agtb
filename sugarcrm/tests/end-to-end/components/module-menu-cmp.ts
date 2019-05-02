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

import BaseView from '../views/base-view';

/**
 * Represents Modules Top Menu.
 *
 * @class ModuleMenuCmp
 * @extends BaseView
 */
export default class extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = {
            moduleList: {
                $: '.module-list',
                item: {
                    $: 'a[href="#{{itemName}}"].module-name',
                    iconStatus: '.icon.status-{{status}}'
                },
                listItem: {
                    $: 'a.module-list-link[href="#{{itemName}}"]'
                },
                currentItem: 'li.current a[href="#{{itemName}}"]',
                moreIcon: '.more button',

                moduleItems: {
                    $: '.dropdown.active',
                    caret: ' .fa.fa-caret-down',
                    // Acounts Module menu selectors
                    'Create Account': 'a[data-navbar-menu-item="LNK_NEW_ACCOUNT"]',
                    'View Accounts': 'a[data-navbar-menu-item="LNK_ACCOUNT_LIST"]',
                    'View Account Reports': 'a[data-navbar-menu-item="LNK_ACCOUNT_REPORTS"]',
                    'Import Accounts': 'a[data-navbar-menu-item="LNK_IMPORT_ACCOUNTS"]',

                    // KB Module menu selectors
                    'Create Article': 'a[data-navbar-menu-item="LNK_NEW_ARTICLE"]',
                    'Create Template': 'a[data-navbar-menu-item="LNK_NEW_KBCONTENT_TEMPLATE"]',
                    'View Articles': 'a[data-navbar-menu-item="LNK_LIST_ARTICLES"]',
                    'View Templates': 'a[data-navbar-menu-item="LNK_LIST_KBCONTENT_TEMPLATES"]',
                    'View Categories': 'a[data-navbar-menu-item="LNK_LIST_KBCATEGORIES"]',
                    'Settings': 'a[data-navbar-menu-item="LNK_KNOWLEDGE_BASE_ADMIN_MENU"]',

                    // Prospects (Targets)
                    'Create Target': 'a[data-navbar-menu-item="LNK_NEW_PROSPECT"]',
                    'Create Target From vCard': 'a[data-navbar-menu-item="LNK_IMPORT_VCARD"]',
                    'View Targets': 'a[data-navbar-menu-item="LNK_PROSPECT_LIST"]',
                    'Import Targets': 'a[data-navbar-menu-item="LNK_IMPORT_PROSPECTS"]',

                    // Prospect Lists (Target Lists)
                    'Create Target List': 'a[data-navbar-menu-item="LNK_NEW_PROSPECT_LIST"]',
                    'View Target Lists': 'a[data-navbar-menu-item="LNK_PROSPECT_LIST_LIST"]',
                },
            }
        };
    }

    /**
     * Get Module Item Selector
     *
     * @param itemName
     * @param dropdown
     * @returns {*|String}
     */
    public getModuleButtonSelector(itemName, dropdown?) {
        return (dropdown) ?
            this.$('moduleList.listItem', {itemName: itemName}) :
            this.$('moduleList.item', {itemName: itemName});
    }

    /**
     * Click on Module Item in Modules menu
     *
     * @param link
     * @param dropdown
     * @returns {*}
     */
    public async clickItem(link, dropdown?) {
        let itemSelector = this.getModuleButtonSelector(link, dropdown);

        await this.driver
            .waitForVisible(itemSelector)
            .click(itemSelector);

    }

    /**
     * Is Module Menu Item visible
     * Modules Mega menu contain visible modules and other modules are hidden in dropdown menu
     *
     * @param itemName
     */
    public async isVisible(itemName) {
        return await this.driver.isVisible(this.getModuleButtonSelector(itemName));
    }

    /**
     * Click on Modules Mege Menu dropdown to show all modules
     */
    public async showAllModules() {
        await this.driver
            .click(this.$('moduleList.moreIcon'));
    }

    /**
     * Click on Menu Item under Module's Menu in Sugar Mega Menu
     * @param {string} menuItem
     * @returns {Promise<void>}
     */
    public async clickItemUnderModuleMenu(menuItem: string) {
        await this.driver.click(this.$('moduleList.moduleItems.caret'));
        await this.driver.click(this.$(`moduleList.moduleItems.${menuItem}`));
        await this.driver.waitForApp();
    }
}
