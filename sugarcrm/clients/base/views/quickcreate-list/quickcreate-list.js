({
    extendsFrom:'ListView',
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.off("quickcreate:list:toggled", null, this);
        this.context.on("quickcreate:list:toggled", this.listToggled, this);
    },
    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.ListView
     * @alias SUGAR.App.layout.ListView
     * @extends View.View
     */
    _renderHtml: function() {
        this.limit = this.context.get('limit') ? this.context.get('limit') : 2;
        app.view.View.prototype._renderHtml.call(this);
    },

    listToggled: function(isOpened) {
        debugger;
        this.listOpened = isOpened;

        this.$('.dataTables_filter').toggle();
    }
})

