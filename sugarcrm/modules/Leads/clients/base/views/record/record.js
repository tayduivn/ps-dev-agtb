({
    extendsFrom: 'RecordView',

    delegateButtonEvents: function() {
        this.context.on('button:lead_convert_button:click', this.initiateDrawer, this);
        this.context.on('button:manage_subscriptions:click', this.manageSubscriptionsClicked, this);
        app.view.views.RecordView.prototype.delegateButtonEvents.call(this);
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

    /**
     * Event to trigger the convert lead process for the lead
     */
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
    },

    /**
     * Event to trigger the Manage Subscriptions for the lead
     */
    manageSubscriptionsClicked: function() {
        var params = [
            {'name': 'return_module', value: this.module},
            {'name': 'return_id', value: this.model.id},
            {'name': 'action', value: 'Subscriptions'},
            {'name': 'module', value: 'Campaigns'}
        ];

        var route = '#bwc/index.php?' + $.param(params);
        app.router.navigate(route, {trigger: true});
    }
})
