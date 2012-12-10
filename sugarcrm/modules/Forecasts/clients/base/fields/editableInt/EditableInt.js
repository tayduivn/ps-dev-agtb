({
    extendsFrom : 'IntField',

    events : {
        'mouseenter span.editable': 'onMouseEnter',
        'mouseleave span.editable': 'onMouseLeave',
        'click span.editable': 'onClick',
        'blur span.edit input' : 'onBlur',
        'keypress span.edit input' : 'onKeypress',
        'change span.edit input' : 'onChange'
    },

    errorCode : '',

    /**
     *
     * @param evt
     */
    onMouseEnter : function(evt) {
        if(!this.canEdit()) return;
        this.$el.find('i').addClass('icon-pencil icon-small');
    },

    /**
     *
     * @param evt
     */
    onMouseLeave : function(evt) {
        if(!this.canEdit()) return;
        this.$el.find('i').removeClass('icon-pencil icon-small');
    },

    /**
     *
     * @param evt
     */
    onClick : function(evt) {
        evt.preventDefault();
        if(!this.canEdit()) return;

        this.options.viewName = 'edit';
        this.render();

        // put the focus on the input
        this.$el.find('span.edit input').focus().select();
    },

    /**
     *
     * @param evt
     */
    onKeypress : function(evt) {
        // submit if pressed return or tab
        if(evt.which == 13 || evt.which == 9)
        {
            this.onBlur(evt);
        }
    },

    /**
     *
     * @param evt
     */
    onChange : function(evt) {
        // submit if value changed
        this.onBlur(evt);
    },

    /**
     *
     * @param evt
     */
    onBlur : function(evt) {
        // submit if unfocused
        evt.preventDefault();
        if(!this.canEdit()) return;
        var value = this.$el.find('input').val();
        if(this.isValid(value)) {
            this.model.set(this.name, value);
            this.options.viewName = 'detail';
            this.render();
        } else {
            // will generate error styles here, for now log to console
            console.log(app.lang.get("LBL_CLICKTOEDIT_INVALID", "Forecasts"));
            this.$el.find('span.edit input').focus().select();
        }
    },

    /**
     *
     * @param value
     * @return {Boolean}
     */
    isValid: function(value) {
        var regex = new RegExp("^\\+?\\d+$");
        if(_.isNull(value.match(regex))) {
            this.errorCode = 'LBL_CLICKTOEDIT_INVALID';
            return false;
        }
        return true;
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