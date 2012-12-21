({
    events: {
        'click .rowaction': 'rowActionSelect'
    },

    rowActionSelect: function(evt) {
        if ($(evt.currentTarget).data('event')) {
            this.view.context.trigger($(evt.currentTarget).data('event'), this.model);
        }
    }
})