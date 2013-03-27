({
    events: {
        'click .widget-edit': 'editClicked',
        'click .widget-refresh' : 'refreshClicked',
        'click .widget-remove' : 'removeClicked',
        'click .minify' : 'toggleMinify'
    },
    cssIconDefault: 'icon-cog',
    cssIconRefresh: 'icon-refresh icon-spin',
    initialize: function(options) {
        this.index = options.meta.index;
        app.view.Layout.prototype.initialize.call(this, options);
        this.on("render", function() {
            this.model.trigger("applyDragAndDrop");
        }, this);
        this.context.on("dashboard:collapse:fire", this.collapse, this);
    },
    _addComponentsFromDef: function(components) {
        if(this.meta.empty)
            this.$el.html(app.template.empty(this));
        else
            this.$el.html(this.template(this));

        app.view.Layout.prototype._addComponentsFromDef.call(this, components);
    },
    _placeComponent: function(comp, def) {
        if(this.meta.empty) {
            this.$el.append(comp.el);
        } else if(this.meta.preview) {
            this.$el.addClass("preview-data");
            this.$(".widget-content:first").append(comp.el);
        } else {
            this.$(".widget-content:first").append(comp.el);
        }
    },
    setDashletMetadata: function(meta) {
        var metadata = this.model.get("metadata"),
            component = this.getCurrentComponent(metadata, this.index);
        _.each(meta, function(value, key){
            this[key] = value;
        }, component);

        this.model.set("metadata", JSON.parse(JSON.stringify(metadata)), {silent: true});
        this.model.trigger("change:layout");
        if(this.model.mode === 'view') {
            this.model.save(null, {
                //Show alerts for this request
                showAlerts: true
            });
        }

        return component;
    },
    getCurrentComponent: function(metadata, tracekey) {
        var position = tracekey.split(''),
            component = metadata.components;
        _.each(position, function(index){
            component = component.rows ? component.rows[index] : component[index];
        }, this);

        return component;
    },
    addDashlet: function(meta) {
        var component = this.setDashletMetadata(meta);

        this.meta.empty = false;
        this.meta.label = component.name;
        //clear previous dashlet
        this._components[0].dispose();
        this.removeComponent(0);

        if(component.context) {
            _.extend(component.context, {
                forceNew: true
            })
        }
        this._addComponentsFromDef([
            component
        ]);
        this.loadData();
        this.render();
    },
    removeDashlet: function() {
        var metadata = this.model.get("metadata"),
            component = this.getCurrentComponent(metadata, this.index);
        _.each(component, function(value, key){
            if(key!=='width') {
                delete component[key];
            }
        }, this);
        this.model.set("metadata", JSON.parse(JSON.stringify(metadata)), {silent: true});
        this.model.trigger("change:layout");
        if(this.model.mode === 'view') {
            this.model.save(null, {
                //Show alerts for this request
                showAlerts: true
            });
        }
        this.meta.empty = true;
        this._components[0].dispose();
        this.removeComponent(0);
        this._addComponentsFromDef([
            {
                view: 'dashlet-cell-empty'
            }
        ]);
        this.render();
    },
    addRow: function(columns) {
        this.layout.addRow(columns);
    },
    removeClicked: function(evt) {
        this.removeDashlet();
    },
    refreshClicked: function(evt) {
        var component = _.first(this._components),
            context = component.context,
            self = this;
        context._dataFetched = false;
        this.loadData();
    },
    editClicked: function(evt) {
        var component = _.first(this._components),
            self = this;
        app.drawer.open({
            layout: {
                name: 'dashletconfiguration',
                components: [
                    {
                        view: component.context.get("dashlet").type,
                        context: {
                            model: new app.Bean(),
                            module: component.context.get("module"),
                            dashlet: _.extend({
                                viewName: 'config'
                            },component.context.get("dashlet"))
                        }
                    }
                ]
            },
            context: {
                model: new app.Bean(),
                forceNew: true
            }
        }, function(model) {
            if(!model) return;
            self.addDashlet({
                name: model.get("name"),
                view: model.get("type"),
                context: {
                    module: model.get("module") || null,
                    model: model.get("model") || null,
                    modelId: model.get("modelId") || null,
                    dashlet: model.attributes
                }
            });
        });
    },
    toggleMinify: function(evt) {
        this.$(".minify > i").toggleClass("icon-chevron-down icon-chevron-up");
        this.$(".thumbnail").toggleClass("collapsed");
        this.$(".widget-content").toggleClass("hide");
    },
    collapse: function(collapsed) {
        this.$(".minify > i").toggleClass("icon-chevron-down", collapsed);
        this.$(".minify > i").toggleClass("icon-chevron-up", !collapsed);
        this.$(".thumbnail").toggleClass("collapsed", collapsed);
        this.$(".widget-content").toggleClass("hide", collapsed);
    },
    loadData: function(options) {
        this.$(".dropdown-toggle > i").removeClass(this.cssIconDefault).addClass(this.cssIconRefresh);
        var self = this;
        options = options || {};
        options.complete = function() {
            self.$(".dropdown-toggle > i")
                .removeClass(self.cssIconRefresh)
                .addClass(self.cssIconDefault);
        };
        app.view.Layout.prototype.loadData.call(this, options);
    },
    _dispose: function() {
        this.off("render");
        this.context.off("dashboard:collapse:fire", null, this);
        app.view.Layout.prototype._dispose.call(this);
    }
})
