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
({
    className: 'businessrules',

    loadData: function (options) {
        this.br_uid = this.options.context.attributes.modelId;
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.off("businessRules:save:finish", null, this);
        this.context.on("businessRules:save:finish", this.saveBusinessRules, this);

        this.context.off("businessRules:save:save", null, this);
        this.context.on("businessRules:save:save", this.saveOnlyBusinessRules, this);

        this.context.off("businessRules:cancel:button", null, this);
        this.context.on("businessRules:cancel:button", this.cancelBusinessRules, this);

        this.myDefaultLayout = this.closestComponent('sidebar');
        app.routing.before('route', this.beforeRouteChange, this, true);
    },

    render: function () {
        app.view.View.prototype.render.call(this);
        renderBusinessRule(this.br_uid, this.myDefaultLayout);
    },

    saveBusinessRules: function() {
        saveBR(App.router.buildRoute("pmse_Business_Rules"));
    },

    saveOnlyBusinessRules: function() {
        saveBR();
    },

    cancelBusinessRules: function () {
        cancelAction(app.router);
    },

    beforeRouteChange: function () {
        var self = this,
            resp = false;
        if (decision_table.isDirty){
            var targetUrl = Backbone.history.getFragment();
            //Replace the url hash back to the current staying page
            app.router.navigate(targetUrl, {trigger: false, replace: true});
            app.alert.show('leave_confirmation', {
                level: 'confirmation',
                messages: app.lang.get('LBL_WARN_UNSAVED_CHANGES', this.module),
                onConfirm: function () {
                    var targetUrl = Backbone.history.getFragment();
                    app.router.navigate(targetUrl , {trigger: true, replace: true });
                    window.location.reload()
                },
                onCancel: $.noop
            });
            return false;
        }
        return true;
    }
})

