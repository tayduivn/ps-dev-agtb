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
 * @class View.Fields.Base.BoolField
 * @alias SUGAR.App.view.fields.BaseBoolField
 * @extends View.Field
 */
({
    /**
     * HTML tag of the select2 field.
     *
     * @property {String}
     */
    select2fieldTag: 'select',

    /**
     * {@inheritDoc}
     *
     * Renders both checkbox and dropdown
     */
    _render: function() {
        this._super('_render');
        this.$(this.select2fieldTag).select2({'minimumResultsForSearch': -1});
    },

    /**
     * {@inheritDoc}
     */
    _getFallbackTemplate: function(viewName) {
        if (viewName === 'massupdate') {
            return 'dropdown';
        }
        return this._super('_getFallbackTemplate', [viewName]);
    },

    /**
     * {@inheritDoc}
     */
    bindDomChange: function() {
        var $el = this.$(this.select2fieldTag);
        if (!$el.length) {
            $el = this.$(this.fieldTag);
        }
        $el.on('change', _.bind(function() {
            var value = $el.is(this.select2fieldTag) ? $el.val() : $el.prop('checked');
            this.model.set(this.name, this.unformat(value));
        }, this));
    },

    /**
     * {@inheritDoc}
     *
     * Bypass `render` when action is `massupdate` or `edit`.
     */
    bindDataChange: function() {
        if (!this.model) {
            return;
        }

        this.model.on('change:' + this.name, function(model, value) {
            if (this.action === 'massupdate') {
                this.$(this.select2fieldTag).val(this.format(value) ? '1' : '0');
            } else if (this.action === 'edit') {
                this.$(this.fieldTag).prop('checked', this.format(value));
            } else {
                this.render();
            }
        }, this);
    },

    /**
     * {@inheritDoc}
     */
    unbindDom: function() {
        this.$(this.select2fieldTag).off();
        this._super('unbindDom');
    },

    /**
     * {@inheritDoc}
     *
     * @param {String/Boolean} value The value to unformat.
     * @return {Boolean} Unformatted value.
     */
    unformat: function(value) {
        if (_.isString(value)) {
            value = value == '1';
        }
        return value;
    },

    /**
     * {@inheritDoc}
     *
     * @param {String/Boolean} value The value to format.
     * @return {Boolean} formatted value.
     */
    format: function(value) {
        if (_.isString(value)) {
            value = value == '1';
        }
        return value;
    }
})
