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

import {When} from '@sugarcrm/seedbed';
import RecordLayout from '../layouts/record-layout';

/**
 * Open the specified subpanel on a specified record view
 *
 * @example "I open the tasks subpanel on #Account_ARecord view"
 */
When(/^I open the (\S+) subpanel on (#\S+) view$/,
    async function(
        subpanelName: string,
        recordLayout: RecordLayout,
    ): Promise<void> {
        await recordLayout.SubpanelsLayout.openSubpanel(subpanelName);
    }, {waitForApp: true});

When(/^I (create_new|link_existing) record from (\S+) subpanel on (#\S+) view$/,
    async function(

        actionName: string,
        subpanelName: string,
        recordLayout: RecordLayout,
    ): Promise<void> {
        if(actionName === 'create_new')
            await recordLayout.SubpanelsLayout.createRecord(subpanelName);
        else if (actionName === 'link_existing')
            await recordLayout.SubpanelsLayout.linkRecord(subpanelName);
    }, {waitForApp: true});



