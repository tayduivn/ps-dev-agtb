({
    loadData: function(options) {
        this.context.set("fields", _.union(this.getFieldNames(), (this.context.get("fields") || [])));
        app.view.Layout.prototype.loadData.call(this, options, false);
    }
})
