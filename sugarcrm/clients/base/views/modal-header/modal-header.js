({
    events: {
        'click .close' : 'close'
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