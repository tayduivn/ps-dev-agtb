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

import {TableDefinition} from 'cucumber';
import {When, seedbed} from '@sugarcrm/seedbed';
import {chooseModule, chooseRecord, closeAlert, provideRecordViewInput, recordViewActionsMenuItemClick, recordViewHeaderButtonClicks} from './general_bdd';

/**
 *  Convert (or cancel copying of) target to lead
 *
 *  @example
 *      When I convert *Pr_1 record in Prospects record view with the following header values:
 *          | *   | first_name | last_name |
 *          | L_1 | Lead_F     | Lead_L    |
 *
 */
When(/^I (convert|cancel conversion of) \*(\w+) record in (\w+) record view with the following header values:$/,
    async function (action: string, name: string, module: string, table: TableDefinition) {
        // TODO: In the future we should check the current route and if we are already on the correct module/record
        await chooseModule(module);
        let view = await seedbed.components[`${module}List`].ListView;
        let record = await seedbed.cachedRecords.get(name);

        // Navigate to record view of specified record
        await chooseRecord({id: record.id}, view);
        let rec_view = await seedbed.components[`${name}Record`];
        let drawer_view = await seedbed.components[`${module}Drawer`];

        // Select 'Convert Target' action in the Actions menu
        await recordViewActionsMenuItemClick('converttarget', rec_view);

        // Expand record view
        await recordViewHeaderButtonClicks('show more', drawer_view);

        // Provide record input
        // TODO: currently only Header changes are allowed.
        // TODO: AT-238 is filed to address this limitation
        await provideRecordViewInput(drawer_view.HeaderView, table);

        // Proceed with cancel or save
        switch (action) {
            case 'convert':
                await recordViewHeaderButtonClicks('Save', rec_view);
                await closeAlert();
                break;
            case 'cancel conversion of':
                await recordViewHeaderButtonClicks('Cancel', rec_view);
                break;
            default:
                throw new Error(`Invalid action ${action} selected!`);
        }
    }, {waitForApp: true}
);
