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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    /**
     * {@inheritDoc}
     *
     * Unformats the float based on userPreferences (grouping/decimal separator).
     * If we weren't able to parse the value, the original value is returned.
     *
     * @param {String} value the formatted value based on user preferences.
     * @return {Number|String} the unformatted value, or original string if invalid.
     */
    unformat: function(value) {
        var unformattedValue = app.utils.unformatNumberStringLocale(value);
        // if unformat failed, return original value
        return _.isFinite(unformattedValue) ? unformattedValue : value;

    },

    /**
     * {@inheritDoc}
     *
     * Formats the float based on user preferences (grouping separator).
     * If the field definition has `disabled_num_format` as `true` the value
     * won't be formatted. Also, if the value isn't a finite float it will
     * return `undefined`.
     *
     * @param {Number} value the float value to format as per user preferences.
     * @return {String|undefined} the formatted value based as per user
     *   preferences.
     */
    format: function(value) {
        if (this.def.disable_num_format) {
            return value;
        }
        return app.utils.formatNumber(
            value,
            this.def.round || 4,
            this.def.precision || 4,
            app.user.getPreference('number_grouping_separator') || ',',
            app.user.getPreference('decimal_separator') || '.');
    }
})
