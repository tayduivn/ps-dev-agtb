({
    extendsFrom: 'ListView',

    initialize: function(opts) {
        app.view.views.ListView.prototype.initialize.call(this, opts);

        this.layout.bind("hide", this.toggleList, this);
    },

    toggleList: function(e) {
        (e) ? this.$el.show() : this.$el.hide();
    }
})
