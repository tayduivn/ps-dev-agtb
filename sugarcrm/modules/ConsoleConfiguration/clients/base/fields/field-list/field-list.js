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
 * @class View.Fields.Base.ConsoleConfiguration.FieldListField
 * @alias SUGAR.App.view.fields.BaseConsoleConfigurationFieldListField
 * @extends View.Fields.Base.BaseField
 */
({
    /**
     * The field properties to get from multi-line-list
     */
    whitelistedProperties: [
        'name',
        'label',
        'widget_name',
    ],

    /**
     * Fields mapped to their subfields
     */
    mappedFields: {},

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.mappedFields = this.getMappedFields();
    },

    /**
     * @inheritdoc
     *
     * Overrides the parent bindDataChange to make sure this field is re-rendered
     * when the config is reset
     */
    bindDataChange: function() {
        if (this.model) {
            this.context.on('consoleconfig:reset:defaultmetarelay', function() {
                // the default meta data is ready, use it to re-render
                var defaultViewMeta = this.context.get('defaultViewMeta');
                var moduleName = this.model.get('enabled_module');
                if (!_.isEmpty(defaultViewMeta) && !_.isEmpty(defaultViewMeta[moduleName])) {
                    this.mappedFields = this.getMappedFields();
                    this.context.set('defaultViewMeta', null);
                    this.render();
                }
            }, this);
        }
    },

    /**
     * Return the proper view metadata.
     *
     * @param {string} moduleName The selected module name from the available modules.
     */
    getViewMetaData: function(moduleName) {
        // If defaultViewMeta exists, it means we are restoring the default settings.
        var defaultViewMeta = this.context.get('defaultViewMeta');
        if (!_.isEmpty(defaultViewMeta) && !_.isEmpty(defaultViewMeta[moduleName])) {
            return this.context.get('defaultViewMeta')[moduleName];
        }

        // Not restoring defaults, use the regular view meta data
        return app.metadata.getView(moduleName, 'multi-line-list');
    },

    /**
     * Gets the module's multi-line list fields from the model with the parent field mapping
     *
     * @return {Object} the fields
     */
    getMappedFields: function() {
        var tabContentFields = {};

        var multiLineMeta = this.getViewMetaData(this.model.get('enabled_module'));

        _.each(multiLineMeta.panels, function(panel) {
            _.each(panel.fields, function(fieldDefs) {
                var subfields = [];
                _.each(fieldDefs.subfields, function(subfield) {
                    var parsedSubfield = _.pick(subfield, this.whitelistedProperties);

                    // if label does not exist, get it from the parent's vardef
                    if (!_.has(parsedSubfield, 'label')) {
                        parsedSubfield.label = this.model.fields[parsedSubfield.name].label ||
                            this.model.fields[parsedSubfield.name].vname;
                    }

                    parsedSubfield.parent_name = fieldDefs.name;
                    parsedSubfield.parent_label = fieldDefs.label;

                    if (_.has(parsedSubfield, 'widget_name')) {
                        parsedSubfield.name = parsedSubfield.widget_name;
                    }

                    subfields = subfields.concat(parsedSubfield);
                }, this);

                tabContentFields[fieldDefs.name] = _.has(tabContentFields, fieldDefs.name) ?
                    tabContentFields[fieldDefs.name].concat(subfields) : subfields;
            }, this);
        }, this);

        return tabContentFields;
    },
})
