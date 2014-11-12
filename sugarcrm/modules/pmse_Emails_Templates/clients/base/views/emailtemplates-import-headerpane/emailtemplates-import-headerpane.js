({
    extendsFrom: 'HeaderpaneView',
    events:{
        'click [name=emailtemplates_finish_button]': 'initiateFinish',
        'click [name=emailtemplates_cancel_button]': 'initiateCancel'
    },

    initiateFinish: function() {
        this.context.trigger('emailtemplates:import:finish');
    },

    initiateCancel : function() {
        //app.router.navigate(app.router.buildRoute('Home'), {trigger: true});
        app.router.goBack();
    }
})
