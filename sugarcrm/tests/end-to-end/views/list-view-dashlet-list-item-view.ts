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
import BaseListItemView from './list-item-view';

/**
 * @class ListViewDashletListItemView
 * @extends ListItemView
 */
export default class ListViewDashletListItemView extends BaseListItemView {

    public id: string;
    public index: number;
    public current: boolean;

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: 'tr td:nth-child(1) a[href*="{{id}}"]',
        });

        this.id = options.id;
        this.index = options.index;
        this.current = !this.id && !this.index;
    }

    /**
     * Click item in the dashlet
     */
    public async clickListItem() {
        let selector = this.$('', {id: this.id});
        await this.driver.click(selector);
        await this.driver.waitForApp();
    }
}
