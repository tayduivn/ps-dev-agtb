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
    },
    showMore: function() {
        var fieldsArray = this.$("span[sfuuid]") || [];
        _.each(fieldsArray, function(field, i) {
            $(field).parent().parent().show();
        });
        this.$(".more").toggleClass("hide");
        this.$(".less").toggleClass("hide");
    },
    hideMore: function() {
        var fieldsArray = this.$("span[sfuuid]") || [];
        var that = this;
        _.each(fieldsArray, function(field, i) {
            if (i > that.fieldsToDisplay - 1) {
                $(field).parent().parent().hide();
            }
        });
        this.$(".less").toggleClass("hide");
        this.$(".more").toggleClass("hide");
    },
    bindDataChange: function() {
        if (this.model) {
            this.model.on("change", function() {
                if (this.context.get('subnavModel')) {
                    this.context.get('subnavModel').set({
                        'title': this.model.get('name'),
                        'meta': this.meta
                    });
                }
                this.$('.modelNotLoaded').hide();
                this.$('.modelLoaded').show();
            }, this);
        }
    }

})
