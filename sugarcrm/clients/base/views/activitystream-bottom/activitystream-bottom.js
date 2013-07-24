({
    extendsFrom: 'ListBottomView',

    showMoreRecords: function(evt) {
        var self    = this,
            options = this.context.get('collectionOptions') || {};

        // Indicates records will be added to those already loaded in to view
        options.add = true;

        options.limit = this.limit;
        options.success = function() {
            if (!self.disposed) {
                self.render();
            }
        };
        this.collection.paginate(options);
    },

    bindDataChange: function() {
        if (this.collection) {
            this.listenTo(this.collection, "add reset sync", function() {
                this.render();
            });
        }
    }
})
