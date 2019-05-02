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

import * as _ from 'lodash';
import ListView from './list-view';
import ForecastsListItemView from './forecasts-list-item-view';

/**
 * Represents Forecasts module Record view.
 *
 * @class ForecastsListView
 * @extends ListView
 */
export default class ForecastsListView extends ListView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.flex-list-view-content',
        });
    }

    public createListItem (conditions) {

        if (!(conditions || conditions.id)) {
            return null;
        }

        let listViewItem = this.createComponent<ForecastsListItemView>(ForecastsListItemView, {
            id: conditions.id,
            module: this.module,
        });

        this.listItems.push(listViewItem as any);
        return listViewItem as any;
    }
}
