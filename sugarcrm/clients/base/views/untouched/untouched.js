({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.untouchedCollection = app.data.createBeanCollection(this.module);
        this.untouchedCollection.fetch({limit: 5, params: {untouched: 7, fields: {'Opportunities': ['name', 'last_activity_date']}, order_by: 'last_activity_date'}});
    },

    render: function() {
        if (this.untouchedCollection.isEmpty()) { return; }
        app.view.View.prototype.render.call(this);
    },

    bindDataChange: function() {
        this.untouchedCollection.on("reset", this.render, this);
    }
})