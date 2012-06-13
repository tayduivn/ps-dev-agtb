/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.FilterView
 * @alias SUGAR.App.layout.FilterView
 * @extends View.View
 */
({
    initialize:function (options) {
        _.bindAll(this); // Don't want to worry about keeping track of "this"
        // CSS className must be changed to avoid conflict with Bootstrap CSS.
        options.className = "progressBar";
        app.view.View.prototype.initialize.call(this, options);
    },

    bindDataChange: function() {
        if (this.model.isNew()) {
            this.model = this.context.model.forecasts.progress;
            this.model.on('change', this.render);
        }
    },
    
    render: function () {
        console.log("testing logs");
        _.extend(this, this.model.toJSON());
        app.view.View.prototype.render.call(this);
    }

})

//@ sourceURL=views/progress/progress.js
