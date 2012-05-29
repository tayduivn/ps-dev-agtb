(function(app) {

    /**
     * Top view that displays a list of models pulled from the context's collection.
     * @class View.Views.ListViewTop
     * @alias SUGAR.App.layout.ListViewTop
     * @extends View.View
     */
    app.view.views.ListTopView = app.view.View.extend({
        events: {
            'click [rel=tooltip]': 'fixTooltip'
        },
        fixTooltip: function() {
            console.log("click on a tooltip");
            this.$(".tooltip").hide();
        }
    });

})(SUGAR.App);
