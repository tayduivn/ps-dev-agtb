({
    events: {
        'click .close' : 'close',
        'click a.previous-row' : 'previous',
        'click a.next-row' : 'next'
    },
    close: function(evt) {
        evt.preventDefault();
        this.layout.hide();
    },
    disablePrevious : function(enabled) {
        this.handleButtonToggle(this.$el.find('a.previous-row'), enabled);
    },
    disableNext : function(enabled) {
        this.handleButtonToggle(this.$el.find('a.next-row'), enabled);
    },
    handleButtonToggle : function(el, enabled) {
        if(enabled === true) {
            el.addClass('disabled')
        } else {
            el.removeClass('disabled');
        }
    },
    previous : function(evt) {
        evt.preventDefault();
        this.layout.trigger('previous', this.layout);
    },
    next : function(evt) {
        evt.preventDefault();
        this.layout.trigger('next', this.layout);
    },
    setTitle: function(title) {
        this.title = title;
    }
})