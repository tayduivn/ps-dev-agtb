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

    render: function() {
        app.view.Field.prototype.render.call(this);
        // need to add this class to the parent td cell and we dont
        // have access to that from the field level
        var tdEl = this.$el.parent('td');
        if(!tdEl.hasClass('td-inline-edit')) {
            tdEl.addClass('td-inline-edit');
        }
    },

    /**
     * Utility Method to check if we can edit again.
     */
    checkIfCanEdit: function() {
        // only worksheet owner can edit
        var selectedUser = this.context.get('selectedUser');
        this._canEdit = _.isEqual(app.user.get('id'), selectedUser.id);
        // only if sales stage is won/lost can edit
        if(this._canEdit) {
            var salesStage = this.model.get('sales_stage'),
                disableIfSalesStageIs = _.union(
                    app.metadata.getModule('Forecasts', 'config').sales_stage_won,
                    app.metadata.getModule('Forecasts', 'config').sales_stage_lost
                );
            if(salesStage && _.indexOf(disableIfSalesStageIs, salesStage) != -1) {
                this._canEdit = false;
            }
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
     * overridden from date.js -- Forecasts must validate date before setting the model
     * whereas the base date.js field sets the model, then does validation when you save
     *
     * @param ev
     */
    hideDatepicker: function(ev) {
        var hrsMins = {
            hours: '00',
            minutes: '00'
        };

        this.datepickerVisible = false;

        // sets this.dateValue
        this._getDatepickerValue();

        if(this._verifyDateString(this.dateValue)) {
            // sidecar field validation stuff we dont use, but setting to maintain compatibility
            this.leaveDirty = false;

            // set the field model with the new valid dateValue
            this.model.set(this.name, this._buildUnformatted(this.dateValue, hrsMins.hours, hrsMins.minutes));

            // trigger the onBlur function to set the field back to detail view and render
            this.onBlur(ev);
        } else {
            var hb = Handlebars.compile("{{str_format key module args}}"),
                args = [app.lang.get(this.def.label, 'Forecasts')];

            // sidecar field validation stuff we dont use, but setting to maintain compatibility
            this.leaveDirty = true;

            // set the proper error message
            this.errorMessage = hb({'key': 'LBL_EDITABLE_INVALID', 'module': 'Forecasts', 'args': args});

            // display rad error tooltipz!
            this.showErrors();
        }
    },

    /**
     * overridden from date.js -- Forecasts must validate date before setting the model
     * whereas the base date.js field sets the model, then does validation when you save
     *
     * @param value
     * @return {Boolean}
     * @private
     */
    _verifyDateString: function(value) {
        var dateFormat = (this.usersDatePrefs) ? app.date.toDatepickerFormat(this.usersDatePrefs) : 'mm-dd-yyyy',
            isValid = true;

        //First try generic date parse (since we might have an ISO). This should generally work with the
        //ISO date strings we get from server.
        if(_.isNaN(Date.parse(value))) {
            isValid = false;
            //Safari chokes on '.', '-', so retry replacing with '/'
            if(_.isNaN(value.replace(/[\.\-]/g, '/'))) {
                //Use datepicker plugin to verify datepicker format
                isValid = $.prototype.DateVerifier(value, dateFormat);
            }
        }
        return isValid;
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
        // switch back to the field's detail view
        this.options.def.view = 'detail';
        // sidecar field validation stuff we dont use, but setting to maintain compatibility
        this.leaveDirty = false;
        // hide any error stuff that still may be visible
        this.hideErrors();
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
        this.$el.find('.error-tooltip').addClass('add-on local').removeClass('hide').css('display','inline-block');
        // we want to show the tooltip message, but hide the add-on (exclamation)
        this.$el.find("[rel=tooltip]").tooltip('destroy'); // so the title is not cached
        this.$el.find("[rel=tooltip]").tooltip({container: 'body', placement: 'top', title: this.errorMessage}).tooltip('show').hide();
    },

    /**
     * Undo everything that showErrors does to the dom, otherwise you can enter an invalid date,
     * leave the field which displays the previous date like normal, then when you come back you would
     * see error styles even though you just clicked in the field
     */
    hideErrors: function() {
        this.$el.find('.error-tooltip').removeClass('add-on local').addClass('hide');
    },

    _setDateIfDefaultValue: function() {

    }
})
