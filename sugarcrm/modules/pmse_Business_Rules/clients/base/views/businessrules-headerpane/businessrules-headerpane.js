({
    extendsFrom: 'HeaderpaneView',

    events:{
        'click [name=project_finish_button]': 'initiateFinish',
        'click [name=project_save_button]': 'initiateSave',
        'click [name=project_cancel_button]': 'initiateCancel'
    },

    initiateFinish: function() {
        this.context.trigger('businessRules:save:finish');
    },
    initiateSave: function() {
        this.context.trigger('businessRules:save:save');
    },
    initiateCancel : function() {
        this.context.trigger('businessRules:cancel:button');
    }
})
