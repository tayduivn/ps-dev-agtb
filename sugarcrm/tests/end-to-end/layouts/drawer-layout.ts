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
 * Represents a Drawer page layout
 *
 * @class DrawerLayout
 * @extends RecordLayout
 */
export default class DrawerLayout extends RecordLayout {

    constructor(options) {

        super(options);

        this.selectors = this.mergeSelectors({
            $: '.drawer.active',
            'show more': '.show-hide-toggle .more',
            'show less': '.show-hide-toggle .less',
        });

        this.type = 'drawer';
    }
}
