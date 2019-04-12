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
import PipelineView from '../views/pipeline-view';
import {closeAlert, closeWarning} from './general_bdd';


/**
 *  Select tab in Opportunities pipeline view
 *
 *  @example
 *  When I select pipelineByStage tab in #OpportunitiesPipelineView view
 */
When(/^I select (pipelineByTime|pipelineByStage) tab in (#\S+) view$/,
    async function (tabName: string, view: PipelineView): Promise<void> {

        await view.selectTab(tabName);

    }, {waitForApp: true});


/**
 *  Delete record in pipeline view
 *
 *  @example
 *  When I delete *Opp_1 in #OpportunitiesPipelineView
 */
When(/^I delete (\*[a-zA-Z](?:\w|\S)*) in (#\S+) view$/,
    async function (record: { id: string }, view: any) {
        let listItem = view.getListItem({id: record.id});
        await listItem.clickDeleteButton('delete');

        // Close Confirmation alert
        await closeWarning('Confirm');

        await closeAlert();
    }, {waitForApp: true});
