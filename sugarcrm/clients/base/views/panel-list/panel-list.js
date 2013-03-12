({
    extendsFrom: 'RecordlistView',

    contextEvents: {
        "list:editall:fire": "toggleEdit",
        "list:editrow:fire": "editClicked",
        "list:unlinkrow:fire": "unlinkClicked"
    },

    initialize: function(opts) {
        app.view.views.RecordlistView.prototype.initialize.call(this, opts);

        this.layout.bind("hide", this.toggleList, this);
    },

    unlinkClicked: function(model) {
        console.log("Unlinkined", model);
        var self = this;
        app.alert.show('unlink_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('NTC_UNLINK_CONFIRMATION'),
            onConfirm: function() {
                model.destroy({
                    relate: true,
                    success: function() {
                        self.render();
                    }
                });
            }
        });
    },

    toggleList: function(e) {
        (e) ? this.$el.show() : this.$el.hide();
    }
})
