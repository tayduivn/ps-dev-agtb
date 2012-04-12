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
        render: function() {
            console.log("in headerview render", this);
            this.currentModule = this.context.get('module');
            this.moduleList = this.moduleList || _.toArray(app.metadata.getModuleList());
            app.view.View.prototype.render.call(this);
        }
    });

})(SUGAR.App);