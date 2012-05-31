/**
 * View that displays the subnav bar
 * @class View.Views.SubnavView
 * @alias SUGAR.App.layout.SubnavView
 * @extends View.View
 */
({

    /**
     * Subnav will be visible for those layouts
     */
    showForLayouts: ["detail", "edit"],

    /**
     * Listens to the app:view:change event and show or hide the subnav
     */
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        var self = this;

        app.events.on("app:view:change", function(viewName) {

            if ($.inArray(viewName, self.showForLayouts) !== -1) {
                self.$el.find('span').each(function() {
                    $(this).empty();
                });
                self.show();
                self.bindDataChange();
            } else {
                self.hide();
                self.bindDataChange();
            }
        });
    },
    /**
     * Renders Subnav view
     */
    render: function() {
        if (!app.api.isAuthenticated()) return;
        app.view.View.prototype.render.call(this);
    },
    hide: function() {
        this.$el.hide();
    },
    show: function() {
        this.$el.show();
    },
    bindDataChange: function() {
        var self = this;
        if (app.controller.context.attributes.model) {
            app.controller.context.attributes.model.on("change", function() {
                    self.render();
                }, this
            );
        }
    }

})