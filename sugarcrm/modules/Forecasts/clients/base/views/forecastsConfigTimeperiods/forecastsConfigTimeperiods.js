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
        if (field.name == "timeperiod_start_month") {
            field = this._setUpTimeperiodStartMonthBind(field);
        } else if(field.name == "timeperiod_start_day") {
            field = this._setUpTimeperiodStartDayBind(field);
        }
        //BEGIN SUGARCRM flav=pro ONLY
            if (field.name == "timeperiod_interval") {
                field = this._setUpTimeperiodIntervalBind(field);
            }
        //END SUGARCRM flav=pro ONLY

        /**
         * This is needed to make sure that this view is read only
         * when viewing it in the Tabbed Config View
         */
        if(!_.isUndefined(this.layout) && this.layout.meta.type == "forecastsTabbedConfig") {
            // if we are on the tabbed config, this is read only!
            field.options.def.view = 'detail';
        }
        app.view.View.prototype._renderField.call(this, field);
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
            var timeperiod_start_day = $('select[name="timeperiod_start_day"]');
            var selected_month = 0
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
            var option_html
            var current_date = new Date();
            current_date.setMonth(selected_month);
            current_date.setDate(0);
            option_html = '<option value=""></option>';
            var days = current_date.getDate();
            for (var i = 1; i <= days; i++) {
                option_html += '<option value="' + i + '"';
                if(i == this.model.get('timeperiods_start_day')) {
                    option_html += ' selected ';
                }
                option_html += '>' + i + '</option>';
            }
            return option_html;
        };
        // INVESTIGATE:  This is to get around what may be a bug in sidecar. The field.value gets overriden somewhere and it shouldn't.
        //field.def.value = this.model.get(field.name)+'';
        field.def.value = this.model.get(field.name);
        return field;
    },

    /**
     * Sets up the change event on the timeperiod_start_day drop down to maintain the day selection
     * @param field the dropdown month field
     * @return {*}
     * @private
     */
    _setUpTimeperiodStartDayBind: function(field) {

        // INVESTIGATE:  This is to get around what may be a bug in sidecar. The field.value gets overriden somewhere and it shouldn't.
        field.def.value = this.model.get(field.name);

        //build the day options based on the initially selected month
        var current_date = new Date();
        current_date.setMonth(this.model.get('timeperiod_start_month'));
        current_date.setDate(0);
        field.def.options = {};
        var days = current_date.getDate();
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

        var self = this;
        // INVESTIGATE:  This is to get around what may be a bug in sidecar. The field.value gets overriden somewhere and it shouldn't.
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
            self.model.set(this.name, selected_interval);
            self.model.set('timeperiod_leaf_interval', selected_interval == 'Annual' ? 'Quarter' : 'Month');
        }
        return field;

    }
    //END SUGARCRM flav=pro ONLY
})