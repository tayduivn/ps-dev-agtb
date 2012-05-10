(function(app) {

    app.view.views.HeaderView = app.view.View.extend({

        initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);

            var self = this;
            app.events.on("app:view:change", function() {
                self.render();
            });
        },

        render: function() {
            if (!app.api.isAuthenticated()) {
                this.$el.addClass("hide");
            }
            else {
                this.$el.removeClass("hide");
                this._renderLeftList();
                this._renderRightList();
                app.view.View.prototype.render.call(this);
            }
        },

        _renderLeftList: function() {
            // TODO: Implement
        },

        _renderRightList: function() {
            // TODO: Implement
        }

    });

})(SUGAR.App);