({
    // date
    extendsFrom:'BasedatepickerField',

    _render: function(value) {
        var self = this, viewName;

        self._presetDateValues();

        app.view.Field.prototype._render.call(this);//call proto render

        viewName = self._getViewName();

        $(function() {
            if (self._isEditView(viewName)) {
                self._setupDatepicker();
            }
        });
    },
    /**
     * Formats value per user's preferences 
     * @param {String} value The value to format 
     * @return {String} Formatted value 
     */
    format:function(value) {
        var jsDate, parts;
        if (this._isNewEditViewWithNoValue(value)) {
            // If there is a default 'string' value like "yesterday", format it as a date
            jsDate = this._setDateIfDefaultValue();
            if (!jsDate) {
                return value;
            }
            value  = app.date.format(jsDate, this.usersDatePrefs);
        } else if (!value) {
            return value;
        } else {
            // Bug 56249 .. Date constructor doesn't reliably handle yyyy-mm-dd
            // e.g. new Date("2011-10-10" ) // in my version of chrome browser returns
            // Sun Oct 09 2011 17:00:00 GMT-0700 (PDT)
            parts = value.match(/(\d+)/g);
            jsDate = new Date(parts[0], parts[1]-1, parts[2]); //months are 0-based
            value  = app.date.format(jsDate, this.usersDatePrefs);
        }
        this.dateValue = value;
        this.$(".datepicker").datepicker('update', this.dateValue);
        jsDate = app.date.parse(value);
        return app.date.format(jsDate, this.usersDatePrefs);
    },

    /**
     * Overrides basedate's unformat.
     */
    unformat:function(value) {
        // In case ISO 8601 get it back to js native date which date.format understands
        var jsDate = new Date(value);
        return app.date.format(jsDate, this.serverDateFormat);

    },

    /**
     * If the field def has a display_default property, or, is required, this
     * will set the model with corresponding date time.
     * @return {Date} The date created
     */
    _setDateIfDefaultValue: function() {
        var value, jsDate; 

        if (this.def.display_default) {
            jsDate = app.date.parseDisplayDefault(this.def.display_default);
            this.model.set(this.name, app.date.format(jsDate, this.serverDateFormat));
        } else if (this.def.required) {
            return this._setDateNow();
        } else {
            return null;  
        }
        return jsDate;
    }

})

