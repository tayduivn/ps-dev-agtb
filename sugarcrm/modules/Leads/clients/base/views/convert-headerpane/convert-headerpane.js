({
    extendsFrom: 'HeaderpaneView',
    events:{
        'click [name=lead_convert_finish_button].enabled': 'initiateFinish'
    },
    initialize: function(options) {
        app.view.views.HeaderpaneView.prototype.initialize.call(this, options);
    },
    _renderHtml: function() {
        this.title = app.lang.get("LBL_CONVERTLEAD_TITLE", this.module) + ': ' + this.model.get('name');
        app.view.views.HeaderpaneView.prototype._renderHtml.call(this);
    },
    initiateFinish: function() {
        this.context.trigger('lead:convert:finish');
    }
})
