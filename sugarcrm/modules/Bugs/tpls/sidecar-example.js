/**
 * Sidecar example for using within sugar - Application configuration.
 * @class config
 * @alias SUGAR.App.config
 * @singleton
 */
(function(app) {
    //Disable sync for now
    //app.sync = function(){return false};
    /*app.router.routes = {
        "" :

    app.router.index = function() {
        this.controller.loadView();
    };
    }*/

    app.router.setLayout("index", {
        module: "Bugs",
        layout: "main",
        create: true
    });

    app.router.setLayout("test", {
        module: "Bugs",
        layout: "test",
        create: true
    });



    app.view.views.ExampleView = app.view.View.extend({
        //Override init to prevent checking for metadata, ect.
        init : function(){

        },
        render : function() {
            console.log("rendering an example view");
            this.$el.html(
                "<a href='#Bugs/layout/list'>Go to List!</a><br/>" +
                "<a href='#Bugs/layout/test'>Go to Example!</a><br/>" +
                "<a href='#Bugs/layout/complex'>Go to Complex Layout</a>");
        }
    });

})(SUGAR.App);
