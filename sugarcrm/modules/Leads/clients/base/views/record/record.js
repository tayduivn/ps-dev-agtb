({
    extendsFrom: 'RecordView',

    initialize: function(options) {
        this.events = _.extend({}, this.events, {'click [name=lead_convert_button]': 'initiateDrawer'});
        app.view.views.RecordView.prototype.initialize.call(this, options);
    },

    /**
     * Set the save button to show if the model has been edited.
     */
    bindDataChange: function() {
        app.view.views.RecordView.prototype.bindDataChange.call(this);
        this.model.on('change', this.setLeadButtonStates, this);
    },

     /**
     * Change the behavior of buttons depending on the state that they should be in
     */
    setLeadButtonStates: function() {
        var convertButton = this.$('[name=lead_convert_button]'),
            convertedState = this.model.get('converted') == '1' ? true : false;

        if(convertedState) {
            convertButton.parent('li').hide();
        }
    },

    initiateDrawer: function() {
        app.drawer.open({
            layout : "convert",
            context: {
                module: 'Leads',
                leadsModel: this.model
            }
        });
    }
})
