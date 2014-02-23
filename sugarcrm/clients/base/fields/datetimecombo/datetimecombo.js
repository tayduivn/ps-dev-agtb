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
    extendsFrom: 'DateField',

    /**
     * HTML tag of the secondary field.
     *
     * @property {String}
     */
    secondaryFieldTag: 'input[data-type=time]',

    /**
     * {@inheritDoc}
     *
     * Add `FieldDuplicate` plugin to the list of required plugins.
     */
    _initPlugins: function() {
        this._super('_initPlugins');

        this.plugins = _.union(this.plugins, [
            'FieldDuplicate'
        ]);

        return this;
    },

    /**
     *{@inheritDoc}
     *
     * Add `show-timepicker` on click listener.
     */
    _initEvents: function() {
        this._super('_initEvents');

        _.extend(this.events, {
            'click [data-action="show-timepicker"]': 'showTimePicker'
        });

        return this;
    },

    /**
     * Handler to refresh field state.
     *
     * Called from {@link Sugar.App.plugins._onFieldDuplicate}.
     */
    onFieldDuplicate: function() {
        if (this.disposed) {
            return;
        }

        if (this.view.name === 'merge-duplicates' &&
            this.options.viewName &&
            this.options.viewName === 'edit'
        ) {
            if (_.isEmpty(this.model.get(this.name))) {
                this.$(this.fieldTag).val('');
                this.$(this.secondaryFieldTag).val('');
            } else {
                this.format(this.model.get(this.name));
            }
        }
    },

    /**
     * Handler to show time picker on icon click.
     *
     * We trigger the focus on element instead of the jqueryfied element, to
     * trigger the focus on the input and avoid the `preventDefault()` imposed
     * in the library.
     */
    showTimePicker: function() {
        this.$(this.secondaryFieldTag)[0].focus();
    },

    /**
     * Return user time format.
     *
     * @return {String} User time format.
     */
    getUserTimeFormat: function() {
        return app.user.getPreference('timepref');
    },

    /**
     * Return user time format.
     *
     * @return {String} User time format.
     */
    getUserDateTimeFormat: function() {
        return this.getUserDateFormat() + ' ' + this.getUserTimeFormat();
    },

    /**
     * Return time place holder based on supplied format.
     *
     * @param {String} format Format.
     * @return {String} Time place holder.
     */
    getTimePlaceHolder: function(format) {
        var map = {
            'H': 'hh',
            'h': 'hh',
            'i': 'mm',
            'a': '',
            'A': ''
        };

        return format.replace(/[HhiaA]/g, function(s) {
            return map[s];
        });
    },

    /**
     * Set up the time picker.
     */
    _setupTimePicker: function() {
        var $field = this.$(this.secondaryFieldTag),
            userTimeFormat = this.getUserTimeFormat();

        $field.timepicker({
            timeFormat: userTimeFormat,
            // FIXME: add metadata driven support for the following properties
            scrollDefaultNow: true,
            step: 15
        });

        $field.attr('placeholder', this.getTimePlaceHolder(userTimeFormat));

        if (this.def.required) {
            this.setRequiredPlaceholder($field);
        }
    },

    /**
     * Handle date and time picker changes.
     *
     * All parameters and returned value are formatted according to user
     * preferences.
     *
     * @param {String} d Date value.
     * @param {String} t Time value.
     * @return {String} Datetime value.
     */
    handleDateTimeChanges: function(d, t) {
        var now = app.date();

        d = d || (t && now.format(app.date.convertFormat(this.getUserDateFormat())));
        t = t || (d && now.format(app.date.convertFormat(this.getUserTimeFormat())));

        return (d + ' ' + t).trim();
    },

    /**
     * Date picker doesn't trigger a `change` event whenever the date value
     * changes we need to override this method and listen to the `hide` event.
     *
     * Handles `hide` date picker event expecting to set the default time if
     * not filled yet, see {@link #handleDateTimeChanges}.
     *
     * All invalid values are cleared from fields without triggering an event
     * because `this.model.set()` could have been already empty thus not
     * triggering a new event and not calling the default code of
     * `bindDomChange()`.
     *
     * @override
     */
    handleHideDatePicker: function() {
        var $timeField = this.$(this.secondaryFieldTag),
            $dateField = this.$(this.fieldTag),
            t = $timeField.val(),
            d = $dateField.val(),
            datetime = this.unformat(this.handleDateTimeChanges(d, t));

        if (!datetime) {
            $timeField.val('');
            $dateField.val('');
        }

        this.model.set(this.name, datetime);
    },

    /**
     * {@inheritDoc}
     *
     * Bind time picker `changeTime` event expecting to set the default date if
     * not filled yet, see {@link #handleDateTimeChanges}.
     */
    bindDomChange: function() {
        this._super('bindDomChange');

        var $dateField = this.$(this.fieldTag),
            $timeField = this.$(this.secondaryFieldTag);

        $timeField.timepicker().on({
            change: _.bind(function() {
                var t = $timeField.val().trim(),
                    datetime = '';

                if (t) {
                    var d = $dateField.val();
                    datetime = this.unformat(this.handleDateTimeChanges(d, t));
                }

                if (!datetime) {
                    $timeField.val('');
                    $dateField.val('');
                }

                this.model.set(this.name, datetime);
            }, this)
        });
    },

    /**
     * {@inheritDoc}
     *
     * Add extra logic to unbind secondary field tag.
     */
    unbindDom: function() {
        this._super('unbindDom');

        this.$(this.secondaryFieldTag).off();
    },

    /**
     * Binds model changes on this field, taking into account both field tags.
     *
     * @override
     */
    bindDataChange: function() {
        if (!this.model) {
            return;
        }

        this.model.on('change:' + this.name, function(model, value) {
            if (this.action !== 'edit') {
                this.render();
                return;
            }

            value = this.format(value) || {};

            this.$(this.fieldTag).val(value['date']);
            this.$(this.secondaryFieldTag).val(value['time']);
        }, this);
    },

    /**
     * Formats date value according to user preferences.
     *
     * If no value is defined, we {@link #_setDefaultValue set a default value}.
     *
     * @param {String} value Datetime value to format.
     * @return {Object/String/undefined} On edit mode the returned value is an
     *   object with two keys, `date` and `time`. On detail mode the returned
     *   value is a date, formatted according to user preferences if supplied
     *   value is a valid date, otherwise returned value is undefined.
     *
     * @override
     */
    format: function(value) {
        value = value || this._setDefaultValue();

        if (!value) {
            return value;
        }

        value = app.date(value);

        if (!value.isValid()) {
            return;
        }

        if (this.action === 'edit') {
            value = {
                'date': value.format(app.date.convertFormat(this.getUserDateFormat())),
                'time': value.format(app.date.convertFormat(this.getUserTimeFormat()))
            };

        } else {
            value = value.formatUser();
        }

        return value;
    },

    /**
     * Unformats datetime value for storing in model.
     *
     * @return {String} Unformatted value.
     *
     * @override
     */
    unformat: function(value) {
        if (!value) {
            return value;
        }

        value = app.date(value, app.date.convertFormat(this.getUserDateTimeFormat()), true);

        if (!value.isValid()) {
            return;
        }

        return value.format();
    },

    /**
     * {@inheritDoc}
     *
     * If current device is a touch device, time picker is readonly.
     */
    _render: function() {
        this._super('_render');

        if (this.action !== 'edit') {
            return;
        }

        this._setupTimePicker();

        if (app.utils.isTouchDevice()) {
            this.$(this.secondaryFieldTag).attr('readonly', true);
        }
    },

    /**
     * {@inheritdoc}
     */
    _dispose: function() {
        this.$(this.secondaryFieldTag).timepicker('remove');

        this._super('_dispose');
    }
})
