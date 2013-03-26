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

        var context = this.context.parent || this.context;
        app.view.Layout.prototype._addComponentsFromDef.call(this, components, context, context.get("module"));
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

        this.model.set("metadata", app.utils.deepCopy(metadata), {silent: true});
        this.model.trigger("change:layout");
        if(this.model.mode === 'view') {
            this.model.save(null, {
                //Show alerts for this request
                showAlerts: true
            });
        }

        this.meta.components[0] = component;
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
        var def = component.view || component.layout || component;

        this.meta.empty = false;
        this.meta.label =  def.label || def.name || "";
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
        this.model.set("metadata", app.utils.deepCopy(metadata), {silent: true});
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
        var self = this,
            meta = app.utils.deepCopy(this.meta.components[0]),
            type = meta.layout ? "layout" : "view";
        if(_.isString(meta[type]))
        {
            meta[type] = {name:meta[type], config:true};
        } else {
            meta[type].config = true;
        }

        app.drawer.open({
            layout: {
                name: 'dashletconfiguration',
                components: [meta]
            },
            context: {
                model: new app.Bean(),
                forceNew: true
            }
        }, function(model) {
            debugger;
            if(!model) return;
            var conf = model.toJSON(),
                dash = {
                    context: {
                        module: model.get("module") || meta.context ? meta.context.module : null
                    }
                };
            delete conf.config;
            dash[type] = conf;
            self.addDashlet(dash);
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
