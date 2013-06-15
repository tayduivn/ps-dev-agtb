({
    extendsFrom: 'ActivitystreamLayout',

    /**
     * Fetch and render activities when 'preview:render' event has been fired.
     */
    initialize: function(options) {
        app.view.invokeParent(this, {type: 'layout', name: 'activitystream', method: 'initialize', args:[options]});
        app.events.on("preview:render", this.fetchActivities, this);
    },

    /**
     * Fetch and render activities.
     *
     * @param model
     * @param collection
     * @param fetch
     * @param previewId
     */
    fetchActivities: function(model, collection, fetch, previewId) {
        var self = this;

        this.disposeAllActivities();
        this.collection.fetch({
            /*
             * Retrieve activities for the model that the user wants to preview
             */
            endpoint: function(method, collection, options, callbacks) {
                var url = app.api.buildURL(model.module, 'activities', {id: model.get('id'), link: true}, options.params);

                return app.api.call('read', url, null, callbacks);
            },
            /*
             * Render activity stream
             */
            success: function(collection) {
                collection.each(function(model) {
                    //TODO: if not data hide block-footer
                    self.renderPost(model, true);
                });
            }
        });
    },

    /**
     * Dispose all previously rendered activities
     */
    disposeAllActivities: function() {
        _.each(this.renderedActivities, function(view) {
            view.dispose();
        });
        this.renderedActivities = {};
    },

    /**
     * No need to set collectionOptions.
     */
    setCollectionOptions: function() {},

    /**
     * No need to expose data transfer object since this activity stream is readonly.
     */
    exposeDataTransfer: function() {},

    /**
     * Don't load activity stream until 'preview:render' event has been fired.
     */
    loadData: function() {},

    /**
     * No need to bind events here because this activity stream is readonly.
     */
    bindDataChange: function() {}
})
