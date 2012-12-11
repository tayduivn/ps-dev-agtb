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
    
    inputSelector: this.inputSelector,

    errorCode : '',

    _canEdit : true,

    initialize : function(options) {

        app.view.fields.IntField.prototype.initialize.call(this, options);

        if(!_.isUndefined(this.context.forecasts) && !_.isUndefined(this.context.forecasts.config)) {
            this._canEdit = !_.contains(
                // join the two variable together from the config
                this.context.forecasts.config.get("sales_stage_won").concat(
                    this.context.forecasts.config.get("sales_stage_lost")
                ), this.model.get('sales_stage'));
        }
    },

    /**
     *
     * @param evt
     */
    onMouseEnter : function(evt) {
        if(!this.isEditable()) return;
        this.$el.find('i').addClass('icon-pencil icon-small');
    },

    /**
     *
     * @param evt
     */
    onMouseLeave : function(evt) {
        if(!this.isEditable()) return;
        this.$el.find('i').removeClass('icon-pencil icon-small');
    },

    /**
     *
     * @param evt
     */
    onClick : function(evt) {
        evt.preventDefault();
        if(!this.isEditable()) return;

        this.options.viewName = 'edit';
        this.render();

        // put the focus on the input
        this.$el.find(this.inputSelector).focus().select();
    },

    /**
     *
     * @param evt
     */
    onKeypress : function(evt) {
        // submit if pressed return or tab
        if(evt.which == 13 || evt.which == 9) {
            var ogVal = this.value,
                ngVal = this.$el.find(this.inputSelector).val();

            if(_.isEqual(ogVal, ngVal)) {
                this.$el.find(this.inputSelector).blur();
            }
        }
    },

    /**
     *
     * @param evt
     */
    onChange : function(evt) {
        evt.preventDefault();
        if(!this.isEditable()) return;
        var value = this.parsePercentage(this.$el.find(this.inputSelector).val());
        if(this.isValid(value)) {
            this.model.set(this.name, value);
            this.$el.find(this.inputSelector).blur();
        } else {
            // will generate error styles here, for now log to console
            console.log(app.lang.get(this.errorCode, "Forecasts"));
            this.$el.find(this.inputSelector).focus().select();
        }
    },

    /**
     *
     * @param evt
     */
    onBlur : function(evt) {
        // submit if unfocused
        evt.preventDefault();
        this.options.viewName = 'detail';
        this.render();
    },

    /**
     *
     * @param value
     * @return {Boolean}
     */
    isValid: function(value) {
        var regex = new RegExp("^\\+?\\d+$");
        // always make sure that we have a string here, since match only works on strings
        if(_.isNull(value.toString().match(regex))) {
            this.errorCode = 'LBL_CLICKTOEDIT_INVALID';
            return false;
        }

        // we have digits, lets make sure it's int a valid range is one is specified
        if(!_.isUndefined(this.def.minValue) && !_.isUndefined(this.def.maxValue)) {
            // we have a min and max value
            if(value < this.def.minValue || value > this.def.maxValue) {
                this.errorCode = 'LBL_CLICKTOEDIT_INVALID';
                return false;
            }
        }

        // the value passed all validation, return true
        return true;
    },

    /**
     * Can we edit this?
     *
     * @return {boolean}
     */
    isEditable : function() {
        return this._canEdit;
    },

    parsePercentage : function(value) {
        var orig = this.value;
        var parts = value.match(/^([+-])([\d\.]+?)\%$/);
        if(parts) {
            // use original number to apply calculations
            return Math.round(eval(orig + parts[1] + "(" + parts[2] / 100 + "*" + orig +")"));
        }

        return value
    }
})