/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.ListView
 * @alias SUGAR.App.layout.ListView
 * @extends View.View
 */
({
    toggled: false,
    fieldsToDisplay: app.config.fieldsToDisplay || 5,
    events: {
        'click .more': 'toggleMoreLess',
        'click .less': 'toggleMoreLess'
    },
    _renderHtml: function() {
        app.view.View.prototype._renderHtml.call(this);
        var fieldsArray = this.$("span[sfuuid]") || [];

        if (fieldsArray.length > this.fieldsToDisplay) {
            _.each(fieldsArray, function(field, i) {
                if (i > this.fieldsToDisplay - 1) {
                    $(field).parent().parent().hide();
                }
            }, this);
            this.$(".more").removeClass("hide");
        }
        if (this.toggled) {
            this.toggleMoreLess();
        }
    },
    toggleMoreLess: function() {
        this.toggled = !this.toggled;
        var fieldsArray = this.$("span[sfuuid]") || [];
        var that = this;
        _.each(fieldsArray, function(field, i) {
            if (i > that.fieldsToDisplay - 1) {
                $(field).parent().parent().toggle();
            }
        });
        this.$(".less").toggleClass("hide");
        this.$(".more").toggleClass("hide");
    },
    bindDataChange: function() {
        if (this.model) {
            this.model.on("change", function() {
                this.render();
            }, this);
        }
    }

})
