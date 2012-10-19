({
    events: {
        'click .closeSubdetail': 'closePreview',
        'click [data-direction]': 'switchPreview'
    },

    initialize: function(options) {
        _.bindAll(this);

        app.view.View.prototype.initialize.call(this, options);
        this.fallbackFieldTemplate = "detail";
        this.context.on("togglePreview", this.togglePreview);
    },

    _render: function() {
        this.$el.parent().parent().addClass("container-fluid tab-content");
    },

    _renderHtml: function() {
        var fieldsArray;
        app.view.View.prototype._renderHtml.call(this);
    },

    togglePreview: function(model, collection) {
        var fieldsToDisplay = app.config.fieldsToDisplay || 5;
        if (model && collection) {
            // Create a corresponding Bean and Context for clicked search result. It
            // might be a Case, a Bug, etc...we don't know, so we build dynamically.
            this.model = app.data.createBean(model.get('_module'), model.toJSON());
            this.collection = app.data.createBeanCollection(model.get('_module'), collection.models);
            this.context.set({
                'model': this.model,
                'module': this.model.module,
                'collection': this.collection
            });

            // Get the corresponding detail view meta for said module
            this.meta = app.metadata.getView(this.model.module, 'detail') || {};
            // Clip meta panel fields to first N number of fields per the spec
            this.meta.panels[0].fields = _.first(this.meta.panels[0].fields, fieldsToDisplay);

            app.view.View.prototype._render.call(this);
        }
    },

    switchPreview: function(e, index, directionData, id, module) {
        var self = this,
            target = this.$(e.currentTarget),
            data = directionData || target.data(),
            currModule = module || this.model.get("_module"),
            currID = id || this.model.get("postId") || this.model.get("id"),
            currIndex = index || _.indexOf(this.collection.models, this.collection.get(currID));

        if( data.direction === "left" && (currID === _.first(this.collection.models).get("id")) ||
            data.direction === "right" && (currID === _.last(this.collection.models).get("id")) ) {
            return;
        }
        else {
            // We can increment/decrement
            data.direction === "left" ? currIndex -= 1 : currIndex += 1;

            // If there is no target_id, we don't have access to that activity record
            // The other condition ensures we're previewing from activity stream items.
            if( _.isUndefined(this.collection.models[currIndex].get("target_id")) &&
                this.collection.models[currIndex].get("activity_data") ) {

                currID = this.collection.models[currIndex].id;
                this.switchPreview(e, currIndex, data, currID, currModule);
            }
            else {
                var targetModule = this.collection.models[currIndex].get("target_module") || currModule;

                // Some activity stream items aren't previewable - e.g. no detail views
                // for "Meetings" module.
                if( _.isUndefined(app.metadata.getModule(targetModule).views.detail) ) {
                    currID = this.collection.models[currIndex].id;
                    this.switchPreview(e, currIndex, data, currID, currModule);
                }
                else {
                    this.closePreview();
                    this.model = app.data.createBean(targetModule);

                    if( _.isUndefined(this.collection.models[currIndex].get("target_id")) ) {
                        this.model.set("id", this.collection.models[currIndex].get("id"));
                    }
                    else
                    {
                        this.model.set("postId", this.collection.models[currIndex].get("id"));
                        this.model.set("id", this.collection.models[currIndex].get("target_id"));
                    }

                    this.model.fetch({
                        success: function(model) {
                            self.model.set("_module", targetModule);
                            self.togglePreview(model, self.collection);
                        }
                    });
                }
            }
        }
    },

    closePreview: function() {
        this.model.clear();
        this.$el.empty();
    }

})
