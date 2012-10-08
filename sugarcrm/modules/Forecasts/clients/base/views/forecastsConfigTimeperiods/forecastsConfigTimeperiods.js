({

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
                timeperiod_start_day.append(this._buildDaysOptions(input.selected));
                timeperiod_start_day.trigger('liszt:updated');

            }
            this.model.set('timeperiods_start_month', selected_month);
        };

        field._buildDaysOptions = function(selected_month) {
            var option_html
            var current_date = new Date();
            current_date.setMonth(selected_month);
            current_date.setDate(0);
            option_html = '<option value=""></option>';
            var days = current_date.getDate();
            return option_html;
        };
        return field;
    },

    /**
     * Sets up the change event on the timeperiod_start_day drop down to maintain the day selection
     * @param field the dropdown month field
     * @return {*}
     * @private
     */
    _setUpTimeperiodStartDayBind: function(field) {
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
            this.model.set('timeperiods_start_day', selected_day);
        }
        return field;

    }

})