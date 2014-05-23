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
 * @class View.Views.Base.HeaderpaneView
 * @alias SUGAR.App.view.views.BaseHeaderpaneView
 * @extends View.View
 */
({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        if (this.meta && this.meta.title) {
            this.title = this.meta.title;
        }

        this.context.on("headerpane:title",function(title){
            this.title = title;
            if (!this.disposed) this.render();
        }, this);

        this.on('render', function() {
            //shortcut keys
            app.shortcuts.register(app.shortcuts.SCOPE.CREATE, ['esc','ctrl+alt+l'], function() {
                var $cancelButton = this.$('a[name=cancel_button]'),
                    $closeButton = this.$('a[name=close]');

                if ($cancelButton.is(':visible') && !$cancelButton.hasClass('disabled')) {
                    $cancelButton.click();
                } else if ($closeButton.is(':visible') && !$closeButton.hasClass('disabled')) {
                    $closeButton.click();
                }
            }, this);
            app.shortcuts.register(app.shortcuts.SCOPE.CREATE, ['ctrl+s','ctrl+alt+a'], function() {
                var $saveButton = this.$('a[name=save_button]');
                if ($saveButton.is(':visible') && !$saveButton.hasClass('disabled')) {
                    $saveButton.click();
                }
            }, this);
        }, this);
    },

    _renderHtml: function() {
        var title = this.title || this.module;
        this.title = app.lang.get(title, this.module);

        app.view.View.prototype._renderHtml.call(this);
    }
})
