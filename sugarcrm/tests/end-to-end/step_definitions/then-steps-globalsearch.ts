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

import SearchModuleMenuCmp from '../views/search-module-menu-cmp';
import {When, Then} from '@sugarcrm/seedbed';
import {TableDefinition} from 'cucumber';

Then(/I should be able to perform global search against each test case from the following table/, async function(testCaseTable: TableDefinition) {
    const searchMenu = new SearchModuleMenuCmp({});
    let tableFields: any = testCaseTable.hashes();
    let exceptionsArray: Array<Object> = [];

    for (let oneRow of tableFields ) {
        let oneResult: any = await searchMenu.searchOnTableData( oneRow );
        let errCount: number = oneResult.messages.length;
        // console.log('One test case error count: ' + errCount);
        if ( errCount !== 0 ) {
            exceptionsArray.push( oneResult );
        }
    }

    if ( exceptionsArray.length !== 0 ) {
        throw new Error( JSON.stringify(exceptionsArray) );
    }

}, {waitForApp: true});
