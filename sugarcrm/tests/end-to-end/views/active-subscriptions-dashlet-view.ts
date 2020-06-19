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

import DashletView from './dashlet-view';
import ActiveSubscriptionsListView from './active-subscriptions-list-view';

/**
 * Represents Active Subscriptions dashlet
 *
 * @class ActiveSubscriptionsDashlet
 * @extends DashletView
 */
export default class ActiveSubscriptionsDashlet extends DashletView {

    protected ListView: ActiveSubscriptionsListView;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: `.dashlet-container[name=dashlet_${options.position}]`,
        });

        this.ListView = this.createComponent<ActiveSubscriptionsListView>(ActiveSubscriptionsListView, {
            module: options.module,
        });
    }
}
