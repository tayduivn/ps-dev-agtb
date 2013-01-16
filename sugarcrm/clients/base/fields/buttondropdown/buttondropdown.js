({
    events: {
        'click a[name]': 'handleActions'
    },

    handleActions: function(event) {
        event.preventDefault();
        this.context.trigger('button:' + $(event.currentTarget).prop('name') + ':click');
    }
})