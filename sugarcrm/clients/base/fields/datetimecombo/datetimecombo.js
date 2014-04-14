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
     * @override
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
                app.date.convertFormat(this.getUserDateTimeFormat())
            )
        );

        this.model.set(this.name, value);
        this.model.setDefaultAttribute(this.name, value);

        return this;
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
     *
     * @protected
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

            value = this.format(value) || {'date': '', 'time': ''};

            this.$(this.fieldTag).val(value['date']);
            this.$(this.secondaryFieldTag).val(value['time']);
        }, this);
    },

    /**
     * Formats date value according to user preferences.
     *
     * @param {String} value Datetime value to format.
     * @return {Object/String/undefined} On edit mode the returned value is an
     *   object with two keys, `date` and `time`. On detail mode the returned
     *   value is a date, formatted according to user preferences if supplied
     *   value is a valid date, otherwise returned value is `undefined`.
     *
     * @override
     */
    format: function(value) {
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
            value = value.formatUser(false);
        }

        return value;
    },

    /**
     * Unformats datetime value for storing in model.
     *
     * @return {String} Unformatted value or `undefined` if value is
     *   an invalid date.
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
     * Override decorateError to take into account the two fields.
     *
     * @override
     */
    decorateError: function (errors) {
        var ftag = this.fieldTag || '',
            $ftag = this.$(ftag),
            errorMessages = [],
            $tooltip;

        // Add error styling
        this.$el.closest('.record-cell').addClass('error');
        this.$el.addClass('error');

        if (_.isString(errors)) {
            // A custom validation error was triggered for this field
            errorMessages.push(errors);
        } else {
            // For each error add to error help block
            _.each(errors, function (errorContext, errorName) {
                errorMessages.push(app.error.getErrorString(errorName, errorContext));
            });
        }

        $ftag.parent().addClass('error');

        $tooltip = [$(this.exclamationMarkTemplate(errorMessages)), $(this.exclamationMarkTemplate(errorMessages))];

        var self = this;

        $ftag.parent().children('input').each(function(index) {
            $(this).after($tooltip[index]);
            self.createErrorTooltips($tooltip[index]);
        });
    },

    /**
     * {@inheritDoc}
     */
    _render: function() {
        this._super('_render');

        if (this.action !== 'edit') {
            return;
        }

        this._setupTimePicker();
    },

    /**
     * {@inheritdoc}
     */
    _dispose: function() {
        if (this.$(this.secondaryFieldTag).timepicker) {
            this.$(this.secondaryFieldTag).timepicker('remove');
        }

        this._super('_dispose');
    }
})
