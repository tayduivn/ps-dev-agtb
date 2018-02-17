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

import {Then, stepsHelper} from '@sugarcrm/seedbed';
import RecordLayout from '../layouts/record-layout';

/**
 * Take a screenshot of either the subpanel header or footer on a specific
 * record view.
 *
 * @example "I verify that the tasks subpanel header on #Account_ARecord view still looks like accounts-task-subpanel-header"
 */
Then(/^I verify that the (\S+) subpanel (\S+) on (#\S+) view still looks like (.*)$/, async function(
    subpanelName: string,
    selector: string,
    recordLayout: RecordLayout,
    fileName: any
): Promise<void> {
    const subpanel = recordLayout.SubpanelsLayout.subpanels[subpanelName];
    await stepsHelper.verifyElementByImage(subpanel, fileName, selector);
});
