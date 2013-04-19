({
    className: "block filtered tabs-left activitystream-layout",

    initialize: function(opts) {
        var self = this;
        this.opts = opts;
        this.renderedActivities = {};

        app.view.Layout.prototype.initialize.call(this, opts);

        var endpoint = function(method, model, options, callbacks) {
            var real_module = self.context.parent.get('module'),
                layoutType = self.context.parent.get('layout'),
                modelId = self.context.parent.get('modelId'),
                action = model.module, // equal to 'Activities'
                url;
            switch (layoutType) {
                case "activities":
                    url = app.api.buildURL(real_module, null, {}, options.params);
                    break;
                case "records":
                    url = app.api.buildURL(real_module, action, {}, options.params);
                    break;
                case "record":
                    url = app.api.buildURL(real_module, "activities", {id: modelId, link: true}, options.params);
                    break;
            }
            return app.api.call("read", url, null, callbacks);
        };

        this.context.set("collectionOptions", {
            endpoint: endpoint,
            success: function(collection) {
                collection.each(_.bind(self.renderPost, self));
            }
        });

        // Expose the dataTransfer object for drag and drop file uploads.
        jQuery.event.props.push('dataTransfer');
    },

    bindDataChange: function() {
        if (this.collection) {
            this.collection.on('add', this.renderPost, this);
            this.collection.on('reset', function() {
                _.each(this.renderedActivities, function(view) {
                    view.dispose();
                });
                this.renderedActivities = {};
                this.collection.each(function(post) {
                    this.renderPost(post);
                }, this);
            }, this);
        }

        if (this.context.parent) {
            var model = this.context.parent.get("model");
            this.listenTo(model, "sync", _.once(function() {
                // Only attach to the sync event after the inital sync is done.
                this.listenTo(model, "sync", function() {
                    var options = this.context.get("collectionOptions");
                    this.collection.fetch(options);
                });
            }));
        }
    },

    prependPost: function(model) {
        var view = this.renderPost(model);
        view.$el.parent().prepend(view.$el);
    },

    loadData: function(options) {
        // We want to ensure the data related to this activity loads before the
        // stream for a better user experience.
        var parentCol = this.context.parent.get("collection");
        if (parentCol.isEmpty()) {
            parentCol.once("sync", function(){
                this._load(options);
            }, this);
        } else {
            this._load(options);
        }
    },

    _load: function(options) {
        if (_.isUndefined(this.context.parent.get('layout')) || _.isUndefined(options)) {
            return;
        }
        options = _.extend(options, this.context.get('collectionOptions'));
        this.collection.fetch(options);
    },

    renderPost: function(model) {
        var view;
        if(_.has(this.renderedActivities, model.id)) {
            view = this.renderedActivities[model.id];
        } else {
            view = app.view.createView({
                context: this.context,
                name: "activitystream",
                module: this.module,
                layout: this,
                model: model
            });
            this.addComponent(view);
            this.renderedActivities[model.id] = view;
            view.render();
        }
        return view;
    },

    _placeComponent: function(component) {
        if(component.name === "activitystream") {
            this.$el.find(".activitystream-list").append(component.el);
        } else if(component.name === "activitystream-bottom") {
            this.$el.append(component.el);
            component.render();
        } else {
            this.$el.prepend(component.el);
        }
    },

    unbindData: function() {
        var model, collection;

        if (this.context.parent) {
            model = this.context.parent.get("model");
            collection = this.context.parent.get("collection");

            if (model) {
                model.off("change sync", null, this);
            }
            if (collection) {
                collection.off("sync", null, this);
            }
        }

        app.view.Layout.prototype.unbindData.call(this);
    }
})
