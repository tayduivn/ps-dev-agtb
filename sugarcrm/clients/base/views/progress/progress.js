(function(app) {

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.FilterView
     * @alias SUGAR.App.layout.FilterView
     * @extends View.View
     */
    app.view.views.ProgressView = app.view.View.extend({

        /**
         * Initialize the View
         *
         * @constructor
         * @param {Object} options
         */
        initialize: function(options){
            options.className = "progressBar";
            app.view.View.prototype.initialize.call(this, options);
        },

        /**
         * Start the rendering of the JS Tree
         */
        render : function (){
            app.view.View.prototype.render.call(this);
        }

    });

})(SUGAR.App);