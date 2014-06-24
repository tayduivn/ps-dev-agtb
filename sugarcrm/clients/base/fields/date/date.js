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
/**
 * @class View.Fields.Base.DateField
 * @alias SUGAR.App.view.fields.BaseDateField
 * @extends View.Field
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
     * {@inheritDoc}
     */
    initialize: function(options) {
        // FIXME: Remove this when SIDECAR-517 gets in
        this._initPlugins();
        this._super('initialize', [options]);
        this._initEvents();
        this._initDefaultValue();
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
     * If we're creating a new model and a valid `display_default` property was
     * supplied (e.g.: `next friday`) we'll use it as a date instead.
     *
     * @chainable
     * @protected
     */
    _initDefaultValue: function() {
        if (!this.model.isNew() || this.model.get(this.name) || !this.def.display_default) {
            return this;
        }

        var value = app.date.parseDisplayDefault(this.def.display_default);
        if (!value) {
            return this;
        }

        value = this.unformat(
            app.date(value).format(
                app.date.convertFormat(this.getUserDateFormat())
            )
        );

        this.model.set(this.name, value);
        this.model.setDefaultAttribute(this.name, value);

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
     *
     * @protected
     */
    _setupDatePicker: function() {
        var $field = this.$(this.fieldTag),
            userDateFormat = this.getUserDateFormat(),
            datePickerDateFormat = app.date.toDatepickerFormat(userDateFormat),
            options = {
                format: datePickerDateFormat,
                languageDictionary: this._patchPickerMeta()
            };

        var appendToTarget = this._getAppendToTarget();
        if (appendToTarget) {
            options['appendTo'] = appendToTarget;
        }

        $field.datepicker(options);
        $field.attr('placeholder', datePickerDateFormat);

        if (this.def.required) {
            this.setRequiredPlaceholder($field);
        }
    },

    /**
     * Retrieve a selector against which the date picker should be appended to.
     *
     * FIXME: find a proper way to do this and avoid scrolling issues SC-2739
     *
     * @return {String/undefined} Selector against which the date picker should
     *   be appended to, `undefined` if none.
     * @private
     */
    _getAppendToTarget: function() {
        if (this.$el.parents('div#drawers').length) {
            return 'div#drawers .active .main-pane:first';
        }

        if (this.$el.parents('div#content').length) {
            return 'div#content .main-pane:first';
        }

        return;
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
     * {@override}
     *
     * Parent method isn't called 'cause `handleHideDatePicker()` already takes
     * care of unformatting the value.
     */
    bindDomChange: function() {
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

        var $field = this.$(this.fieldTag),
            datePicker = $field.data('datepicker');
        if (datePicker && !datePicker.hidden) {
            // todo: when SC-2395 gets implemented change this to 'remove' not 'hide'
            $field.datepicker('hide');
        }
    },

    /**
     * Patches our `dom_cal_*` metadata for use with date picker plugin since
     * they're very similar.
     *
     * @private
     */
    _patchPickerMeta: function() {
        var pickerMap = [], pickerMapKey, calMapIndex, mapLen, domCalKey,
            calProp, appListStrings, calendarPropsMap, i, filterIterator;

        appListStrings = app.metadata.getStrings('app_list_strings');

        filterIterator = function(v, k, l) {
            return v[1] !== "";
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
                // Reject the first 0: "" element and then map out the new language tuple
                // so it's back to an array of strings
                calProp = _.filter(calProp, filterIterator).map(function(prop) {
                    return prop[1];
                });
                //e.g. pushed the Sun in front to end (as required by datepicker)
                calProp.push(calProp);
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
     * Formats date value according to user preferences.
     *
     * @param {String} value Date value to format.
     * @return {String/undefined} Formatted value or `undefined` if value is an
     *   invalid date.
     */
    format: function(value) {
        if (!value) {
            return value;
        }

        value = app.date(value);

        if (!value.isValid()) {
            return;
        }

        return value.formatUser(true);
    },

    /**
     * Unformats date value for storing in model.
     *
     * @return {String/undefined} Unformatted value or `undefined` if value is
     *   an invalid date.
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
        this._super('_render');

        if (this.action !== 'edit' && this.action !== 'massupdate') {
            return;
        }

        this._setupDatePicker();
    },

    /**
     * {@inheritdoc}
     */
    _dispose: function() {
        // FIXME: new date picker versions have support for plugin removal/destroy
        // we should do the upgrade in order to prevent memory leaks

        var $field = this.$(this.fieldTag);
        if ($field.data('datepicker')) {
            $(window).off('resize', $field.data('datepicker').place);
        }

        this._super('_dispose');
    }
})
