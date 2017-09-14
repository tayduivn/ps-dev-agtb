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

import {givenStepsHelper, stepsHelper, Given} from '@sugarcrm/seedbed';
import {TableDefinition} from 'cucumber';

Given(/^I (launch|update) (?:App)\s*(?:(?:with)?\s*config\s*(?:with)?: "([^"]*)")?$/,
    (launch: string, schemesList: string): Promise<void> =>
        givenStepsHelper.launchOrUpdate(launch, schemesList));

Given(/^I use\s*(default)?\s*account\s*(?:"([^"]*)"(?:\/"([^"]*)")?)?$/,
    (isDefaultAccount: string, username: string, password: string): Promise<void> =>
        givenStepsHelper.useAccount(isDefaultAccount, username, password));

Given(/^(?:(\d+) )?(\w+) records exist( created by bulk)?:$/,
    (count: number, module: string, byBulk: boolean, table: TableDefinition): Promise<void> =>
        stepsHelper.createRecords(count, module, byBulk, table));

Given<
    string,
    string
    >(
    /^(?:(\d+) )?(\w+) records exist related via (\w+) link\s*(?:to (\*\S+))?:$/,
    function(
        countStr: string,
        module: string,
        link: string,
        record: any,
        table: TableDefinition
    ): Promise<void> {
        return givenStepsHelper.createRelatedRecord(
            countStr,
            module,
            link,
            table,
            record
        );
    }
);
