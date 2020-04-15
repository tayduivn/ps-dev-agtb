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
 * @class View.Fields.Base.ConsoleConfiguration.AvailableFieldListField
 * @alias SUGAR.App.view.fields.BaseConsoleConfigurationAvailableFieldListField
 * @extends View.Fields.Base.BaseField
 */
({
    /**
     * Fields with these names should not be displayed in fields list.
     */
    ignoredNames: ['deleted', 'mkto_id', 'googleplus'],

    /**
     * Fields with these types should not be displayed in fields list.
     */
    ignoredTypes: ['id', 'link'],

    /**
     * Here are stored all available fields for all available tabs.
     */
    availableFieldLists: [],

    /**
     * List of fields that are displayed for a given module.
     */
    currentAvailableFields: [],

    /**
     * @inheritdoc
     *
     * Collects all supported fields for all available modules and sets the module specific fields to be displayed.
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        var moduleName = this.model.get('enabled_module');

        this.setAvailableFields(moduleName);
        this.currentAvailableFields = this.availableFieldLists;
    },

    /**
     * Sets the available fields for the requested module.
     *
     * @param {string} moduleName The selected module name from the available modules.
     */
    setAvailableFields: function(moduleName) {
        var allFields = app.metadata.getModule(moduleName, 'fields');
        var multiLineList = app.metadata.getView(moduleName, 'multi-line-list');
        var multiLineFields = this.getSelectedFields(_.first(multiLineList.panels).fields);
        this.availableFieldLists = [];

        _.each(allFields, function(field) {
            if (this.isFieldSupported(field, multiLineFields)) {
                this.availableFieldLists.push({
                    'name': field.name,
                    'label': app.lang.get(field.label || field.vname, moduleName)
                });
            }
        }, this);
    },

    /**
     * Parse metadata and return array of fields that are already defined in the metadata.
     *
     * @param {Array} multiLineFields List of fields that appear on the multi-line list view.
     * @return {Array} True if the field is already in, false otherwise.
     */
    getSelectedFields: function(multiLineFields) {
        var fields = [];
        _.each(multiLineFields, function(column) {
            _.each(column.subfields, function(subfield) {
                // if widget_name exists, it's a special field, use widget_name instead of name
                fields.push({'name': subfield.widget_name || subfield.name});
            }, this);
        }, this);
        return fields;
    },

    /**
     * Restricts specific fields to be shown in available fields list.
     *
     * @param {Object} field Field to be verified.
     * @param {Array} multiLineFields List of fields that appear on the multi-line list view.
     * @return {boolean} True if field is supported, false otherwise.
     */
    isFieldSupported: function(field, multiLineFields) {
        // Specified fields names should be ignored.
        if (!field.name || _.contains(this.ignoredNames, field.name)) {
            return false;
        }

        // Specified field types should be ignored.
        if (_.contains(this.ignoredTypes, field.type) || field.dbType === 'id') {
            return false;
        }

        // Multi-line list view fields should not be displayed.
        if (_.findWhere(multiLineFields, {'name': field.name})) {
            return false;
        }

        return !this.hasNoStudioSupport(field);
    },

    /**
     * Verify if fields do not have available studio support.
     * Studio fields have multiple value types (array, bool, string, undefined).
     *
     * @param {Object} field Field selected to get verified.
     * @return {boolean} True if there is no support, false otherwise.
     */
    hasNoStudioSupport: function(field) {
        // if it's a special field, do not check studio attribute
        if (!_.isUndefined(field.type) && field.type === 'widget') {
            return false;
        }

        var studio = field.studio;
        if (!_.isUndefined(studio)) {
            if (studio === 'false' || studio === false) {
                return true;
            }

            if (!_.isUndefined(studio.listview)) {
                if (studio.listview === 'false' || studio.listview === false) {
                    return true;
                }
            }
        }
        return false;
    },
})
