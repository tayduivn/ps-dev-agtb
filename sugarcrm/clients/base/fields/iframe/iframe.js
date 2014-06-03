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
    _render: function() {
        this._super('_render');
        if (this.tplName === 'disabled') {
            this.$(this.fieldTag).attr('disabled', 'disabled');
        }
    },

    /**
     * {@inheritDoc}
     */
    unformat: function(value) {
        value = (value !== '' && value != 'http://') ? value.trim() : '';
        return value;
    },

    /**
     * {@inheritDoc}
     *
     * Formatter for the iframe field. If the iframe field definition is
     * configured with a generated url (`this.def.gen`) by another field, those
     * field values (defined in curly braces) are parsed from the model and set
     * on the value to be returned. Finally, if the value doesn't contain
     * 'http://' or 'https://', it is prepended on the value before being
     * returned.
     *
     * @param {String} value The value set on the iframe field.
     * @return {String} The formatted iframe value.
     */
    format: function(value) {
        if (_.isEmpty(value)) {
            // Name conflict with iframe's default value def and the list view's
            // default column flag
            value = _.isString(this.def['default']) ? this.def['default'] : undefined;
        }

        if (this.def.gen == '1') {
            var regex = /{(.+?)}/,
                result = null;
            do {
                result = regex.exec(value);
                if (result) {
                    value = value.replace(result[0], this.model.get(result[1]));
                }
            } while (result);
        }

        if (_.isString(value) && !value.match(/^(http|https):\/\//)) {
            value = 'http://' + value.trim();
        }
        return value;
    }
})
