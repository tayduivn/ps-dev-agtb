({
    events: {
        'click [name=save_button]': 'save',
        'click [name=cancel_button]': 'cancel'
    },

    save: function() {
        this.context.trigger('quickcreate:save');
    },

    cancel: function() {
        this.context.parent.trigger('modal:close');
    }
})
