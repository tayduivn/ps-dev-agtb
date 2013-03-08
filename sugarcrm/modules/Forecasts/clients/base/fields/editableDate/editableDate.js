({
    extendsFrom: 'DateField',

    events: {
        'mouseenter span.editable': 'togglePencil',
        'mouseleave span.editable': 'togglePencil',
        'click span.editable': 'onClick',
        'blur input.datepicker': 'onBlur',
        'keyup input.datepicker': 'onKeyUp',
        'keydown input.datepicker': 'onKeyDown'
    },

    inputSelector: 'input.datepicker',

    errorMessage: '',

    _canEdit: true,

    initialize: function (options) {
        app.view.fields.DateField.prototype.initialize.call(this, options);
        this.checkIfCanEdit();
    },

    /**
     * Utility Method to check if we can edit again.
     */
    checkIfCanEdit: function() {
    },

    /**
     * Overwrite bindDomChange
     *
     * Since we need to do custom logic when a field changes, we have to overwrite this with out ever calling
     * the parent.
     *
     */
    bindDomChange: function () {
        return;
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
        if (!this.isEditable()) {
            return;
        }

        this.options.def.view = 'edit';
        if (!this.disposed) {
            this.render();
        }

        // put the focus on the input
        this.$el.find(this.inputSelector).focus().select();
    },

    /**
     * Handle when return/enter and tab keys are pressed
     *
     * @param evt
     */
    onKeyUp: function (evt) {
        // submit if pressed return or tab
        if (evt.which == 13) {
            var ogVal = this.value,
                ngVal = this.$el.find(this.inputSelector).val();

            if (_.isEqual(ogVal, ngVal)) {
                this.$el.find(this.inputSelector).blur();
            }
        }
    },


    /**
     * Handle when return/enter and tab keys are pressed
     *
     * @param evt
     */
    onKeyDown: function (evt) {
        // submit if pressed return or tab
        if (evt.which == 9) {
            evt.preventDefault();
            // tab key pressed, trigger event from context
            this.context.trigger('forecasts:tabKeyPressed', evt.shiftKey, this);
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
        this.options.def.view = 'detail';
        if (!this.disposed) {
            this.render();
        }
    },

    /**
     * Is the new value valid for this field.
     *
     * @param value
     * @return {Boolean}
     */
    isValid: function (value) {
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
     * Method to show the error message
     */
    showErrors : function() {
        // attach error styles
    },

    _setDateIfDefaultValue: function() {

    }
})
