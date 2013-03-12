({
    className: "block filtered tabs-left activitystream-layout",

    initialize: function(opts) {
        var self = this;
        this.opts = opts;
        this.renderedActivities = {};

        app.view.Layout.prototype.initialize.call(this, opts);

        // Expose the dataTransfer object for drag and drop file uploads.
        jQuery.event.props.push('dataTransfer');
    },

    bindDataChange: function() {
        var self = this;

        if (this.collection) {
            this.collection.on('add', this.renderPost, this);
            this.collection.on('reset', function() {
                _.each(this.renderedActivities, function(view) {
                    view._dispose();
                });
                this.renderedActivities = {};
                this.collection.each(function(post) {
                    this.renderPost(post);
                }, this);
            }, this);
        }

        if (this.context.parent) {
            var model = this.context.parent.get("model");
            model.on("change", _.once(function() {
                model.on("sync", function() {
                    var options = self.collection.get("collectionOptions");
                    this.fetch(options);
                }, this.collection);
            }));
        }
    },

    prependPost: function(model) {
        var view = this.renderPost(model);
        view.$el.parent().prepend(view.$el);
    },

    loadData: function(options) {
        //We want to ensure the data related to this activity loads before the stream for UX purposes.
        var parentCol = this.context.parent.get("collection");
        if (parentCol.isEmpty()) {
            parentCol.once("sync", function(){this._load(options)}, this);
        } else {
            this._load(options);
        }
    },

    _load: function(options) {
        var self = this,
            endpoint = function(method, model, options, callbacks) {
                var real_module = self.opts.context.parent.get('module'),
                    modelId = self.opts.context.parent.get('modelId'), url;
                if (real_module !== "Activities") {
                    url = app.api.buildURL(real_module, model.module, {id: modelId}, options.params);
                } else {
                    url = app.api.buildURL(model.module, null, {}, options.params);
                }
                return app.api.call("read", url, null, callbacks);
            };
        options = _.extend({
            endpoint: endpoint,
            success: function(collection) {
                collection.each(_.bind(self.renderPost, self));
            }
        }, options);
        this.context.set("collectionOptions", options);
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
    }
})
