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
var Cukes = require('@sugarcrm/seedbed'),
    BaseLayout = Cukes.BaseLayout;

/**
 * Represents List page layout.
 *
 * @class SugarCukes.ListLayout
 * @extends Cukes.BaseLayout
 */
class ListLayout extends BaseLayout {

    constructor(options) {
        super(options);

        this.type = 'list';

        // TODO:
        // we are lucky that activitystream-layout has display: none,
        // but that isn't always true for list views,
        // since some might have only the class hide which does the same.
        this.selectors = {
            $: '.main-pane:not([style*="display: none"])'
        };

        this.addView('FilterView', 'FilterView', { module: options.module });
        this.addView('ListView', 'ListView', { module: options.module, default: true });
    }
}

module.exports = ListLayout;
