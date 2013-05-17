({
    extendsFrom: 'CreateView',

    initialize: function(options) {
        app.view.views.CreateView.prototype.initialize.call(this, options);
        this.enableDuplicateCheck = false;
    }
})
