({
    className: "filtered tabbable tabs-left",

    // "Hide/Show" state per panel
    HIDE_SHOW_KEY: 'hide-show',
    HIDE_SHOW: {
        HIDE: 'hide',
        SHOW: 'show'
    },

    initialize: function(opts) {
        app.view.Layout.prototype.initialize.call(this, opts);

        this.hideShowLastStateKey = app.user.lastState.key(this.HIDE_SHOW_KEY, this);

        this.on("hide", this.toggleChevron, this);
        this.listenTo(this.collection, "reset", function() {
            var hideShowLastState = app.user.lastState.get(this.hideShowLastStateKey);
            if(_.isUndefined(hideShowLastState)) {
                this.trigger('hide', (this.collection.length != 0), false);
            } else {
                this.trigger('hide', (hideShowLastState == this.HIDE_SHOW.SHOW));
            }
        });
    },

    _placeComponent: function(component) {
        this.$(".subpanel").append(component.el);
        if (component.name != "panel-top") {
            component.hide();
        }
    },

    toggleChevron: function(e) {
        this.$(".subpanel").toggleClass("out", e);
        //check if there's second param then check it and save show/hide to user state
        if(arguments.length === 1 || arguments[1]) {
            app.user.lastState.set(this.hideShowLastStateKey, e? this.HIDE_SHOW.SHOW : this.HIDE_SHOW.HIDE);
        }
    }
})
