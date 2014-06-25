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
    },

    _renderHtml: function() {
        var title = this.title || this.module;
        this.title = app.lang.get(title, this.module);

        app.view.View.prototype._renderHtml.call(this);
    }
})
