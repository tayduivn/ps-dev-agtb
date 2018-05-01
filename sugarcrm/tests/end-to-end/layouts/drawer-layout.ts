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
import RecordLayout from './record-layout';

/**
 * Represents a Detail/Record page layout.
 *
 * @class RecordLayout
 * @extends BaseView
 */
export default class DrawerLayout extends RecordLayout {

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: '.drawer.active',
            'show more': '.show-hide-toggle .btn.more',
            'show less': '.show-hide-toggle .btn.less',

        });

        this.type = 'drawer';
    }
    public async showMore(btnName) {
        if (await this.driver.isVisible(this.$(btnName))) {
            await this.driver.click(this.$(btnName));
        }
    }
}
