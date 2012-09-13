({
    events: {
        'click [name=save_button]': 'saveModel',
    },

    bindDataChange: function() {
        var self = this;
        this.model.on('change', function() {
            self.render();
        });
    },

    saveModel: function() {
        this.context.trigger('lead:convert');
    }

})