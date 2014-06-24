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
 * @class View.Views.Base.AuditHeaderpaneView
 * @alias SUGAR.App.view.views.BaseAuditHeaderpaneView
 * @extends View.View
 */
({
    events: {
        'click a[name=close_button]': 'close'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.title = app.lang.get(this.meta.title, this.module);
    },

    close: function() {
        app.drawer.close();
    }
})
