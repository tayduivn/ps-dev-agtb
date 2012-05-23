(function(app) {

    app.view.views.FilterView = app.view.View.extend({

        initialize : function(options){
            app.view.View.prototype.initialize.call(this, options);
        }

        /*
        getFields: function() {
            return ["first_name", "last_name", "title"];
        }
        */

    });

})(SUGAR.App);
