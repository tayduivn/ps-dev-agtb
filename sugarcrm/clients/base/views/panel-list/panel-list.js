({
    extendsFrom: 'RecordlistView',

    contextEvents: {
        "list:editall:fire": "toggleEdit",
        "list:editrow:fire": "editClicked",
        "list:unlinkrow:fire": "unlinkClicked"
    },

    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'recordlist', method: 'initialize', args: [options]});
        this.layout.on("hide", this.toggleList, this);
    },

    unlinkClicked: function(model) {
        var self = this;
        app.alert.show('unlink_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('NTC_UNLINK_CONFIRMATION'),
            onConfirm: function() {
                model.destroy({
                    //Show alerts for this request
                    showAlerts: true,
                    relate: true,
                    success: function() {
                        // We trigger reset after removing the model so that
                        // panel-top will re-render and update the count.
                        self.collection.remove(model).trigger('reset');
                        self.render();
                    }
                });
            }
        });
    },

    toggleList: function(e) {
        this.$el[e ? 'show' : 'hide']();
    }
})
