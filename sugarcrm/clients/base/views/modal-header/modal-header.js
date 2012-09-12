({
    events: {
        'click [data-dismiss="modal"]' : 'close'
    },
    close: function() {
        this.layout.hide();
    },
    setTitle: function(title) {
        this.title = title;
    },
    setButton: function(buttons) {
        this.buttons = buttons;
    }
})