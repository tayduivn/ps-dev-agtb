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

    initialize: function(opts) {
        _.bindAll(this);

        this.toggleComponents = [];
        this.processMeta();
        this.renderHtml();

        this.on("filter:change", function(module, link) {
            this.currentModule = module;
            this.currentLink = link;
        }, this);

        app.view.Layout.prototype.initialize.call(this, opts);
        this.showComponent(this.options.meta['default']);
    },

    processMeta: function() {
        this.tabs = this.options.meta.tabs;
    },

    renderHtml: function() {
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
        } else if(def.view == "filter-create") {
            this.$(".form-search-related").append(component.el);
        } else {
            // Check if hidden or not.
            if (this.availableToggles[component.name]) {
                this.toggleComponents.push(component);
            }

            this.$(".main-content").append(component.el);

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

    showComponent: function(name) {
        _.each(this.toggleComponents, function(comp) {
            if (comp.name == name) {
                comp.show();
            } else {
                comp.hide();
            }
        }, this);
        this.trigger('filterpanel:change', name);
    },

    getActivityContext: function() {
        return this.activityContext;
    },

    showTooltip: function(e) {
        this.$(e.currentTarget).tooltip("show");
    },

    hideTooltip: function(e) {
        this.$(e.currentTarget).tooltip("hide");
    }
})
