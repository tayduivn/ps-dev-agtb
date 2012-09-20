({
    extendsFrom:'AlertView',
    initialize: function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.on('quickcreate:alert', this.show, this);
    }
})