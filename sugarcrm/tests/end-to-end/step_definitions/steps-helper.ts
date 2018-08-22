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
import {seedbed} from '@sugarcrm/seedbed';
import * as request from 'request-promise';

export const updateOpportunityConfig = async (data) => {

    let config = {
        "opps_view_by": data['opps_view_by'],
        "opps_closedate_rollup": data['opps_close_date']
    };

    let reqOptions = seedbed.api._buildOptions(
        'POST',
        'Opportunities/config',
        false,
        config);

    await request(reqOptions);
};
