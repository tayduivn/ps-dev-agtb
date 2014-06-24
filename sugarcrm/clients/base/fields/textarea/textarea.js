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
 * @class View.Fields.Base.TextareaField
 * @alias SUGAR.App.view.fields.BaseTextareaField
 * @extends View.Field
 */
({
    /**
     * {@inheritDoc}
     */
    fieldTag : 'textarea',

    /**
     * Default settings used when none are supplied through metadata.
     *
     * Supported settings:
     * - {Number} max_display_chars The maximum number of characters to be
     *   displayed before truncating the field.
     * - {Boolean} collapsed Defines whether or not the textarea detail view
     *   should be collapsed on initial render.
     *
     *     @example
     *     ```
     *     // ...
     *     'settings' => array(
     *         'max_display_chars' => 50,
     *         'collapsed' => false
     *         //...
     *     ),
     *     //...
     *     ```
     *
     * @protected
     * @type {Object}
     */
    _defaultSettings: {
        max_display_chars: 450,
        collapsed: true
    },

    /**
     * State variable that keeps track of whether or not the textarea field
     * is collapsed in detail view.
     *
     * @type {Boolean}
     */
    collapsed: undefined,

    /**
     * Settings after applying metadata settings on top of
     * {@link View.Fields.BaseTextareaField#_defaultSettings default settings}.
     *
     * @protected
     */
    _settings: {},

    /**
     * {@inheritDoc}
     */
    plugins: ['EllipsisInline'],

    /**
     * {@inheritDoc}
     */
    events: {
        'click [data-action=toggle]': 'toggleCollapsed'
    },

    /**
     * {@inheritDoc}
     *
     * Initializes settings on the field by calling
     * {@link View.Fields.BaseTextareaField#_initSettings _initSettings}.
     * Also sets {@link View.Fields.BaseTextareaField#collapsed collapsed}
     * to the value in `this._settings.collapsed` (either default or metadata).
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this._initSettings();
        this.collapsed = this._settings.collapsed;
    },

    /**
     * Initialize settings, default settings are used when none are supplied
     * through metadata.
     *
     * @return {View.Fields.BaseTextareaField} Instance of this field.
     * @protected
     */
    _initSettings: function() {
        this._settings = _.extend({},
            this._defaultSettings,
            this.def && this.def.settings || {}
        );
        return this;
    },

    /**
     * {@inheritDoc}
     *
     * Prevents editing the textarea field in a list view.
     *
     * @param {String} name The mode to set the field to.
     */
    setMode: function(name) {
        // FIXME: This will be updated pending changes to fields in sidecar,
        // see SC-2608, SC-2776.
        var mode = (this.action === 'list') && _.contains(['edit', 'disabled'], name) ? this.action : name;
        this._super('setMode', [mode]);
    },

    /**
     * {@inheritDoc}
     *
     * Formatter that always returns the value set on the textarea field. Sets
     * a `short` value for a truncated representation, if the lenght of the
     * value on the field exceeds that of `max_display_chars`. The return value
     * can either be a string, or an object such as {long: 'abc'} or
     * {long: 'abc', short: 'ab'}, for example.
     *
     * @param {String} value The value set on the textarea field.
     * @return {String|Object} The value set on the textarea field.
     */
    format: function(value) {
        //Format for the detail template if the action is detail, or if the current action is disabled but the parent
        //action is detail (for use with copy fields that are disabled, but show up on the detail view)
        if ((this.action === 'detail') ||
            (this.action === 'disabled' && (!this.parent || this.parent.action === 'detail'))) {
            var max = this._settings.max_display_chars;
            value = {long: value};

            if (value.long && value.long.length > max) {
                value.short = value.long.substr(0, max).trim();
            }
        }
        return value;
    },

    /**
     * Toggles the field between displaying the truncated `short` or `long`
     * value for the field, and toggles the label for the 'more/less' link.
     */
    toggleCollapsed: function() {
        this.collapsed = !this.collapsed;
        this.render();
    }
})
