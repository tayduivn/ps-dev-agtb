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
    },

    /**
     * Sets up the fields with the handlers needed to properly get and set their values for the timeperiods config view.
     * @param field the field to be setup for this config view.
     * @return {*} field that has been properly setup and augmented to function for this config view.
     * @private
     */
    _setUpTimeperiodConfigField: function(field) {
        switch(field.name) {
            case "timeperiod_start_month":
                return this._setUpTimeperiodStartMonthBind(field);
            case "timeperiod_start_day":
                return this._setUpTimeperiodStartDayBind(field);
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
    },

    /**
     * Sets up the change event on the timeperiod_start_month drop down to change the day drop down based on the month
     * @param field the dropdown month field
     * @return {*}
     * @private
     */
    _setUpTimeperiodStartMonthBind: function (field) {
        // ensure Date object gets an additional function
        field.events = _.extend({"change select":  "_updateDaysForMonth"}, field.events);
        field.bindDomChange = function() {};

        if(typeof(field.def.options) == 'string') {
            field.def.options = app.lang.getAppListStrings(field.def.options);
        }

        /**
         * function that uses the selected month to key in and determine how many days to file into the date chooser for timeperiods
         * @param event
         * @param input
         * @private
         */
        field._updateDaysForMonth = function(event, input) {
            //get the timeperiod day selector
            var timeperiod_start_day = $('select[name="timeperiod_start_day"]'),
                selected_month = 1;

            //trash the current options
            $('option', timeperiod_start_day).remove();
            if(_.has(input, "selected")) {
                selected_month = input.selected;
                timeperiod_start_day.append(this._buildDaysOptions(input.selected));
                timeperiod_start_day.trigger('liszt:updated');
            }
            this.def.value = selected_month;
            this.model.set(this.name, selected_month);
        };

        field._buildDaysOptions = function(selected_month) {
            var option_html,
                selectedDay = this.model.get('timeperiod_start_day') || 1,
                current_date = new Date(),
                days;

            /*
             selected_month will be the value as selected from the dropdown, i. e. January == 1,
             JS Date equates 0 to January, so to get the days in the month, we can do month + 1, with day 0, which is why
             we don't adjust for the -1 offset from the dropdown here.
              */
            days = new Date(current_date.getFullYear(), selected_month, 0).getDate();

            option_html = '<option value=""></option>';

            for (var i = 1; i <= days; i++) {
                option_html += '<option value="' + i + '"';
                if(i == selectedDay) {
                    option_html += ' selected ';
                }
                option_html += '>' + i + '</option>';
            }
            return option_html;
        };

        field.def.value = this.model.get(field.name) || 1;
        return field;
    },

    /**
     * Sets up the change event on the timeperiod_start_day drop down to maintain the day selection
     * @param field the dropdown month field
     * @return {*}
     * @private
     */
    _setUpTimeperiodStartDayBind: function(field) {
        var current_date = new Date(),
            days;

        field.def.value = this.model.get(field.name);

        //build the day options based on the initially selected month
        days = new Date(current_date.getFullYear(), this.model.get('timeperiod_start_month') - 1, 0).getDate();

        field.def.options = {};
        for (var i = 1; i <= days; i++) {
            field.def.options[i] = i;
        }

        // ensure selected day functions like it should
        field.events = _.extend({"change select":  "_updateDays"}, field.events);
        field.bindDomChange = function() {};

        /**
         * function that updates the selected day
         * @param event
         * @param input
         * @private
         */
        field._updateDays = function(event, input) {
            //get the timeperiod day selector
            var selected_day = 0;
            if(_.has(input, "selected")) {
               selected_day = input.selected;
            }
            this.def.value = selected_day;
            this.model.set(this.name, selected_day);
        }

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