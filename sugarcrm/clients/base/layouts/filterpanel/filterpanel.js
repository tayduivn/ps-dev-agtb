({
    events: {
        "click .toolbar-btns a": "toggleView"
    },

    availableToggles: {
        "activitystream" : "icon-th-list",
        "timeline" : "icon-time",
        "calendar" : "icon-calendar",
        "list" : "icon-table"
    },

    initialize: function(opts) {
        _.bindAll(this);

        this.first = true;
        this.processMeta();
        this.template = app.template.get("l.filterpanel");
        this.renderHtml();

        app.view.Layout.prototype.initialize.call(this, opts);
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
                this.toggles.push({toggle: toggle, class: this.availableToggles[toggle]});
            }
        }, this);

        this.$el.html(this.template(this));
    },

    _placeComponent: function(component) {
        this.$el.append(component.el);

        if (this.first) {
            this.first = false;
        } else {
            component.hide();
        }
    },

    toggleView: function(e) {
        var data = this.$(e.currentTarget).data();

        this.showComponent(data.view);
        e.preventDefault();
    },

    showComponent: function(name) {
        _.each(this._components, function(comp) {
            if (comp.name == name) {
                comp.show();
            } else {
                comp.hide();
            }
        }, this);
    }
})