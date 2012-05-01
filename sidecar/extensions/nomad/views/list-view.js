(function(app) {

    app.view.views.ListView = app.view.View.extend({
        initialize: function(options) {
            // Mobile shows only the first two fields
            options.meta.panels[0].fields.length = 2;
            app.view.View.prototype.initialize.call(this, options);
        },

        bindDataChange: function() {
            if (this.collection) {
                this.collection.on("reset", this.render, this);
            }
        }

    });

})(SUGAR.App);