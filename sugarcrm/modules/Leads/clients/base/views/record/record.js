({
    extendsFrom: 'RecordView',

    events:{
        'click [name=lead_convert_button]': 'initiateDrawer'
    },

    initialize: function(options) {
        _.bindAll(this);
        app.view.views.RecordView.prototype.initialize.call(this, options);
    },

    render: function() {
        app.view.views.RecordView.prototype.render.call(this);
        // Set the save button to show if the model has been edited.
        this.model.on("change", function() {
            this.setLeadButtonStates();
        }, this);
    },

     /**
     * Change the behavior of buttons depending on the state that they should be in
     */
    setLeadButtonStates: function() {
        var convertButton = this.$('.lead-convert'),
            convertedState = this.model.get('converted') == '1' ? true : false;

        if(convertedState) {
            convertButton.parent('li').hide();
        }
    },

    initiateDrawer: function() {
        this.layout.trigger("drawer:lead:convert:fire", {
            components: [{
                layout : "convert",
                context: {
                    module: 'Leads',
                    leadsModel: this.model
                }
            }]
        }, this);
    }
})
