({
    extendsFrom: 'IntField',

    events : {
        'click span.editable': 'onClick',
        'blur span.edit input' : 'onBlur'
    },

    onClick: function(evt) {
        evt.preventDefault();

        this.options.viewName = 'edit';
        this.render();

        this.$el.find('span.edit input').focus();
    },

    onBlur : function(evt) {
        evt.preventDefault();

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