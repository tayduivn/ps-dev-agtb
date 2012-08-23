({
    events: {
        'click [name=save_button]': 'saveModel'
    },

    saveModel: function(){
        //bubble event up;
        debugger;
        this.context.trigger('lead:convert');
    }

})