({
    events: {
        "click .toggle-actions button": "toggleView",
        'mouseenter [rel="tooltip"]': 'showTooltip',
        'mouseleave [rel="tooltip"]': 'hideTooltip'
    },

    availableToggles: {
        "activitystream": {icon: "icon-th-list", label: "LBL_ACTIVITY_STREAM"},
        "subpanel": {icon: "icon-table", label: "LBL_DATA_VIEW"},
        "list": {icon: "icon-table", label: "LBL_LISTVIEW"}
    },

    // This is set to the filter that's currently being edited.
    editingFilter: null,

    initialize: function(opts) {
        this.toggleComponents = [];
        this.componentsList = {};
        this.processMeta();
        this.processToggles();

        this.on("filter:change", function(module, link) {
            this.currentModule = module;
            this.currentLink = link;
        }, this);

        app.view.Layout.prototype.initialize.call(this, opts);

        this.on("filter:create:open", function(model) {
            this.$(".filter-options").show();
        }, this);

        this.on("filter:create:close", function(reinitialize, id) {
            if (reinitialize && !id) {
                this.trigger("filter:reinitialize");
            }
            this.$(".filter-options").hide();
        }, this);

        // Needed to initialize this.currentModule.
        this.trigger('filter:change', this.module);

        this.showComponent(this.options.meta['default'], true);
    },

    processMeta: function() {
        this.tabs = this.options.meta.tabs;
    },

    processToggles: function() {
        // Enable toggles
        this.toggles = [];

        _.each(this.options.meta.components, function(component) {
            var toggle;
            if(component.view) {
                toggle = component.view;
            } else if(component.layout) {
                toggle = (_.isString(component.layout)) ? component.layout : component.layout.name;
            }

            if (toggle && this.availableToggles[toggle]) {
                this.toggles.push({toggle: toggle, title: this.availableToggles[toggle].label, 'class': this.availableToggles[toggle].icon });
            }
        }, this);
    },

    _placeComponent: function(component, def) {
        // Specifically target the filter view to render on the toolbar.
        if (def.layout == "filter") {
            this.$(".filter").prepend(component.el);
            return;
        } else if(def.view == "filter-actions" || def.view == "filter-rows") {
            this.$(".filter-options").append(component.el);
        } else {
            // If we recognize the view, prevent it from rendering until it's
            // requested explicitly by the user.
            if (this.availableToggles[component.name]) {
                this.toggleComponents.push(component);
                this.componentsList[component.name] = component;
                this._components.splice(this._components.indexOf(component), 1);
            } else {
                // Safety check, just in case we've got a view that the layout
                // doesn't recognize.
                component.render();
                this.$(".main-content").append(component.el);
            }

            if (component.name == "activitystream") {
                this.activityContext = component.context;
            }
        }
    },

    toggleView: function(e) {
        var $el = this.$(e.currentTarget);

        // Only toggle if we click on an inactive button.
        if (!$el.hasClass("active")) {
            var data = $el.data();
            this.showComponent(data.view);
            e.preventDefault();
        }
    },

    showComponent: function(name, silent) {
        if (this.componentsList[name]) {
            this.componentsList[name].render();
            this._components.push(this.componentsList[name]);
            this.$(".main-content").append(this.componentsList[name].el);
            this.componentsList[name] = null;
        }

        _.each(this.toggleComponents, function(comp) {
            if (comp.name == name) {
                comp.show();
            } else {
                comp.hide();
            }
        }, this);
        this.trigger('filterpanel:change', name, silent);
    },

    _dispose: function() {
        _.each(this.componentsList, function(component) {
            if (component) {
                component.dispose();
            }
        });
        this.componentsList = {};
        this.toggleComponents = null;
        this.activityContext = null;
        app.view.Layout.prototype._dispose.call(this);
    },

    getActivityContext: function() {
        return this.activityContext;
    },

    showTooltip: function(e) {
        var $el = this.$(e.currentTarget);
        //Hotfix for the top left checkall (actionmenu) tooltip
        if ($el.hasClass('checkall')) {
            $el.tooltip({container: this.$el, trigger: 'manual'}).tooltip('show');
        } else {
            $el.tooltip('show');
        }
    },

    hideTooltip: function(e) {
        this.$(e.currentTarget).tooltip('hide');
    }
})
