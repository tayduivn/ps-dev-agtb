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
            this.currentModule = this.context.get('module');
            this.moduleList = _.toArray(app.metadata.getModuleList());
        }

    });

})(SUGAR.App);