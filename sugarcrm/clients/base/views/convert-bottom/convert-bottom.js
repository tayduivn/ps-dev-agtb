({
    events: {
        'click [name=save_button]': 'saveModel'
    },

    saveModel: function(){
        //bubble event up;
        this.context.trigger('lead:convert');
    }

})