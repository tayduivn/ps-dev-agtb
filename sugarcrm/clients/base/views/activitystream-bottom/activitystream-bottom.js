({
    extendsFrom: 'ListBottomView',

    initialize: function(opts) {
        opts.meta = _.extend({type: "list-bottom"}, opts.meta);
        app.view.views.ListBottomView.prototype.initialize.call(this, opts);
    },

    showMoreRecords: function(evt) {
        var self = this,
            options = this.context.get('collectionOptions') || {};

        // Indicates records will be added to those already loaded in to view
        options.add = true;

        options.limit = this.limit;
        options.success = function() {
            self.render();
        };
        this.collection.paginate(options);
    },

    bindDataChange: function() {
        if(this.collection) {
            this.collection.on("reset", function() {
                this.context._dataFetched = true;
                this.render();
            }, this);
        }
    }
})
