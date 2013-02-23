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
                app.alert.show('unlink_list_record', {level: 'process', title: app.lang.getAppString('LBL_PORTAL_DELETING')});
                model.destroy({
                    relate: true,
                    success: function() {
                        app.alert.dismiss('unlink_list_record');
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
