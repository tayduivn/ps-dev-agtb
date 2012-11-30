({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.context.on("headerpane:title",function(title){
            this.title = app.lang.get(title, this.module);
            this.render();
        }, this);
    },

    _renderHtml: function() {
        this.title = this.title || app.lang.get(this.module, this.module);
        app.view.View.prototype._renderHtml.call(this);
    }
})