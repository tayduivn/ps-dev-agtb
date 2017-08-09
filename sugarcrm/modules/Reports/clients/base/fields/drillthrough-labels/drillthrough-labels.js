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
 * @class View.Fields.Base.Reports.DrillthroughLabelsField
 * @alias SUGAR.App.view.fields.BaseReportsDrillthroughLabelsField
 * @extends View.Fields.Base.BaseField
 */
({

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.context.on('refresh:drill:labels', this.render, this);
    },

    /**
     * @override We want to grab the data from the context, not the model
     */
    format: function(value) {
        var filterDef = this.context.get('filterDef');
        var params = this.context.get('dashConfig');

        //TODO: should this be moved outside?
        function getValues(filter, label) {
            var key = getKey(filter);
            var values = filter[key];
            var dateLabels = ['year', 'quarter', 'month', 'week', 'fiscalYear', 'fiscalQuarter'];

            // if datetime values, format differently
            if (values.length === 3 && dateLabels.indexOf(values[2]) !== -1) {
                return label;
            } else {
                return values.join(', ');
            }
        }

        function getKey(filter) {
            return _.keys(filter)[0];
        }

        //TODO: should this be moved outside?
        function parseField(field) {
            var parsedField = {};
            var position = field.indexOf(':');
            var position2 = field.indexOf(':', position + 1);

            parsedField.tableKey = field.substring(0, position);

            if (position2 !== -1) {
                // Expected field format is like Accounts:assigned_user_link:user_name
                parsedField.link = field.substring(position + 1, position2);
                parsedField.name = field.substring(position2 + 1);
            } else {
                // Expected field format is like Accounts:industry
                parsedField.name = field.substring(position + 1);
            }

            return parsedField;
        }

        var group = _.first(filterDef);
        var groupField = parseField(getKey(group));
        this.groupLabel = this._getLabel(groupField) + ': ';
        this.groupValue = getValues(group, params.groupLabel);

        if (filterDef.length > 1) {
            var series = _.last(filterDef);
            var seriesField = parseField(getKey(series));
            this.seriesLabel = this._getLabel(seriesField) + ': ';
            this.seriesValue = getValues(series, params.seriesLabel);
        }

        // returns nothing
        return value;
    },

    /**
     * Take the field info and get the translated label for the field
     *
     * @param {Object} field
     * @return {string} Translated field name or fallback to system field name
     * @private
     */
    _getLabel: function(field) {
        var chartModule = this.context.get('chartModule');
        var fieldsMeta = app.metadata.getModule(chartModule, 'fields');

        if (field.link) {
            // Handle the Module:relationshipLink:fieldName format
            var fieldDef = fieldsMeta[field.link];
            var relatedFieldsMeta = app.metadata.getModule(fieldDef.module || chartModule, 'fields');
            var relatedFieldDef = relatedFieldsMeta[field.name];
            return relatedFieldDef && relatedFieldDef.vname ?
                app.lang.get(relatedFieldDef.vname, fieldDef.module) : field.name;
        } else {
            // Handle the Module:fieldName format
            var fieldDef = fieldsMeta[field.name];
            return fieldDef && fieldDef.vname ? app.lang.get(fieldDef.vname, chartModule) : field.name;
        }
    }
})
