({
    extendsFrom: 'HeaderpaneView',
    events:{
        'click [name=lead_convert_finish_button].enabled': 'initiateFinish',
        'click [name=cancel_button]': 'initiateCancel'
    },
    initialize: function(options) {
        app.view.views.HeaderpaneView.prototype.initialize.call(this, options);
    },
    _renderHtml: function() {
        var leadsModel = this.context.get('leadsModel');
        var name = !_.isUndefined(leadsModel.get('name')) ? leadsModel.get('name') : leadsModel.get('first_name') + ' ' + leadsModel.get('last_name');
        this.title = app.lang.get("LBL_CONVERTLEAD_TITLE", this.module) + ': ' + name;
        app.view.views.HeaderpaneView.prototype._renderHtml.call(this);
    },
    initiateFinish: function() {
        this.context.trigger('lead:convert:finish');
    },

    initiateCancel : function() {
        this.context.trigger("drawer:hide");
        if (this.context.parent)
            this.context.parent.trigger("drawer:hide");
    }
})
