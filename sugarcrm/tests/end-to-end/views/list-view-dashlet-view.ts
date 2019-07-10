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
import ListViewDashletListView from './list-view-dashlet-list-view';

/**
 * Represents List View dashlet.
 *
 * @class ListViewDashlet
 * @extends DashletView
 */
export default class ListViewDashlet extends DashletView {

    public ListView: ListViewDashletListView;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.dashlet',
            table: {
                $: '.table.table-striped.dataTable tbody',
                tableRow: 'tr'
            }
        });

        this.ListView = this.createComponent<ListViewDashletListView>(ListViewDashletListView, {
            module: options.module,
        });
    }
}
