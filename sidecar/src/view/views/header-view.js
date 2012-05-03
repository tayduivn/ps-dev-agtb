(function(app) {

    /**
     * View that displays header for current app
     * @class View.Views.HeaderView
     * @alias SUGAR.App.layout.HeaderView
     * @extends View.View
     */
    app.view.views.HeaderView = app.view.View.extend({
        /**
         * Renders Header view
         */
        initialize: function(options) {
            var self = this;
            app.events.on("app:sync:complete", function() {
                self.render();
            });
            app.view.View.prototype.initialize.call(this, options);
        },
        /**
         * Renders Header view
         */
        render: function() {
            if (!app.api.isAuthenticated()) return;
            this.setModuleInfo();
            app.view.View.prototype.render.call(this);
        },
        hide: function() {
            this.$el.hide();
        },
        show: function() {
            this.$el.show();
        },
        setModuleInfo: function() {
            var self = this;
            this.createListLabels = [];
            this.currentModule = this.context.get('module');
            this.moduleList = _.toArray(app.metadata.getModuleList());

            // TODO - would be nice if this were more dynamic 
            // Unfortunately, can't use: SUGAR.App.lang.getAppListStrings("moduleListSingular")
            // because it does not return KBDocuments, and Bugs => Bug Tracker is unhelpful, etc.
            this.createListLabels.push(SUGAR.App.lang.get('LBL_CREATE_BUG', 'Emails'));
            this.createListLabels.push(SUGAR.App.lang.get('LBL_CREATE_CASE', 'Emails'));
            this.createListLabels.push('Create KBDocument'); // TODO: When server provides this use instead
            this.createListLabels.push(SUGAR.App.lang.get('LBL_CREATE_LEAD', 'Emails'));
        }

    });

}(SUGAR.App));
