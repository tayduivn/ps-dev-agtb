/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Layouts.Base.SpotlightLayout
 * @alias SUGAR.App.view.layouts.BaseSpotlightLayout
 * @extends View.Layout
 */
({
    initialize: function(options) {
        console.log('boo!');
        // need to check if we are logged in first.
        this._super('initialize', [options]);
    }
})
