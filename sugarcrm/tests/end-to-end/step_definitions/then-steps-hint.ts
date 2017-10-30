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

import {Then} from '@sugarcrm/seedbed';
import EnrichedView from "../views/hint/enriched-view";
import NewsView from "../views/hint/news-view";

/**
 * Check whether the hint logo is visible in Enriched view
 *
 * @example "I should see Logo on #<lastName>Preview.EnrichedView"
 */

Then(/^I should see Logo on (#\S+)$/,
    async(view:EnrichedView) => {
        let isView = await view.isVisibleView();
        if (!isView) {
            throw new Error('Expected to see "' + view.$() + '" view');
        }

        let isLogo = await view.checkLogo();
        if (!isLogo) {
            throw new Error('Expected to see Hint Logo on "' + view.$() + '" view');
        }
    });

/**
 * Check whether the news list is visible in News view
 *
 * @example "I should see News on #<lastName>Preview.NewsView"
 */

Then(/^I should see News on (#\S+)$/,
    async(view:NewsView) => {
        let isNewsListVisible = await view.checkNewsList();
        if (!isNewsListVisible) {
            throw new Error('Expected to see News List on "' + view.$() + '" view');
        }
    });
