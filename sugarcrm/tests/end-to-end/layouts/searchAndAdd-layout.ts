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

import {BaseView, seedbed} from '@sugarcrm/seedbed';
import HeaderView from '../views/record-header-view';
import ListLayout from './list-layout';


/**
 * Represents searchAndAdd layout.
 *
 * @class SearchAndAddLayout
 * @extends BaseView
 */
export default class SearchAndAddLayout extends ListLayout {

    public HeaderView: HeaderView;

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.drawer.active',
            showMoreBtn: '.show-hide-toggle .btn.more',
            showLessBtn: '.show-hide-toggle .btn.less',
        });

        this.type = 'drawer';

        this.HeaderView = this.createComponent<HeaderView>(HeaderView, {
            module: options.module,
        });

    }

    public async showMore() {
        if (await this.driver.isVisible(this.$('showMoreBtn'))) {
            await this.driver.click(this.$('showMoreBtn'));
        }
    }
    public async showLess() {
        if (await this.driver.isVisible(this.$('showLessBtn'))) {
            await this.driver.click(this.$('showLessBtn'));
        }
    }
}
