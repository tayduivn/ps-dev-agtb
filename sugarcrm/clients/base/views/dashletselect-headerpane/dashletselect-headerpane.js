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
 * @class View.Views.Base.DashletselectHeaderpaneView
 * @alias SUGAR.App.view.views.BaseDashletselectHeaderpaneView
 * @extends View.View
 */
({
    events: {
        "click a[name=cancel_button]": "close"
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.title = app.lang.get(this.meta.title, this.module);

        //shortcut keys
        app.shortcuts.register(app.shortcuts.SCOPE.CREATE, ['esc','ctrl+alt+l'], function() {
            var $cancelButton = this.$('a[name=cancel_button]');
            if ($cancelButton.is(':visible') && !$cancelButton.hasClass('disabled')) {
                $cancelButton.click();
            }
        }, this);
    },

    close: function() {
        app.drawer.close();
    }
})
