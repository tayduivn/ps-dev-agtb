/**
 * Application configuration.
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
        module: "Forecasts",
        layout: "main"
    });

    app.router.setLayout("hierarchy", {
        module: "Forecasts",
        layout: "hierarchy"
    });

    app.router.setLayout("filter", {
        module: "Forecasts",
        layout: "filter"
    });


    app.router.setLayout("navigation", {
        module: "Forecasts",
        layout: "navigation"
    });

    app.router.setLayout("opportunities_list", {
        module: "Forecasts",
        layout: "opportunities_list"
    });

    app.router.setLayout("list", {
        module: "Opportunities",
        layout: "list"
    });

    app.router.setLayout("opportunities", {
        module: "Forecasts",
        layout: "opportunities"
    });

    /*
    app.view.views.FilterView = app.view.View.extend({
        //Override init to prevent checking for metadata, ect.
        initialize : function(){
            this.data = app.data.createBeanCollection('Forecasts');
            this.data.fetch({success: function(data) { console.log(data);} });
        },
        render : function() {
            this.$el.html("Filter Widget HERE");
        },
        fields : function() {
            return ['forecast_type'];
        }
    });
    */

    app.view.views.ExampleView = app.view.View.extend({
        //Override init to prevent checking for metadata, ect.
        init : function(){

        },
        render : function() {
            console.log("rendering an example view");

            this.$el.html("<a href='#Forecasts/layout/main'>Go to Main Layout</a>");

        }
    });

    app.view.views.NavigationView = app.view.View.extend({
        //Override init to prevent checking for metadata, ect.
        initialize : function(){
            //this.data = app.data.createBeanCollection('Users');
            //this.data.fetch({success: function(data) { console.log(data);} });
        },




        render : function() {


            this.$el.html("<a href='#Forecasts/layout/opportunities'>Opportunities</a> | " +
                          "<a href='#Forecasts/layout/quotas'>Quotas</a>"
            );


        }
    });

/*
    app.view.views.TreeView = app.view.View.extend({
        //Override init to prevent checking for metadata, ect.
        initialize : function(){
            this.data = app.data.createBeanCollection('Users');
            this.data.fetch({success: function(data) { console.log(data);} });
        },
        render : function() {
            var result = app.view.View.prototype.render.call(this);
            console.log(result);
            return result;
        }
    });
*/

    app.view.views.OpportunitiesView = app.view.View.extend({
        //Override init to prevent checking for metadata, ect.
        initialize : function(){
            this.data = app.data.createBeanCollection('Opportunities');
            this.data.fetch({success: function(data) { console.log(data);} });
        },
        render : function() {
            this.$el.html("<a href='#Forecasts/layout/opportunities'>Opportunities</a> | " +
                          "<a href='#Forecasts/layout/quotas'>Quotas</a>"
            );

        }
    });

    app.view.views.Opportunities_listView = app.view.View.extend({
        //Override init to prevent checking for metadata, ect.
        initialize : function(){
            this.data = app.data.createBeanCollection('Opportunities');
            this.data.fetch({success: function(data) { console.log('-------------'); console.log(data);} });
        },

        render : function() {
            //console.log("Hellow!");
            //debugger;
            //template();
            SUGAR.App.controller.context.state.layout.render();
            //this.$el.html("Opportunities List Yo");
        }
    });

})(SUGAR.App);
