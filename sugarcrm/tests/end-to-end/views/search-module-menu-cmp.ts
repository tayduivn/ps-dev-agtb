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
 * Represents Global Search page.
 *
 * @class SearchModuleMenuCmp
 * @extends BaseView
 */
export default class SearchModuleMenuCmp extends BaseView {
    private locator_fts_input = 'input.search-query[aria-label="Global Search"]';
    private locator_searchTobalReturned = 'span[data-fieldname="collection-count"] span.count';
    /** The following fts expanded input will exist if the search box is expanded. */
    private locator_searchExpanded = 'div.search.expanded';
    private searchAllMenuItem = '.table-cell.quicksearch-modulelist-wrapper .menu-item.ellipsis_inline[data-action="select-all"]';

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

    /**
     *  Close the Global Search Input box by clicking on the "x" button, which will also clear the search keywords/tags. This function also reset the modules to search back to "All"
     */
    public async clearSearchInputBox() {
        let xButton = 'div.quicksearch-button-wrapper .btn.btn-invisible';
        await this.driver.waitForVisibleAndClick( xButton );
        await this.clickModuleToSearch('all');
    }

    /**
     * The following will click a module name from the global search filter pulldown.
     * */
    public async clickModuleToSearch(moduleName: string) {
        let itemSelector;

        /** if search input box is not expanded, expand it. Hmm...you can use toogleSearchBox to close the search input, but cannot expand it. There fore, we use click: */
        if (!(await this.isSearchExpanded())) {
            await this.driver.waitForVisibleAndClick(this.locator_fts_input);
        }

        /** If the search module dropdown list is not expanded, expand it. */
        if ( ! (await this.driver.isVisible( this.searchAllMenuItem ) )) {
            /** The following waitForApp is somehow required, otherwise the toggleMenu() cannot click on the module list dropdown. */
            await this.driver.waitForApp();
            await this.toggleMenu();
        }

        /** when "Search all" is selected, the menu item locator does not match the format defined in the "this.selectors" so we need a special case for it. */
        if (moduleName.toLowerCase() === 'all') {
            itemSelector = this.searchAllMenuItem;
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
        await this.driver.waitForVisibleAndClick(theMoreIcon);
    }

    /**
     * Type the tag names and select it if Sugar displays the matching tag
     */
    public async typeSearchTags(inputTag: string) {
        await this.driver.waitForVisibleAndClick(this.locator_fts_input);
        await this.driver.setValue(this.locator_fts_input, inputTag);
        /** The following will wait for the tag prompt to show up then click on it */
        let tagDisplayed = 'a[track="click:' + inputTag + '"]';
        await this.driver.waitForVisibleAndClick(tagDisplayed);
    }

    /**
     * Type the search keywords into the Global Search input box
     */
    public async typeSearchText(inputText: string) {
        await this.driver.waitForVisibleAndClick(this.locator_fts_input);
        await this.driver.setValue(this.locator_fts_input, inputText);
    }

    /**
     * Press the Enter key after finishing typing the text to search
     */
    public async pressEnterToSearch() {
        /** First click inside the Global Search input box before press "Enter" key */
        await this.driver.waitForVisibleAndClick(this.locator_fts_input);
        /** Have not figured out how to press enter except for calling the "keys" function and passing in unicode for "Enter" */
        await this.driver.keys('\uE007');
    }

    /**
     * Retrieve the total number of records returned as the search result
     */
    public async getTotalNumberOfMatch(): Promise<number> {
        /** first retrieve the number including the enclosing parenthesises */
        let rawNum = await this.driver.getText(this.locator_searchTobalReturned);
        /** Then remove the enclosing parenthesises */
        let strOnlyNumPart = await rawNum.replace(/\s*\((\d+)\)\s*/, '$1');
        /** convert str to number: */
        let numResult: number = (Number(strOnlyNumPart)).valueOf();
        /** Now only the pure number part is returned */
        return Promise.resolve(numResult);
    }

    /**
     * To check if the aggrecation filter passed in exists or not
     */
    public async isFilterDisplayed(oneFilter: string) {
        /** find the element by XPATH here since it's not easy for css selector to find elements based on the text content */
        return this.driver.isElementExist('//span[text()="' + oneFilter + '"]');
    }

    /**
     * To check if an item passed in is highlighted
     */
    public async isHighLightedItem(oneItem: string): Promise<boolean> {
        let currentLocator = '//div[@class = "typeahead-wrapper"]/descendant::strong[text()="' + oneItem + '"]';
        let currentCheckResult = await this.driver.isElementExist( currentLocator);
        return Promise.resolve(currentCheckResult);
    }


    /**
     * To check if the FTS search box is expanded or not:
     */
    public async isSearchExpanded(): Promise<boolean> {
        return this.driver.isElementExist(this.locator_searchExpanded);

    }

    /**
     * Perform Global Search against eash test case from the data table.
     */
    public async searchOnTableData(oneRow: any): Promise<Object> {
        let failureMessage = '';
        let testCaseTitle = oneRow['testCaseTitle'];
        let modulesList = oneRow['modulesToSearch'];
        let tagName = oneRow['tagsToSearch'];
        let targetText = oneRow['textToSearch'];
        let highlightlist = oneRow['expectedHighlightedItemsList'];
        let expectedNumReturned: number = Number(oneRow['expectedNumOfMatch']);
        let filterList = oneRow['filtersToSearch'];

        let currentErr: any = {
            testCaseName:  testCaseTitle,
            messages: [],
        };

        /** the following statements choose the modules to be included in the current search */
       if ( ! (await this.driver.isVisible( this.searchAllMenuItem ) )) {
            await this.toggleMenu();
       }

        /** first split the "modulesList" into separate modules names and store in array */
        let modulesArray = modulesList.split(',');

        for (let i in modulesArray) {
            await this.clickModuleToSearch(modulesArray[i]);
        }

        /** The following "if block" types in all the tags to be included in the search if there is any */
        if (tagName.length > 0) {
            /** splitting the tags list and save in an array */
            let tagsArray = tagName.split(',');

            for (let i in tagsArray) {
                await this.typeSearchTags(tagsArray[i]);
            }
        }

        /** after typing in the tag name, type in the text to search if there is any */
        await this.typeSearchText(targetText);
        /** The following is to wait a few seconds for the type-ahead drop-down to show up since somehow calling "await this.driver.waitForApp()" does not work here. */
        await this.driver.pause(1000);
        await this.driver.waitForApp();

        /** Now the type-ahead drop-down should be showing up ready to be checked */
        if (highlightlist.trim().length > 0) {
            /** Split/parse the highlighted item list into elements of an array so that we can check them one by one. */
            let expectedHLArray = highlightlist.split(',');
            let missedHLItems = '';

            for (let i in expectedHLArray) {
                let currentResult = await this.isHighLightedItem(expectedHLArray[i]);

                if ( !currentResult ) {
                    missedHLItems = missedHLItems + '\'' + expectedHLArray[i] + '\', ';
                }

            }

            if ( missedHLItems.length > 0) {
                if ( missedHLItems.trim().match(/^[^,]+,$/) ) {
                    missedHLItems = missedHLItems.trim().replace(/([^,]),/, '$1 is');
                } else {
                    missedHLItems = missedHLItems.trim().replace(/(.*),([^,]+),/, '$1 and $2 are');
                }

                missedHLItems = 'Cause: Missed Highlighted Items (' + missedHLItems + ' expected to be highlighted but not seen) ';
                currentErr.messages.push(missedHLItems);
            }
        }

        /** Press Enter key after checking the highlighted items from the type-ahead dropdown */
        await this.pressEnterToSearch();
        /** The following waitForApp is required, otherwise the total number of matched records won't be displayed. */
        await this.driver.waitForApp();
        /** Getting the total of returned records: */
        let actualTotalReturned: number = await this.getTotalNumberOfMatch();

        if (actualTotalReturned != expectedNumReturned) {
            currentErr.messages.push('Cause:Un-matched Total Returned ' + '(Actual:' + actualTotalReturned + '. Expected:' + expectedNumReturned + ')');
        }


        if (filterList.trim().length > 0) {
            let missedFilter = '';
            let filterArray = filterList.split(',');

            for (let i in filterArray) {
                let currentResult = await this.isFilterDisplayed(filterArray[i]);
                if (!currentResult) {
                    missedFilter = missedFilter + '\'' + filterArray[i] + '\', ';
                }
            }

            if ( missedFilter.length > 0) {
                if ( missedFilter.trim().match(/^[^,]+,$/) ) {
                    missedFilter = missedFilter.trim().replace(/([^,]),/, '$1 is');
                } else {
                    missedFilter = missedFilter.trim().replace(/(.*),([^,]+),/, '$1 and $2 are');
                }

                missedFilter = 'Cause: Incorrect Filters (' + missedFilter + ' expected but not displayed correctly) ';
                currentErr.messages.push(missedFilter);
            }
        }

        await this.clearSearchInputBox();
        return Promise.resolve(currentErr);
    }


}








