({
    className: "filtered tabbable tabs-left",

    // "Hide/Show" state per panel
    HIDE_SHOW_KEY: 'hide-show',
    HIDE_SHOW: {
        HIDE: 'hide',
        SHOW: 'show'
    },

    /**
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        app.view.Layout.prototype.initialize.call(this, opts);

        this.hideShowLastStateKey = app.user.lastState.key(this.HIDE_SHOW_KEY, this);

        this.on("panel:toggle", this.togglePanel, this);
        this.listenTo(this.collection, "reset", function() {
            var hideShowLastState = app.user.lastState.get(this.hideShowLastStateKey);
            if(_.isUndefined(hideShowLastState)) {
                this.togglePanel(this.collection.length > 0, false);
            } else {
                this.togglePanel(hideShowLastState === this.HIDE_SHOW.SHOW, false);
            }
        });
    },
    /**
     * Places layout component in the DOM.
     * @override
     * @param {Component} component
     */
    _placeComponent: function(component) {
        this.$(".subpanel").append(component.el);
        this._hideComponent(component, false);
    },
    /**
     * Toggles panel
     * @param {Boolean} show TRUE to show, FALSE to hide
     * @param {Boolean} saveState(optional) TRUE to save the current state
     */
    togglePanel: function(show, saveState) {
        this.$(".subpanel").toggleClass("out", !show);
        //check if there's second param then check it and save show/hide to user state
        if(arguments.length === 1 || saveState) {
            app.user.lastState.set(this.hideShowLastStateKey, show ? this.HIDE_SHOW.SHOW : this.HIDE_SHOW.HIDE);
        }
        _.each(this._components, function(component) {
            this._hideComponent(component, show);
        }, this);
    },
    /**
     * Show or hide component except `panel-top`.
     * @param {Component} component
     */
    _hideComponent: function(component, show) {
        if (component.name != "panel-top") {
            if (show) {
                component.show();
            } else {
                component.hide();
            }
        }
    }
})
