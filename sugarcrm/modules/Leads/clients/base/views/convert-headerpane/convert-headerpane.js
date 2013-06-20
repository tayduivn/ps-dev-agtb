({
    extendsFrom: 'HeaderpaneView',

    events:{
        'click [name=save_button]:not(".disabled")': 'initiateSave',
        'click [name=cancel_button]': 'initiateCancel'
    },

    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'headerpane', method: 'initialize', args: [options]});
        this.context.on('lead:convert-save:toggle', this.toggleSaveButton, this);
    },

    /**
     * Grab the lead's name and set the title to Convert: Name
     */
    _renderHtml: function() {
        var leadsModel = this.context.get('leadsModel');
        var name = !_.isUndefined(leadsModel.get('name')) ? leadsModel.get('name') : leadsModel.get('first_name') + ' ' + leadsModel.get('last_name');
        this.title = app.lang.get("LBL_CONVERTLEAD_TITLE", this.module) + ': ' + name;
        app.view.invokeParent(this, {type: 'view', name: 'headerpane', method: '_renderHtml'});
    },

    /**
     * When finish button is clicked, send this event down to the convert layout to wrap up
     */
    initiateSave: function() {
        this.context.trigger('lead:convert:save');
    },

    /**
     * When cancel clicked, hide the drawer
     */
    initiateCancel : function() {
        app.drawer.close();
    },

    /**
     * Enable/disable the Save button
     *
     * @param enable true to enable, false to disable
     */
    toggleSaveButton: function(enable) {
        this.$('[name=save_button]').toggleClass('disabled', !enable);
    }
})
