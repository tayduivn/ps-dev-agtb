({
    /**
     * Layout for tabbing between filterable components.
     * Mostly to toggle between Activity Stream and list views
     *
     * @class BaseFilterpanelLayout
     * @extends Layout
     */
    events: {
        "click .toggle-actions button": "toggleView",
        'mouseenter [rel="tooltip"]': 'showTooltip',
        'mouseleave [rel="tooltip"]': 'hideTooltip'
    },

    // Static definition of available toggles, they are displayed in order listed here when shown
    availableToggles: [
        {
            name: "subpanels",
            icon: "icon-table",
            label: "LBL_DATA_VIEW"},
        {
            name: "list",
            icon: "icon-table",
            label: "LBL_LISTVIEW"
        },
        {
            name: "activitystream",
            icon: "icon-th-list",
            label: "LBL_ACTIVITY_STREAM"
        }
    ],

    // This is set to the filter that's currently being edited.
    editingFilter: null,

    /**
     * @override
     * @param {Object} opts
     */
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

        // get the last viewed layout
        this.toggleViewLastStateKey = app.user.lastState.key('toggle-view', this);
        var lastViewed = app.user.lastState.get(this.toggleViewLastStateKey);

        // show the first toggle if the last viewed state isn't set in the metadata
        if (_.isUndefined(lastViewed) && (this.toggles.length > 0)) {
            lastViewed = _.first(this.toggles).toggle;
        }

        // Needed to initialize this.currentModule.
        this.trigger('filter:change', (lastViewed === "activitystream") ? 'Activities' : this.module);

        // Toggle the appropriate button and layout for initial render
        this.$('[data-view="' + lastViewed + '"]').button('toggle');
        this.showComponent(lastViewed, true);
    },

    /**
     * Not necessary and needs to be refactored...
     */
    processMeta: function() {
        this.tabs = this.options.meta.tabs;
    },

    /**
     * Get components from the metadata and declare toggles
     */
    processToggles: function() {
        // Enable toggles
        this.toggles = [];
        var temp = {};

        //Go through components and figure out which toggles we should add
        _.each(this.options.meta.components, function(component) {
            var toggle;
            if(component.view) {
                toggle = component.view;
            } else if(component.layout) {
                toggle = (_.isString(component.layout)) ? component.layout : component.layout.name;
            }
            var availableToggle = _.find(this.availableToggles, function(curr){
                return curr.name === toggle;
            });
            if (toggle && availableToggle) {
                temp[toggle] = {toggle: toggle, title: availableToggle.label, 'class': availableToggle.icon };
            }
        }, this);

        // Sort the toggles by the order in the availableToggles list
        for(var i = 0; i < this.availableToggles.length; i++){
            var curr = this.availableToggles[i];
            if(temp[curr.name]){
                this.toggles.push(temp[curr.name]);
            }
        }
    },

    /**
     * @override
     * @private
     * @param {Component} component
     * @param {Object} def
     */
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
            var toggleAvailable = _.isObject(_.find(this.availableToggles, function(curr){
                return curr.name === component.name;
            }));
            if (toggleAvailable) {
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

    /**
     * Show a toggle
     * @param {Event} e
     */
    toggleView: function(e) {
        var $el = this.$(e.currentTarget);

        // Only toggle if we click on an inactive button.
        if (!$el.hasClass("active")) {
            var data = $el.data();
            this.showComponent(data.view);
            app.user.lastState.set(this.toggleViewLastStateKey, data.view);
            e.preventDefault();
        }
    },

    /**
     * Show a component and triggers "filterpanel:change"
     * @param {String} name
     * @param {Boolean} silent
     */
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

    /**
     * @override
     * @private
     */
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

    /**
     * Not necessary and needs to be refactored...
     * @returns {Context}
     */
    getActivityContext: function() {
        return this.activityContext;
    },

    /**
     * Show bootstrap tooltip
     * @param {Event} e
     */
    showTooltip: function(e) {
        var $el = this.$(e.currentTarget);
        //Hotfix for the top left checkall (actionmenu) tooltip
        if ($el.hasClass('checkall')) {
            $el.tooltip({container: this.$el, trigger: 'manual'}).tooltip('show');
        } else {
            $el.tooltip('show');
        }
    },

    /**
     * Hide bootstrap tooltip
     * @param {Event} e
     */
    hideTooltip: function(e) {
        this.$(e.currentTarget).tooltip('hide');
    }
})
