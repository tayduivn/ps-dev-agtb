({
    events: {
        'click [data-event="list:follow:fire"]': 'toggleFollowing'
    },

    extendsFrom: 'RowactionField',

    initialize: function(options) {
        app.view.invokeParent(this, {type: 'field', name: 'rowaction', method: 'initialize', args:[options]});
        this.format();
    },
    bindDataChange: function() {
        if (this.model) {
            this.model.on("change:following", this.resetLabel, this);
        }
    },
    /**
     * Set current label and value since the follow button relates to the following
     *
     * @param value
     */
    format: function(value) {

        value = this.model.get("following");

        //For record view, the label should be diffent from other views
        //It also needs to have mouseover handlers for updating text
        if(this.tplName === "detail") {
            var label = value ? "LBL_FOLLOWING" : "LBL_FOLLOW";
            this.label = app.lang.get(label, this.module);
        } else {
            var label = value ? "LBL_UNFOLLOW" : "LBL_FOLLOW";
            this.label = app.lang.get(label, this.module);
        }
        return value;
    },
    /**
     * Reset label and triggers "show" handler to update parent controller dom
     */
    resetLabel: function() {
        this.render();
        //It should trigger the handler "show" to update parent controller
        //i.e. actiondropdown
        this.trigger("show");
    },
    unbindDom: function() {
        this.$("[data-hover=true]").off();
        app.view.invokeParent(this, {type: 'field', name: 'rowaction', method: 'unbindDom'});
    },
    _render: function() {
        var mouseoverText, mouseoverClass, self = this;
        app.view.invokeParent(this, {type: 'field', name: 'rowaction', method: '_render'});

        if(this.tplName !== "detail") {
            return;
        }

        if (this.model.get("following")) {
            mouseoverText = app.lang.get("LBL_UNFOLLOW");
            mouseoverClass = "label-important";
        } else {
            mouseoverText = app.lang.get("LBL_FOLLOW");
            mouseoverClass = "label-success";
        }

        this.$("[data-hover=true]").on("mouseover", function() {
            $(this).text(mouseoverText).attr("class", "label").addClass(mouseoverClass);
        }).on("mouseout", function() {
            var kls = self.model.get("following") ? "label-success" : "";
            $(this).text(self.label).attr("class", "label").addClass(kls);
        });
    },
    /**
     * Call REST API for subscribe and unsubscribe
     *
     * @param Window.Event
     */
    toggleFollowing: function(e) {
        var isFollowing = this.model.get("following");

        if(!_.isUndefined(isFollowing)) {
            var guid = this.model.get("id"),
                module = this.context.get("module"),
                method, action, value;

            if(isFollowing) {
                method = "delete";
                action = "unsubscribe";
                value = false;
            }
            else {
                method = "create";
                action = "subscribe";
                value = true;
            }
            var self = this,
                url = app.api.buildURL(module, action, {id: guid});

            app.api.call(method, url, null, {
                success: function() {
                    if(self.disposed) {
                        return;
                    }
                    self.model.set("following", value);
                }
            });
        }
    }
})
