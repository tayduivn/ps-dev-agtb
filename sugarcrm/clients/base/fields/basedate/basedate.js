({
    events: {
        'click .icon-calendar': '_toggleDatepicker'
    },
    datepickerVisible: false,

    // used by hbt template
    dateValue: '', 

    serverDateFormat: 'Y-m-d',

    /**
     * Base initialization
     * @param  {Object} options the options
     */
    initialize: function(options) {
        this.userTimePrefs  = app.user.get('timepref');
        this.usersDatePrefs = app.user.get('datepref');

        // Only if object has a showAmPm
        if (!_.isUndefined(this.showAmPm)) {

            // Sugar time format will always have an 'h'. If it ends with either [aA] it requires am/pm.
            this.showAmPm = this.userTimePrefs.match(/[aA]$/)==null ? true : false; // TODO: date.js doesn't yet support g/G options
        }

        app.view.Field.prototype.initialize.call(this, options);
    },

    /**
     * Base unformat
     * @param  {String} value the value
     * @return {String} computed value
     */
    unformat:function(value) {
        var jsDate;
        if (value) {
            jsDate = app.date.parse(value);
            if (jsDate && _.isFunction(jsDate.toISOString)) {
                return jsDate.toISOString();
            } else {
                app.logger.error("Issue converting date to iso string; no toISOString available for date created for value: "+value);
                return value;
            }
        }
        return value;
    },
        
    /**
     * NOP - jquery.timepicker (the plugin we use for time part of datetimecombo widget),
     * triggers both a 'change', and, a 'changeTime' event. If we let base Field's bindDomChange
     * handle, it will result in our format method getting called and we do NOT want that. Essentially,
     * we want our datepicker and timepicker plugins to handle any change related events.
     */
    bindDomChange: function() {
        // NOP -- pass through to prevent base Field's bindDomChange from handling
    },

    // NOP - Derived fields must implement
    _setDateIfDefaultValue: function() {
    },
    /**
     * Set our internal time and date values so hbt picks up
     */
    _presetDateValues: function() {
        this.dateValue = this.$('.datepicker').val();
        this.dateValue = (this.dateValue) ? this.dateValue : '';

        // Only if object has a _setTimeValue 
        if (!_.isUndefined(this._setTimeValue) && _.isFunction(this._setTimeValue)) {
            this._setTimeValue();
        }
    },
    /**
     * Toggles datepicker hidden or shown
     */
    _toggleDatepicker: function() {
        var action = (this.datepickerVisible) ? 'hide' : 'show';
        this.$(".datepicker").datepicker(action);
    },

    /**
     * Set up the Datepicker 
     * @return {[type]} [description]
     */
    _setupDatepicker: function() {
        this.datepickerMap = this._patchDatepickerMeta(); // converts com_cal_* to languageDictionary 
        this.$(".datepicker").attr('placeholder', app.date.toDatepickerFormat(this.usersDatePrefs));

        /* TODO: Remove all this once satisfied language injection works properly ;)
        var spanishLangExample = {
            days: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"],
            daysShort: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"],
            daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa", "Do"],
            months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
            monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"] 
        };*/
        this.$(".datepicker").datepicker({
            //languageDictionary: spanishLangExample, // TODO: remove this too!
            languageDictionary: this.datepickerMap,
            format: (this.usersDatePrefs) ? app.date.toDatepickerFormat(this.usersDatePrefs) : 'mm-dd-yyyy'
        });

        // Bind Datepicker to our proxy functions
        this.$(".datepicker").datepicker().on({
            show: _.bind(this.showDatepicker, this),
            hide: _.bind(this.hideDatepicker, this)
        });
    },
    /**
     * Hook when datepicker plugin shown.
     */
    showDatepicker: function(ev) {
        this.datepickerVisible = true;
    },
    /**
     * Main hook to update model when datepicker selected.
     */
    hideDatepicker: function(ev) {
        var model     = this.model,
            fieldName = this.name, 
            timeValue = '',
            hrsMins   = {},
            dateValue = '',
            $timepicker;

        this.datepickerVisible = false;
        model      = this.model;
        fieldName  = this.name;

        if (this.type === 'datetimecombo') {
            $timepicker= this.$('.ui-timepicker-input');
            // Get time values. If none, set to default of midnight; also get date, set model, etc.
            hrsMins    = this._getHoursMinutes($timepicker);
            this._setTimepickerValue($timepicker, hrsMins.hours, hrsMins.minutes);
        } else {
            // For non datetime (likely date) we blank out hours and minutes
            hrsMins    = {
                hours: '00',
                minutes: '00'
            };
        }
        dateValue  = this._getDatepickerValue();
        model.set(fieldName, this._buildUnformatted(dateValue, hrsMins.hours, hrsMins.minutes), {silent: true});
    },
    _buildUnformatted: function(d, h, m) {
        var parsedDate = app.date.parse(d, this.usersDatePrefs);
        d = app.date.format(parsedDate, this.serverDateFormat);
        return this.unformat(d + ' ' + h + ':' + m + ':00');
    },
    /**
     * Gets the current datepicker value.
     * 
     * Note: If we have no date (e.g. display default was set to none), when the 
     * user selects time part, date part will be pre-filled with today's date.
     */
    _getDatepickerValue: function() {
        var date  = this.$('input.datepicker'), dateValue;

        dateValue = this._getTodayDateStringIfNoDate(date.prop('value'));
        this.dateValue = dateValue; // so hbt template will pick up on next render
        return dateValue;
    },
    /**
     * Sets the current datepicker value.
     * @param String dateValue date value 
     */
    _setDatepickerValue: function(dateValue) {
        var date = this.$('input.datepicker');
        dateValue = this._getTodayDateStringIfNoDate(dateValue);
        date.prop('value', dateValue); 
    },

    /**
     * Per FDD: When the Datetime field required, default value should be SYSDATE and all zeros for Time
     */
    _setDateNow: function() {
        // Per the FDD: When the Datetime field is mandatory, the default value
        // should be SYSDATE and all zeros for Time value
        var jsDate = new Date();
        jsDate.setHours(0, 0, 0, 0);
        this.model.set(this.name, jsDate.toISOString(), {silent: true}); 
        return jsDate;
    },
    /**
     * Checks if dateStringToCheck is falsy..if so, returns today's date as string formatted by
     * user's prefs. Otherwise, just returns dateStringToCheck.
     */
    _getTodayDateStringIfNoDate: function(dateStringToCheck) {
        if (!dateStringToCheck) {
            var d = new Date();
            return app.date.format(d, this.usersDatePrefs);
        } 
        return dateStringToCheck;
    },
    /**
     * Gets the name of this view.
     * @return String view name
     */
    _getViewName: function() {
        return this.view.meta && this.view.meta.type ? this.view.meta.type : this.view.name;
    },
    /**
     * Determines if this view is edit view.
     * @param {String} The view name
     * @return Boolean true if edit view 
     */
    _isEditView: function(viewName) {
        if(this.options.def.view === 'edit' || this.options.viewName === 'edit' || viewName === 'edit') {
            return true;
        }
        return false;
    },

    // TODO: Hack! 
    // We need to verify that reusing the app_list_strings.dom_cal_* is the way to go on this. Other
    // option is to just create a new meta property ** sigh ** yet more junk in app list strings!
    // 
    // Patches our dom_cal_* metadata for use with our datepicker plugin since they're very similar.
    _patchDatepickerMeta: function() {
        var pickerMap = [], pickerMapKey, calMapIndex, mapLen, domCalKey, 
            calProp, appListStrings, calendarPropsMap, i;

        appListStrings = app.metadata.getStrings('app_list_strings');
            
        // Note that ordering here is used in following for loop 
        calendarPropsMap = ['dom_cal_day_long',
                            'dom_cal_day_short',
                            'dom_cal_month_long',
                            'dom_cal_month_short'];

        for (calMapIndex = 0, mapLen = calendarPropsMap.length; calMapIndex < mapLen; calMapIndex++) {

            domCalKey = calendarPropsMap[calMapIndex];
            calProp  = appListStrings[domCalKey];

            // Patches the metadata to work w/datepicker (which is almost the same).
            // Meta "calProp" will look something like:
            // ["", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]
            // But we need:
            // ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"]
            // So here we check if the first element is falsy, if so, splice it out, and copy, first to last.
            if (calProp && calProp.length>1 && !calProp[0]) {
                calProp.shift();
                calProp.push(calProp[0]);
            }
            switch (calMapIndex) {
                case 0:
                    pickerMapKey = 'day';
                    break;
                case 1:
                    pickerMapKey = 'daysShort';
                    break;
                case 2:
                    pickerMapKey = 'months';
                    break;
                case 3:
                    pickerMapKey = 'monthsShort';
                    break;
            }
            pickerMap[pickerMapKey] = calProp;
        }

        // Now add a daysMin property with just two chars per day
        pickerMap['daysMin'] = _.map(pickerMap.daysShort, function(day) {
            return (day.length > 1) ? day.substr(0,2) : day;
        });

        return pickerMap;
    },
    _isNewEditViewWithNoValue: function(value) {
        return (this.model.isNew() && !value && this._isEditView(this._getViewName()));
    },
    /**
     * Pads an int string to two digits.
     * @param {String} numstr The int as string
     */
    _forceTwoDigits: function(numstr) {
        return numstr.length === 1 ? '0' + numstr: numstr;
    }

})