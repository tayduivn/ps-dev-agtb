({
    extendsFrom: 'HeaderpaneView',

    events:{
        'click [name=lead_convert_finish_button].enabled': 'initiateFinish',
        'click [name=cancel_button]': 'initiateCancel'
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
    initiateFinish: function() {
        this.context.trigger('lead:convert:finish');
    },

    /**
     * When cancel clicked, hide the drawer
     */
    initiateCancel : function() {
        app.drawer.close();
    }
})
