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
/**
 * @class View.Layouts.Home.ListLayout
 * @alias SUGAR.App.view.layouts.HomeListLayout
 * @extends View.DashboardLayout
 */
({
    extendsFrom: 'DashboardLayout',

    initialize: function(options) {
        var ctrlName = options.name || this.toString();
        app.logger.warn('The `' + ctrlName +
            '` has been deprecated since 7.8.0 and will be removed in 7.9.0. ' +
            'To extend from ' + this.extendsFrom + ' directly, remove the `' + ctrlName +
            '` controller and add the `type` property in the metadata file instead.');

        this._super('initialize', [options]);
    }
})
