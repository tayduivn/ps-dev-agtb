({
    extendsFrom: 'IntField',

    events: {
        'mouseenter span.editable': 'togglePencil',
        'mouseleave span.editable': 'togglePencil',
        'click span.editable': 'onClick',
        'blur span.edit input': 'onBlur',
        'keyup span.edit input': 'onKeypress'
    },

    inputSelector: 'span.edit input',

    errorMessage: '',

    _canEdit: true,

    initialize: function (options) {
        app.view.fields.IntField.prototype.initialize.call(this, options);
        this.checkIfCanEdit();
    },

    /**
     * Utility Method to check if we can edit again.
     */
    checkIfCanEdit: function() {
        var selectedUser = this.context.forecasts.get('selectedUser');
        if (!_.isUndefined(this.context.forecasts) && !_.isUndefined(this.context.forecasts.config)) {
            this._canEdit = _.isEqual(app.user.get('id'), selectedUser.id) && !_.contains(
                // join the two variable together from the config
                this.context.forecasts.config.get("sales_stage_won").concat(
                    this.context.forecasts.config.get("sales_stage_lost")
                ), this.model.get('sales_stage'));
        }
    },

    /**
     * Overwrite bindDomChange
     *
     * Since we need to do custom logic when a field changes, we have to overwrite this with out ever calling
     * the parent.
     *
     */
    bindDomChange: function () {
        if (!this.isEditable()) return;
        if (!(this.model instanceof Backbone.Model)) return;
        var self = this;
        var el = this.$el.find(this.fieldTag);
        el.on("change", function () {
            var value = self.parsePercentage(self.$el.find(self.inputSelector).val());
            if (self.isValid(value)) {
                self.model.set(self.name, self.unformat(value));
                self.$el.find(self.inputSelector).blur();
            } else {
                // will generate error styles here, for now log to console
                self.showErrors();
                self.$el.find(self.inputSelector).focus().select();
            }
        });
        // Focus doesn't always change when tabbing through inputs on IE9 (Bug54717)
        // This prevents change events from being fired appropriately on IE9
        if ($.browser.msie && el.is("input")) {
            el.on("input", function () {
                // Set focus on input element receiving user input
                el.focus();
            });
        }
    },

    /**
     * Toggles the pencil icon on and off depending on the mouse state
     *
     * @param evt
     */
    togglePencil: function (evt) {
        evt.preventDefault();
        if (!this.isEditable()) return;
        if(evt.type == 'mouseenter') {
            this.$el.find('.edit-icon').removeClass('hide');
            this.$el.find('.edit-icon').addClass('show');
        } else {
            this.$el.find('.edit-icon').removeClass('show');
            this.$el.find('.edit-icon').addClass('hide');
        }
    },

    /**
     * Switch the view to the Edit view if the field is editable and it's clicked on
     * @param evt
     */
    onClick : function(evt) {
        evt.preventDefault();
        if (!this.isEditable()) return;

        this.options.viewName = 'edit';
        this.render();

        // put the focus on the input
        this.$el.find(this.inputSelector).focus().select();
    },

    /**
     * Handle when return/enter and tab keys are pressed
     *
     * @param evt
     */
    onKeypress: function (evt) {
        if (evt.which == 27) {
            this.$el.find(this.inputSelector).val(this.value);
            this.$el.find(this.inputSelector).blur();
        } else if (evt.which == 13 || evt.which == 9) {
            // blur if value is unchanged
            var ogVal = this.value,
                ngVal = this.$el.find(this.inputSelector).val();
            if (_.isEqual(ogVal, ngVal)) {
                this.$el.find(this.inputSelector).blur();
            }
        }
    },

    /**
     * Blur event handler
     *
     * This forces the field to re-render as the DetailView
     *
     * @param evt
     */
    onBlur : function(evt) {
        evt.preventDefault();
        this.options.viewName = 'detail';
        this.render();
    },

    /**
     * Is the new value valid for this field.
     *
     * @param value
     * @return {Boolean}
     */
    isValid: function (value) {
        var regex = new RegExp("^[+-]?\\d+$"),
            hb = Handlebars.compile("{{str_format key module args}}"),
            args = [];

        text2 = hb({'key' : 'LBL_COMMITTED_THIS_MONTH', 'module' : 'Forecasts', 'args' : args});

        // always make sure that we have a string here, since match only works on strings
        if (_.isNull(value.toString().match(regex))) {
            var langString = app.lang.get(this.def.label,'Forecasts');
            args = [langString];
            this.errorMessage = hb({'key' : 'LBL_EDITABLE_INVALID', 'module' : 'Forecasts', 'args' : args});
            return false;
        }

        // we have digits, lets make sure it's int a valid range is one is specified
        if (!_.isUndefined(this.def.minValue) && !_.isUndefined(this.def.maxValue)) {
            // we have a min and max value
            if(value < this.def.minValue || value > this.def.maxValue) {
                args = [this.def.minValue, this.def.maxValue];
                this.errorMessage = hb({'key' : 'LBL_EDITABLE_INVALID_RANGE', 'module' : 'Forecasts', 'args' : args});
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
    isEditable: function () {
        return this._canEdit;
    },

    /**
     * Check the value to see if it's a percentage, if it is, then figure out the change.
     *
     * @param value
     * @return {*}
     */
    parsePercentage : function(value) {
        var orig = this.value;
        var parts = value.toString().match(/^([+-]?)(\d+(\.\d+)?)\%$/);
        if(parts) {
            // use original number to apply calculations
            value = app.math.mul(app.math.div(parts[2],100),orig);
            if(parts[1] == '+') {
                value = app.math.add(orig,value);
            } else if(parts[1] == '-') {
                value = app.math.sub(orig,value);
            }
            // we round to nearest integer for this field type
            value = app.math.round(value, 0);
        }
        return value;
    },

    /**
     * Method to show the error message
     */
    showErrors : function() {
        // attach error styles
        this.$el.find('.error-message').html(this.errorMessage);
        this.$el.find('.control-group').addClass('error');
        this.$el.find('.help-inline.editable-error').removeClass('hide').addClass('show');
    }

})