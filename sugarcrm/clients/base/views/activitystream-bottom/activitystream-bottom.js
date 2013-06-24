({
    extendsFrom: 'ListBottomView',
    initialize: function (options) {
        app.view.invokeParent(this, {type: 'view', name: 'list-bottom', method: 'initialize', args:[options]});
        this.template = app.template.getView('list-bottom');
    },
    showMoreRecords: function(evt) {
        var self    = this,
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
        var func = function() {
            this.context._dataFetched = true;
            this.render();
        };
        if (this.collection) {
            this.listenTo(this.collection, "reset", func);
            this.listenTo(this.collection, "add", func);
        }
    }
})
