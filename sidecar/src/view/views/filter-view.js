(function(app) {

    app.view.views.FilterView = app.view.View.extend({

        initialize : function(options){
            options.context = app.context.getContext();
            options.context.set({
                "module":"Forecasts",
                "layout":this
            });
            options.context.loadData();
            app.view.View.prototype.initialize.call(this, options);
            return this;
        }

        /*
        getFields: function() {
            return ["first_name", "last_name", "title"];
        }
        */

    });

})(SUGAR.App);
