({
    extendsFrom: 'IntField',

    events : {
        'mouseenter span.editable': 'onMouseEnter',
        'mouseleave span.editable': 'onMouseLeave',
        'click span.editable': 'onClick',
        'blur span.edit input' : 'onBlur'
    },

    onMouseEnter : function(evt) {
        if(!this.canEdit()) return;
        this.$el.find('i').addClass('icon-pencil icon-small');
    },

    onMouseLeave : function(evt) {
        if(!this.canEdit()) return;
        this.$el.find('i').removeClass('icon-pencil icon-small');
    },

    onClick: function(evt) {
        evt.preventDefault();
        if(!this.canEdit()) return;

        this.options.viewName = 'edit';
        this.render();

        // put the focus on the input
        this.$el.find('span.edit input').focus();
    },

    onBlur : function(evt) {
        evt.preventDefault();
        if(!this.canEdit()) return;
        this.options.viewName = 'detail';
        this.render();
    },

    /**
     * Can we edit this?
     *
     * @return {boolean}
     */
    canEdit : function() {
        return true;
    }
})