
({
    extendsFrom: 'HeaderpaneView',
    events:{
        'click [name=businessrules_finish_button]': 'initiateFinish',
        'click [name=businessrules_cancel_button]': 'initiateCancel'
    },

    initiateFinish: function() {
        this.context.trigger('businessrules:import:finish');
    },

    initiateCancel : function() {
        //app.router.navigate(app.router.buildRoute('Home'), {trigger: true});
        app.router.goBack();
    }

})
