({
    /**
     * Custom RecordlistView used within Subpanel layouts.
     *
     * @class View.SubpanelListView
     * @alias SUGAR.App.view.views.SubpanelListView
     * @extends View.RecordlistView
     */
    extendsFrom: 'RecordlistView',

    contextEvents: {
        "list:editall:fire": "toggleEdit",
        "list:editrow:fire": "editClicked",
        "list:unlinkrow:fire": "unlinkClicked"
    },

    /**
     * @override
     * @param options
     */
    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'recordlist', method: 'initialize', args: [options]});
        this.layout.on("hide", this.toggleList, this);
    },

    /**
     * When parent recordlist's initialize is invoked (above), this will get called
     * and populate our the list's meta with the proper view subpanel metadata.
     * @return {Object} The view metadata for this module's subpanel. Tries in following
     * order: "subpanel-for-accounts" (parent module), "subpanel-list", "record-list",
     * than, as last resort, we will return `{}`
     */
    _initializeMetadata: function() {
        return  _.extend({},
                app.metadata.getView(null, 'subpanel-list', true),
                app.metadata.getView(this.options.module, 'record-list', true),
                app.metadata.getView(this.options.module, 'subpanel-list', true)
            );
    },

    /**
     * Unlinks (removes) the selected model from the list view's collection
     * @param model
     */
    unlinkClicked: function(model) {
        var self = this;
        app.alert.show('unlink_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('NTC_UNLINK_CONFIRMATION'),
            onConfirm: function() {
                model.destroy({
                    //Show alerts for this request
                    showAlerts: true,
                    relate: true,
                    success: function() {
                        // We trigger reset after removing the model so that
                        // panel-top will re-render and update the count.
                        self.collection.remove(model).trigger('reset');
                        self.render();
                    }
                });
            }
        });
    },

    /**
     * Toggles the list visibility
     * @param {Boolean} show TRUE to show, FALSE to hide.
     */
    toggleList: function(show) {
        this.$el[show ? 'show' : 'hide']();
    }
})
