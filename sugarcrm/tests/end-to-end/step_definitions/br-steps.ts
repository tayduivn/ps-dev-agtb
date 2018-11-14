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

import ModuleMenuCmp from '../components/module-menu-cmp';
import SearchModuleMenuCmp from '../views/search-module-menu-cmp';
import {whenStepsHelper, stepsHelper, Utils, When, Then, Given, seedbed} from '@sugarcrm/seedbed';
import {TableDefinition} from 'cucumber';
import {italic} from "chalk";

// BR-FTS:
When(/^I choose modules "([^"]*)" to search$/, async function (modulesList: string) {

    const searchMenu = new SearchModuleMenuCmp({});

    await searchMenu.toggleMenu();


    // first splict the "modulesList" into seperate modules names and store in array
    let modulesArray = modulesList.split(',');

    for (let i in modulesArray) {
        await searchMenu.clickModuleToSearch(modulesArray[i]);
    }

}, {waitForApp: true});


// BR-FTS:
When(/^I search for text "([^"]*)" with tags "([^"]*)" in globalsearch before confirming$/, async function (targetText: string, tagName: string) {

    const searchMenu = new SearchModuleMenuCmp({});

    // aways type in the Tags to search first if there is any involved
    if (tagName.length > 0) {
        // splitting the tags list and save in an array
        let tagsArray = tagName.split(',');

        for (let i in tagsArray) {

            await searchMenu.typeSearchTags(tagsArray[i]);
        }

    }
    // after typing in the tag name, type in the text to search if there is any
    await searchMenu.typeSearchText(targetText);
    // Sep 28, moving the following code as a separate step so that we can do different tests before and after the pressing the "Enter" key
    // await searchMenu.pressEnterToSearch();

}, {waitForApp: true});


// BR-FTS: This is basically to press the enter key after typing text to search
// we are making the "Enter" as a separate step because there are different items to check before and after pressing "Enter"
// Specifically, we need to check the highlighted texts/items from the type-ahead info that are displayed as you type something into the FTS input box, which will disappear after you press "Enter".
When(/^I confirm to search whatever in the search input box$/, async function() {
   const searchMenu = new SearchModuleMenuCmp({});
   await searchMenu.pressEnterToSearch();

}, {waitForApp: true});


// BR-FTS: The following is actually the Then function for Global Search
Then(/^I should see "([^"]*)" displayed as number of match under search result$/, async function (expectedNumReturned: number) {

    const searchMenu = new SearchModuleMenuCmp({});
    let actualTotalReturned = await searchMenu.getTotalNumberOfMatch();
    // let actualTotalInteger: number = Number.parseInt( actualTotalReturned );

    if (actualTotalReturned != expectedNumReturned) {

        throw new Error('Total number of matches in search result does not match expected. ' + ' Actual: ' + actualTotalReturned + '. Expected: ' + expectedNumReturned + ".");
    }

}, {waitForApp: true});


// BR-FTS:
Then(/^I should see search result filters "([^"]*)" under the Filter right hand side pane*/, async function (filterList: string) {

    const searchMenu = new SearchModuleMenuCmp({});

    if (filterList.trim().length === 0) {
        return;
    }

    let filterArray = filterList.split(',');
    for (let i in filterArray) {
        let currentResult = await searchMenu.isFilterDisplayed(filterArray[i]);
        if (!currentResult) {
            throw new Error(filterArray[i] + ' should be displayed but was not found!');
        }
    }

}, {waitForApp: true});


// BR-FTS:
Then(/^I should see list of highlighted items "([^"]*)" under the search type ahead results$/, async function (highlightlist: string) {
    const searchMenu = new SearchModuleMenuCmp({});

    // Do nothing if there is nothing in the highlighted items list.
    if (highlightlist.trim().length === 0) {
        return;
    }

    // Split/parse the highlighted item list into elements of an array so that we can check them one by one.
    let expectedHLArray = highlightlist.split(',');

    // Somehow we need to explictly call the "waitForApp" as below before we start to check the highlighted items from the type-ahead search results.
    await this.driver.waitForApp();

    for (let i in expectedHLArray) {
        let currentResult = await searchMenu.isHighLightedEmail(expectedHLArray[i]);

        if (!currentResult) {
            throw new Error('Expected ' + expectedHLArray[i] + ' to be highlighted but is not!');
        }
    }

    await this.driver.waitForApp();

}, {waitForApp: true});

