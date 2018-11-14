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

/** import {BaseView} from '@sugarcrm/seedbed'; */

import BaseView from './base-view';


/**
 * Represents Modules Top Menu.
 *
 * @class ModuleMenuCmp
 * @extends BaseView
 */
export default class SearchModuleMenuCmp extends BaseView {

    // BR-FTS
    private locator_fts_input = 'input.search-query[aria-label="Global Search"]';
    // BR-FTS:
    private locator_searchTobalReturned = 'span[data-fieldname="collection-count"] span.count';

    // BR-FTS -- The following fts expanded input will exist if the search box is expanded.
    private locator_searchExpanded = 'div.search.expanded';

    constructor(options) {

        super(options || {});

        this.selectors = this.mergeSelectors({
            moduleList: {
                $: '.table-cell.quicksearch-modulelist-wrapper',
                moreIcon: '.module-dropdown-button.btn.btn-invisible',
                listItem: {
                    $: '.menu-item.ellipsis_inline[data-module="{{moduleName}}"]'
                },
            }
        });
    }



    // BR-FTS: the following will click a module name from the global search filter pulldown.
    public async clickModuleToSearch(moduleName: string) {

        let itemSelector;

        // BR-FTS: if search input box is not expanded, expand it. Hmm...you can use toogleSearchBox to close the search input, but cannot expand it. There fore, we use click:
        if (!(await this.isSearchExpanded())) {
            await this.driver.waitForVisibleAndClick(this.locator_fts_input);
        }

        // when "Search all" is selected, the menu item locator does not match the format defined in the "this.selectors" so we need a special case for it.
        if (moduleName.toLowerCase() === 'all') {

            itemSelector = '.table-cell.quicksearch-modulelist-wrapper .menu-item.ellipsis_inline[data-action="select-all"]';
        } else {
            itemSelector = this.$('moduleList.listItem', {moduleName: moduleName});
        }

        await this.driver.waitForVisible(itemSelector);
        await this.driver.click(itemSelector);

    }


    /**
     * Click on Modules Mege Menu dropdown to show all modules
     */
    public async toggleMenu() {
        let theMoreIcon = this.$('moduleList.moreIcon');
        await this.driver.click(theMoreIcon);
    }



    // BR-FTS: type the tag names and select it if Sugar displays the matching tag
    public async typeSearchTags(inputTag: string) {

        await this.driver.waitForVisibleAndClick(this.locator_fts_input);
        await this.driver.setValue(this.locator_fts_input, inputTag);

        /** The following will wait for the tag prompt to show up then click on it */
        let tagDisplayed = 'a[track="click:' + inputTag + '"]';
        await this.driver.waitForVisibleAndClick(tagDisplayed);

    }

    // BR-FTS: type the text to search into the Global Search input box
    public async typeSearchText(inputText: string) {

        await this.driver.waitForVisibleAndClick(this.locator_fts_input);
        await this.driver.setValue(this.locator_fts_input, inputText);

    }

    // BR-FTS: press the Enter key after finishing typing the text to search
    public async pressEnterToSearch() {
        // First click inside the Global Search input box before press "Enter" key
        await this.driver.waitForVisibleAndClick(this.locator_fts_input);

        // Have not figured out how to press enter except for calling the "keys" function and passing in unicode for "Enter"
        await this.driver.keys('\uE007');
    }

    // BR-FTS: retrieve the total number of records returned as the search result
    public async getTotalNumberOfMatch(): Promise<number> {
        // first retrieve the number including the enclosing parenthesises
        let rawNum = await this.driver.getText(this.locator_searchTobalReturned);
        // Then remove the enclosing parenthesises
        let strOnlyNumPart = await rawNum.replace(/\s*\((\d+)\)\s*/, "$1");

        // convert str to number:
        let numResult: number = Number(strOnlyNumPart);
        // Now only the pure number part is returned
        return Promise.resolve(numResult);

    }

    // BR-FTS: to check if the aggrecation filter passed in exists or not
    // find the element by XPATH here since it's not easy for css selector to find elements based on the text content
    public async isFilterDisplayed(oneFilter: string) {
        return this.driver.isElementExist('//span[text()="' + oneFilter + '"]');

    }

    // BR-FTS: to check if an item passed in is highlighted
    public async isHighLightedItem(oneItem: string) {
        let currentLocator = '//div[@class = "typeahead-wrapper"]/descendant::strong[text()="' + oneItem + '"]';
        return this.driver.isElementExist( currentLocator );
    }


    // BR-FTS: to check if the FTS search box is expanded or not:
    public async isSearchExpanded(): Promise<boolean> {
        return this.driver.isElementExist(this.locator_searchExpanded);

    }


}








