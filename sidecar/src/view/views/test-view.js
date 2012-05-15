(function(app) {

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.TreeView
     * @alias SUGAR.App.layout.TreeView
     * @extends View.View
     */
    app.view.views.TestView = app.view.View.extend({
        render : function (){

            app.view.View.prototype.render.call(this);
        }

    });

})(SUGAR.App);