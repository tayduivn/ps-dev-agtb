/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * EnumColorcodedField is an enum field that sets a background color
 * based on its value.
 *
 * @class View.Fields.Base.EnumColorcodedField
 * @alias SUGAR.App.view.fields.BaseEnumColorcodedField
 * @extends View.Fields.Base.EnumField
 */
({
    extendsFrom: 'EnumField',

    /**
     * List of additional CSS classes to apply when showing a colored pill.
     *
     * @type string[]
     * @private
     */
    _defaultExtraClasses: ['label', 'pill'],

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        /**
         * Map of enum keys to CSS class name(s).
         *
         * @type {Object}
         * @private
         */
        this._colorCodeClasses = this.def.color_code_classes || {};
    },

    /**
     * @inheritdoc
     *
     * Listen for a change to this field's value and set color coding
     * appropriately.
     */
    bindDataChange: function() {
        this._super('bindDataChange');
        this.model.on('change:' + this.name, this.setColorCoding, this);
    },

    /**
     * @inheritdoc
     *
     * Checks color code conditions to determine if this field should have
     * color applied to it.
     */
    _render: function() {
        this.type = 'enum'; // use enum templates
        this._super('_render');
        this.setColorCoding();
    },

    /**
     * Set color coding based on enum value.
     * This is only applied when the action is list (not inline edit on
     * list view).
     */
    setColorCoding: function() {
        this._clearColorCode();

        if (!this.model || this.action !== 'list') {
            return;
        }

        this._setColorCodeClass(this._getColorCodeClass());
    },

    /**
     * Gets color code class based on metadata.
     *
     * @return {string} One of the color codes or an empty string
     *   if no color code.
     * @private
     */
    _getColorCodeClass: function() {
        var value = this.model.get(this.name);

        if (_.isEmpty(value)) {
            return '';
        }

        return this._colorCodeClasses[value] || '';
    },

    /**
     * Set the color code class to the field tag.
     *
     * @param {string} colorCodeClass Color code class name.
     * @private
     */
    _setColorCodeClass: function(colorCodeClass) {
        if (colorCodeClass) {
            this.$el.addClass(this._defaultExtraClasses.join(' ') + ' ' + colorCodeClass);
        }
    },

    /**
     * Clear color coding classes.
     * @private
     */
    _clearColorCode: function() {
        var classes = _.union(_.values(this._colorCodeClasses), this._defaultExtraClasses).join(' ');
        this.$el.removeClass(classes);
    }
})
