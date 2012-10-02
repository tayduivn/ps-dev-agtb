({
    events: {
    },

    initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);
            this.collection = app.data.createBeanCollection(this.model.module);
            this.collection.fetch({limit:5, params:{untouched: 7, fields:{'Opportunities':['name', 'last_activity_date']}, order_by:'last_activity_date'}});

    },

    bindDataChange: function() {
        var self = this;
        if (this.collection) {
            this.collection.on("reset", self.render, this);
        }
    }
})