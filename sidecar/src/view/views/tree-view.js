(function(app) {

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.TreeView
     * @alias SUGAR.App.layout.TreeView
     * @extends View.View
     */
    app.view.views.TreeView = app.view.View.extend({

        render : function (){
            app.view.View.prototype.render.call(this);
            $("#demo1")
            // call `.jstree` with the options object
            .jstree({
                // the `plugins` array allows you to configure the active plugins on this instance
                "plugins" : ["themes","html_data","ui","crrm"],
                "themes" : {
                            "theme" : "classic",
                            "dots" : true,
                            "icons" : false
                        },
                // each plugin you have included can have its own config object
                "core" : { "initially_open" : [ "phtml_1" ] }
                // it makes sense to configure a plugin only if overriding the defaults
            });
        },

        getFields: function() {
            return ["name"];
        }

    });

})(SUGAR.App);