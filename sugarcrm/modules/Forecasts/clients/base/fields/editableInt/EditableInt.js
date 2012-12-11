({
    extendsFrom: 'IntField',

    events: {
        'mouseenter span.editable': 'togglePencil',
        'mouseleave span.editable': 'togglePencil',
        'click span.editable': 'onClick',
        'blur span.edit input': 'onBlur',
        'keypress span.edit input': 'onKeypress'
    },

    inputSelector: 'span.edit input',

    errorCode: '',

    _canEdit: true,

    initialize: function (options) {

        app.view.fields.IntField.prototype.initialize.call(this, options);

        if (!_.isUndefined(this.context.forecasts) && !_.isUndefined(this.context.forecasts.config)) {
            this._canEdit = !_.contains(
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
                console.log(app.lang.get(self.errorCode, "Forecasts"));
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
        this.$el.find('i').toggleClass('icon-pencil icon-small');
    },

    /**
     * Switch the view to the Edit view if the field is editable and it's clicked on
     * @param evt
     */
    onClick: function (evt) {
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
        // submit if pressed return or tab
        if (evt.which == 13 || evt.which == 9) {
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
    onBlur: function (evt) {
        // submit if unfocused
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
        var regex = new RegExp("^\\+?\\d+$");
        // always make sure that we have a string here, since match only works on strings
        if (_.isNull(value.toString().match(regex))) {
            this.errorCode = 'LBL_CLICKTOEDIT_INVALID';
            return false;
        }

        // we have digits, lets make sure it's int a valid range is one is specified
        if (!_.isUndefined(this.def.minValue) && !_.isUndefined(this.def.maxValue)) {
            // we have a min and max value
            if (value < this.def.minValue || value > this.def.maxValue) {
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
    isEditable: function () {
        return this._canEdit;
    },

    /**
     * Check the value to see if it's a percentage, if it is, then figure out the change.
     *
     * @param value
     * @return {*}
     */
    parsePercentage: function (value) {
        var orig = this.value;
        var parts = value.match(/^([+-])([\d\.]+?)\%$/);
        if (parts) {
            // use original number to apply calculations
            return Math.round(eval(orig + parts[1] + "(" + parts[2] / 100 + "*" + orig + ")"));
        }

        return value
    }
})