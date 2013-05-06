({
    events: {
        'click [data-event="list:follow:fire"]': 'toggleFollowing'
    },

    extendsFrom: 'RowactionField',

    initialize: function(options) {
        app.view.invoke(this, 'field', 'rowaction', 'initialize', {args:[options]});

        var self = this;
        this.context.on("follow:value:toggle", function(model) {
            if(self.model === model) {
                self.render();
            }
        }, this);
    },

    _render: function() {
        var mouseoverText, mouseoverClass, oldText, self = this;
        app.view.invoke(this, 'field', 'rowaction', '_render');

        this.$(".label").off();

        if (this.model.get("following")) {
            mouseoverText = app.lang.get("LBL_UNFOLLOW");
            mouseoverClass = "label-important";
        } else {
            mouseoverText = app.lang.get("LBL_FOLLOW");
            mouseoverClass = "label-success";
        }

        this.$(".label").on("mouseover", function() {
            oldText = $(this).text();
            $(this).text(mouseoverText).attr("class", "label").addClass(mouseoverClass);
        }).on("mouseout", function() {
            var kls = self.model.get("following") ? "label-success" : "";
            $(this).text(oldText).attr("class", "label").addClass(kls);
        });
    },

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
                    self.model.set("following", value);
                    if (self.collection) {
                        var realModel = self.collection.get(self.model.id);
                        if (realModel) {
                            realModel.set("following", value);
                            self.context.trigger("follow:value:toggle", realModel);
                        }
                    } else {
                        self.render();
                    }
                }
            });
        }
    }
})
