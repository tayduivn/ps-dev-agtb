({
    extendsFrom: 'TogglepanelLayout',
    // This is set to the filter that's currently being edited.
    editingFilter: null,

    /**
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        var moduleMeta = app.metadata.getModule(opts.module);
        this.disableActivityStreamToggle(moduleMeta);

        this.on("filterpanel:change:module", function(module, link) {
            this.currentModule = module;
            this.currentLink = link;
        }, this);

        this.on("filter:create:open", function(model) {
            this.$(".filter-options").show();
        }, this);

        this.on("filter:create:close", function(reinitialize, id) {
            if (reinitialize && !id) {
                this.trigger("filter:reinitialize");
            }
            this.$(".filter-options").hide();
        }, this);

        // This is required, for example, if we've disabled the subapanels panel so that app doesn't attempt to render later
        this.on('filterpanel:lastviewed:set', function(viewed) {
            this.toggleViewLastStateKey = this.toggleViewLastStateKey || app.user.lastState.key('toggle-view', this);
            var lastViewed = app.user.lastState.get(this.toggleViewLastStateKey);
            if (lastViewed !== viewed) {
                app.user.lastState.set(this.toggleViewLastStateKey, viewed);
            }
        }, this);

        app.view.invokeParent(this, {type: 'layout', name: 'togglepanel', method: 'initialize', args: [opts]});
        // Needed to initialize this.currentModule.
        var lastViewed = app.user.lastState.get(this.toggleViewLastStateKey);
        this.trigger('filterpanel:change:module', (moduleMeta.activityStreamEnabled && lastViewed === "activitystream") ? 'Activities' : this.module);
    },

    /**
     * Applies last filter
     * @param {Collection} collection the collection to retrieve the filter definition
     * @param {String} condition(optional) You can specify a condition in order to prevent applying filtering
     */
    applyLastFilter: function(collection, condition) {
        var triggerFilter = true;
        if (_.size(collection.origFilterDef)) {
            if (condition === 'favorite') {
                //Here we are verifying the filter applied contains $favorite otherwise we don't really care about
                //refreshing the listview
                triggerFilter = !_.isUndefined(_.find(collection.origFilterDef, function(value, key) {
                    return key === '$favorite' || (value && !_.isUndefined(value.$favorite));
                }));
            }
            if (triggerFilter) {
                var query = this.$('.search input.search-name').val();
                this.trigger('filter:apply', query, collection.origFilterDef);
            }
        }
    },

    /**
     * Disables the activity stream toggle if activity stream is not enabled for a module
     * @param {Collection} moduleMeta  The metadata for the component
     */
    disableActivityStreamToggle: function(moduleMeta) {
        if (!moduleMeta.activityStreamEnabled) {
            _.each(moduleMeta.availableToggles, function(module){
                if (module.name  === 'activitystream') {
                    module.disabled = true;
                    module.label = 'LBL_ACTIVITY_STREAM_DISABLED';
                }
            });
        }
    }
})
