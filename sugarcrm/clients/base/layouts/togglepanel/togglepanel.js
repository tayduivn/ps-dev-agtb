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

    /**
     * @override
     * @param {Object} opts
     */
    initialize: function (opts) {
        this.toggleComponents = [];
        this.componentsList = {};
        this.processToggles();

        app.view.Layout.prototype.initialize.call(this, opts);

        this.showComponent(this.options.meta['default'], true);
    },

    /**
     * Get components from the metadata and declare toggles
     */
    processToggles: function () {
        // Enable toggles
        this.toggles = [];
        var temp = {};

        //Go through components and figure out which toggles we should add
        _.each(this.options.meta.components, function (component) {
            var toggle;
            if (component.view) {
                toggle = component.view;
            } else if (component.layout) {
                toggle = (_.isString(component.layout)) ? component.layout : component.layout.name;
            }


            var availableToggle = _.find(this.options.meta.availableToggles, function (curr) {
                return curr.name === toggle;
            }, this);
            if (toggle && availableToggle) {
                temp[toggle] = {toggle: toggle, title: availableToggle.label, 'class': availableToggle.icon };
            }
        }, this);

        if (this.options.meta.availableToggles) {
            // Sort the toggles by the order in the availableToggles list
            for (var i = 0; i < this.options.meta.availableToggles.length; i++) {
                var curr = this.options.meta.availableToggles[i];
                if (temp[curr.name]) {
                    this.toggles.push(temp[curr.name]);
                }
            }
        }

    },

    /**
     * @override
     * @private
     * @param {Component} component
     * @param {Object} def
     */
    _placeComponent: function (component, def) {

        if (def && def.targetEl) {
            if (def.position == 'prepend') {
                this.$(def.targetEl).prepend(component.el);
                return;
            } else {
                this.$(def.targetEl).append(component.el);
            }
        } else {
            // If we recognize the view, prevent it from rendering until it's
            // requested explicitly by the user.
            var toggleAvailable = _.isObject(_.find(this.options.meta.availableToggles, function (curr) {
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
        }
    },

    /**
     * Show a toggle
     * @param {Event} e
     */
    toggleView: function (e) {
        var $el = this.$(e.currentTarget);

        // Only toggle if we click on an inactive button.
        if (!$el.hasClass("active")) {
            var data = $el.data();
            this.showComponent(data.view);
            e.preventDefault();
        }
    },

    /**
     * Show a component and triggers "filterpanel:change"
     * @param {String} name
     * @param {Boolean} silent
     */
    showComponent: function (name, silent) {
        if (!name) return;
        if (this.componentsList[name]) {
            this.componentsList[name].render();
            this._components.push(this.componentsList[name]);
            this.$(".main-content").append(this.componentsList[name].el);
            this.componentsList[name] = null;
        }

        _.each(this.toggleComponents, function (comp) {
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
    _dispose: function () {
        _.each(this.componentsList, function (component) {
            if (component) {
                component.dispose();
            }
        });
        this.componentsList = {};
        this.toggleComponents = null;
        app.view.Layout.prototype._dispose.call(this);
    },

    /**
     * Show bootstrap tooltip
     * @param {Event} e
     */
    showTooltip: function (e) {
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
    hideTooltip: function (e) {
        this.$(e.currentTarget).tooltip('hide');
    }
})
