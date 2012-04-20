(function(app) {

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.TreeView
     * @alias SUGAR.App.layout.TreeView
     * @extends View.View
     */
    app.view.views.TreeView = app.view.View.extend({

        initialize : function(options){
            options.context = app.context.getContext();
            options.context.set({
                "module":"Users",
                "layout":this
            });
            options.context.loadData();
            app.view.View.prototype.initialize.call(this, options);
            return this;
        },

        getFields: function() {
            return ["first_name", "last_name", "title"];
        }

    });

})(SUGAR.App);