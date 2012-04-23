(function(app) {

    /**
     * View that displays header for current app
     * @class View.Views.GridView
     * @alias SUGAR.App.layout.GridView
     * @extends View.View
     */
    app.view.views.GridView = app.view.View.extend({
        /**
         * Renders Header view
         */
        render: function() {
            app.view.View.prototype.render.call(this);
            var gTable = this.$el.find('#gridTable').dataTable();
            gTable.$('td').editable(function (value, settings) {
                console.log('value:' + value);
                return value;
            });
        }
    });

})(SUGAR.App);