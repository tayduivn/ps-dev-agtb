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
    _renderSelf: function() {
        app.view.View.prototype._renderSelf.call(this);
        var fieldsArray = this.$("form[name=detail]").find("span[sfuuid]") || [];

        var that = this;
        if (fieldsArray.length > that.fieldsToDisplay) {
            _.each(fieldsArray, function(field, i) {
                if (i > that.fieldsToDisplay - 1) {
                    $(field).hide();
                }
            });
            this.$(".more").removeClass("hide");
        }
    },
    showMore: function() {
        var fieldsArray = this.$("form[name=detail]").find("span[sfuuid]") || [];
        _.each(fieldsArray, function(field, i) {
            $(field).show();
        });
        this.$(".more").addClass("hide");
        this.$(".less").removeClass("hide");
    },
    hideMore: function() {
        var fieldsArray = this.$("form[name=detail]").find("span[sfuuid]") || [];
        var that = this;
        _.each(fieldsArray, function(field, i) {
            if (i > that.fieldsToDisplay - 1) {
                $(field).hide();
            }
        });
        this.$(".less").addClass("hide");
        this.$(".more").removeClass("hide");
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
