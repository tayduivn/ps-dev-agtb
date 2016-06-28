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
({
extendsFrom: 'EnumField',

    /**
     * @inheritdoc
     */
    initialize: function() {
        this._super('initialize', arguments);
        this.model.addValidationTask('caldav_module_check', _.bind(this._validateModule, this));
        app.error.errorName2Keys.caldavModuleNotSelected = app.lang.get('LBL_CONFIG_ERROR_SELECT_MODULE', this.module);
    },

    /**
     * Validate caldav_module field on empty values.
     *
     * @param {Object} fields The list of fields to validate.
     * @param {Object} errors The errors object during this validation task.
     * @param {Function} callback The callback function to continue validation.
     */
    _validateModule: function(fields, errors, callback) {
        if (!this.model.get('caldav_module')) {
            errors.caldav_module = errors.caldav_module || {};
            errors.caldav_module.caldavModuleNotSelected = true;
        }
        callback(null, fields, errors);
    },

loadEnumOptions: function(fetch, callback) {
    this._super('loadEnumOptions', [fetch, callback]);

    var field_options = this.model.get(this.name + '_options');

    if (field_options) {
        this.items = field_options;
    }
}
})
