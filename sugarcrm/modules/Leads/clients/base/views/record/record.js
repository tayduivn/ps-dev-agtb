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
            convertButton.closest('li').hide();
        }
    },

    initiateDrawer: function() {

        var model = app.data.createBean(this.model.module);
        model.copy(this.model);
        model.set('id', this.model.id);

        app.drawer.open({
            layout : "convert",
            context: {
                forceNew: true,
                module: 'Leads',
                leadsModel: model
            }
        });
    }
})
