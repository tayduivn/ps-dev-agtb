/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.ListView
 * @alias SUGAR.App.layout.ListView
 * @extends View.View
 */
({
    fieldsToDisplay: app.config.fieldsToDisplay || 5,
    events: {
        'click .more': 'showMore',
        'click .less': 'hideMore'
    },
    render: function() {
        app.view.View.prototype.render.call(this);
        var fieldsArray = this.$el.find("form[name=detail]").find("span[sfuuid]") || [];

        var that = this;
        if (fieldsArray.length > that.fieldsToDisplay) {
            _.each(fieldsArray, function(field, i) {
                if (i > that.fieldsToDisplay - 1) {
                    $(field).hide();
                }
            });
            this.$el.find(".more").removeClass("hide");
        }
        return this;
    },
    showMore: function() {
        var fieldsArray = this.$el.find("form[name=detail]").find("span[sfuuid]") || [];
        _.each(fieldsArray, function(field, i) {
            $(field).show();
        });
        this.$el.find(".more").addClass("hide");
        this.$el.find(".less").removeClass("hide");
    },
    hideMore: function() {
        var fieldsArray = this.$el.find("form[name=detail]").find("span[sfuuid]") || [];
        var that = this;
        _.each(fieldsArray, function(field, i) {
            if (i > that.fieldsToDisplay - 1) {
                $(field).hide();
            }
        });
        this.$el.find(".less").addClass("hide");
        this.$el.find(".more").removeClass("hide");
    },
    bindDataChange: function() {
        if (this.model) {
            this.model.on("change", function() {
                    if (this.app.additionalComponents.subnav) {
                        this.app.additionalComponents.subnav.model = app.controller.context.attributes.model;
                        this.app.additionalComponents.subnav.meta = this.meta;
                    }
                }, this
            );
        }
    }

})
