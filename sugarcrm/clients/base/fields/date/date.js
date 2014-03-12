/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
({
    /**
     * {@inheritDoc}
     */
    plugins: [
        'EllipsisInline',
        'Tooltip'
    ],

    /**
     * {@inheritDoc}
     */
    fieldTag: 'input[data-type=date]',

    /**
     * {@inheritDoc}
     */
    events: {
        'hide': 'handleHideDatePicker'
    },

    /**
     * Boolean if the datepicker is visible or not
     */
    datepickerVisible: undefined,

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        this.datepickerVisible = false;
        // FIXME: Remove this when SIDECAR-517 gets in
        this._initPlugins();
        this._super('initialize', [options]);
        this._initEvents();
    },

    /**
     * Initialize plugins.
     *
     * @chainable
     * @protected
     * @template
     *
     * FIXME: Remove this when SIDECAR-517 gets in
     */
    _initPlugins: function() {
        return this;
    },

    /**
     * Initialize events.
     *
     * @chainable
     * @protected
     * @template
     */
    _initEvents: function() {
        return this;
    },

    /**
     * Return user date format.
     *
     * @return {String} User date format.
     */
    getUserDateFormat: function() {
        return app.user.getPreference('datepref');
    },

    /**
     * Set up date picker.
     *
     * We rely on the library to confirm that the date picker is only created
     * once.
     */
    _setupDatePicker: function() {
        var $field = this.$(this.fieldTag),
            userDateFormat = this.getUserDateFormat(),
            datePickerDateFormat = app.date.toDatepickerFormat(userDateFormat);

        // FIXME: find a proper way to do this and avoid scrolling issues
        var appendTarget = this.$el.parents('div#drawers').length ? 'div#drawers .active .main-pane:first' : 'div#content .main-pane:first';

        $field.datepicker({
            format: datePickerDateFormat,
            languageDictionary: this._patchPickerMeta(),
            appendTo: appendTarget
        });

        this.datepickerVisible = true;

        $field.attr('placeholder', datePickerDateFormat);

        if (this.def.required) {
            this.setRequiredPlaceholder($field);
        }
    },

    /**
     * Date picker doesn't trigger a `change` event whenever the date value
     * changes we need to override this method and listen to the `hide` event.
     *
     * All invalid values are cleared from fields without triggering an event
     * because `this.model.set()` could have been already empty thus not
     * triggering a new event and not calling the default code of
     * `bindDomChange()`.
     */
    handleHideDatePicker: function() {
        var $field = this.$(this.fieldTag),
            value = this.unformat($field.val());

        if (!value) {
            $field.val(value);
        }

        this.model.set(this.name, value);
    },

    /**
     * {@inheritDoc}
     */
    bindDomChange: function() {
        this._super('bindDomChange');

        var $field = this.$(this.fieldTag);

        $field.on('focus', _.bind(this.handleFocus, this));

        $('.main-pane, .flex-list-view-content').on('scroll', _.bind(function() {
            $field.datepicker('place');
        }, this));
    },

    /**
     * {@inheritDoc}
     */
    unbindDom: function() {
        this._super('unbindDom');

        $('.main-pane, .flex-list-view-content').off();
    },

    /**
     * Patches our `dom_cal_*` metadata for use with date picker plugin since
     * they're very similar.
     */
    _patchPickerMeta: function() {
        var pickerMap = [], pickerMapKey, calMapIndex, mapLen, domCalKey,
            calProp, appListStrings, calendarPropsMap, i, filterIterator;

        appListStrings = app.metadata.getStrings('app_list_strings');

        filterIterator = function(v, k, l) {
            return v !== "";
        };

        // Note that ordering here is used in following for loop
        calendarPropsMap = ['dom_cal_day_long', 'dom_cal_day_short', 'dom_cal_month_long', 'dom_cal_month_short'];

        for (calMapIndex = 0, mapLen = calendarPropsMap.length; calMapIndex < mapLen; calMapIndex++) {

            domCalKey = calendarPropsMap[calMapIndex];
            calProp  = appListStrings[domCalKey];

            // Patches the metadata to work w/datepicker; initially, "calProp" will look like:
            // {0: "", 1: "Sunday", 2: "Monday", 3: "Tuesday", 4: "Wednesday", 5: "Thursday", 6: "Friday", 7: "Saturday"}
            // But we need:
            // ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"]
            if (!_.isUndefined(calProp) && !_.isNull(calProp)) {
                // Reject the first 0: "" element
                calProp = _.filter(calProp, filterIterator);
                //e.g. pushed the Sun in front to end (as required by datepicker)
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
        return pickerMap;
    },

    /**
     * If we're on edit view and a valid `display_default` property was supplied
     * (e.g.: `next friday`) we'll use it as a date instead.
     *
     * @return {Date} The date created or `undefined` if `display_default` isn't
     *   supplied or is invalid.
     */
    _setDefaultValue: function() {
        if (!this.model.isNew() || this.action !== 'edit' || !this.def.display_default) {
            return;
        }

        var value = app.date.parseDisplayDefault(this.def.display_default);
        if (!value) {
            return;
        }

        value = this.unformat(
            app.date(value).format(
                app.date.convertFormat(this.getUserDateFormat())
            )
        );

        this.model.set(this.name, value);
        return value;
    },

    /**
     * Formats date value according to user preferences.
     *
     * If no value is defined, we {@link #_setDefaultValue set a default value}.
     *
     * @param {String} value Date value to format.
     * @return {String} Formatted value.
     */
    format: function(value) {
        value = value || this._setDefaultValue();

        if (!value) {
            return value;
        }

        return app.date(value).formatUser(true);
    },

    /**
     * Unformats date value for storing in model.
     *
     * @return {String} Unformatted value.
     */
    unformat: function(value) {
        if (!value) {
            return value;
        }

        value = app.date(value, app.date.convertFormat(this.getUserDateFormat()), true);

        if (!value.isValid()) {
            return;
        }

        return value.formatServer(true);
    },

    /**
     * {@inheritDoc}
     */
    _render: function() {
        this._closeDatepicker();

        this._super('_render');

        if (this.action !== 'edit') {
            return;
        }

        this._setupDatePicker();
    },

    /**
     * Closes datepicker without setting value on the model
     *
     * @private
     */
    _closeDatepicker: function() {
        var $field = this.$(this.fieldTag);
        if ($field.data('datepicker') && !$field.data('datepicker').hidden) {
            // todo: when SC-2395 gets implemented change this to 'remove' not 'hide'
            $field.datepicker('hide');
            this.datepickerVisible = false;
        }
    },

    /**
     * {@inheritdoc}
     */
    _dispose: function() {
        // FIXME: new date picker versions have support for plugin removal/destroy
        // we should do the upgrade in order to prevent memory leaks

        // if datepicker is open, close before disposing
        if(this.datepickerVisible) {
            this._closeDatepicker();
        }

        this._super('_dispose');
    }
})
