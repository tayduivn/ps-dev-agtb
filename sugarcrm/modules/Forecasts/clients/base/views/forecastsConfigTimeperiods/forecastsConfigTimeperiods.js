/**
 * Events Triggered
 *
 * liszt:updated
 *      on: timperiod_start_day
 *      by: _setUpTimeperiodStartMonthBind()
 */
({

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        if(!_.isUndefined(options.meta.registerLabelAsBreadCrumb) && options.meta.registerLabelAsBreadCrumb == true) {
            this.layout.registerBreadCrumbLabel(options.meta.panels[0].label);
        }
    },

    /**
     * Overriding _renderField because we need to set up a binding to the start month drop down to populate the day drop down on change
     * @param field
     * @private
     */
    _renderField: function(field) {

        field = this._setUpTimeperiodConfigField(field);

        // TODO-sfa this will get removed when the timeperiod mapping functionality is added (SFA-214)
        /**
         * This is needed to make sure that this view is read only when forecasts module has been set up.
         */
        if(this.model.get('is_setup')) {
            // if forecasts has been setup, this is read only!
            field.options.def.view = 'detail';
        }
        app.view.View.prototype._renderField.call(this, field);

        // format the datepicker used to set the timeperiod start month and day to not show the year
        if (field.name == 'timeperiod_start_picker') {
            field.$('.datepicker').datepicker("option", "dateFormat", "mm-dd");
        }
    },

    /**
     * Sets up the fields with the handlers needed to properly get and set their values for the timeperiods config view.
     * @param field the field to be setup for this config view.
     * @return {*} field that has been properly setup and augmented to function for this config view.
     * @private
     */
    _setUpTimeperiodConfigField: function(field) {
        switch(field.name) {
            case "timeperiod_start_picker":
                return this._setUpTimeperiodPicker(field);
            case "timeperiod_shown_forward":
            case "timeperiod_shown_backward":
                return this._setUpTimeperiodShowField(field);
//BEGIN SUGARCRM flav=pro ONLY
            case "timeperiod_interval":
                return this._setUpTimeperiodIntervalBind(field);
//END SUGARCRM flav=pro ONLY
            default:
                return field;
        }
    },


    _setUpTimeperiodPicker: function(field) {
        var today = new Date();

        field.bindDomChange  = function() {
            if (!(this.model instanceof Backbone.Model)) return;

            var self = this;
            var el = this.$el.find(this.fieldTag);
            el.on("change", function() {
                var value = new Date(self.unformat(el.val()));

                self.model.set("timeperiod_start_day", value.getDate() + 1);
                self.model.set("timeperiod_start_month", value.getMonth() + 1);
            });
            // Focus doesn't always change when tabbing through inputs on IE9 (Bug54717)
            // This prevents change events from being fired appropriately on IE9
            if($.browser.msie && el.is("input")){
                el.on("input", function() {
                    // Set focus on input element receiving user input
                   el.focus();
                });
            }
        };

        /**
         * override bindDataChange to update the date picker in the UI properly whene the value for either
         * `timeperiod_start_month` or `timeperiod_start_day` changes in the model.
          */
        field.bindDataChange = function() {
            if (this.model) {
                this.model.on("change:timeperiod_start_day change:timeperiod_start_month", function() {
                    var today = new Date();

                    this.value = today.getFullYear().toString() + '-' + this.model.get('timeperiod_start_month') + '-' + this.model.get('timeperiod_start_day');
                    this.model.set(this.name, this.value);
                    this.render();
                }, this);
            }
        };


        field.value = today.getFullYear().toString() + '-' + this.model.get('timeperiod_start_month') + '-' + this.model.get('timeperiod_start_day');
        field.model.set(field.name, field.value);

        return field;
    },

    /**
     * Sets up the timeperiod_shown_forward and timeperiod_shown_backward dropdowns to set the model and values properly
     * @param field The field being set up.
     * @return {*} The configured field.
     * @private
     */
    _setUpTimeperiodShowField: function (field) {
        // ensure Date object gets an additional function
        field.events = _.extend({"change select":  "_updateSelection"}, field.events);
        field.bindDomChange = function() {};

        field._updateSelection = function(event, input) {
            var value =  parseInt(input.selected);
            this.def.value = value;
            this.model.set(this.name, value);
        };

        field.def.value = this.model.get(field.name) || 1;
        return field;
    }

    //BEGIN SUGARCRM flav=pro ONLY
    ,
    /**
     * Sets up the change event on the timeperiod_interval drop down to maintain the interval selection
     * and push in the default selction for the leaf period
     * @param field the dropdown interval field
     * @return {*}
     * @private
     */
    _setUpTimeperiodIntervalBind: function(field) {

        field.def.value = this.model.get(field.name);

        // ensure selected day functions like it should
        field.events = _.extend({"change select":  "_updateIntervals"}, field.events);
        field.bindDomChange = function() {};

        if(typeof(field.def.options) == 'string') {
            field.def.options = app.lang.getAppListStrings(field.def.options);
        }

        /**
         * function that updates the selected interval
         * @param event
         * @param input
         * @private
         */
        field._updateIntervals = function(event, input) {
            //get the timeperiod interval selector
            var selected_interval = "Annual";
            if(_.has(input, "selected")) {
                selected_interval = input.selected;
            }
            this.def.value = selected_interval;
            this.model.set(this.name, selected_interval);
            this.model.set('timeperiod_leaf_interval', selected_interval == 'Annual' ? 'Quarter' : 'Month');
        }
        return field;

    }
    //END SUGARCRM flav=pro ONLY
})