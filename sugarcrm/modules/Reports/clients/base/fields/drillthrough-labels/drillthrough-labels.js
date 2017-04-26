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

        function getValues(filter) {
            var key = getKey(filter);
            var values = filter[key];
            return values.join(', ');
        }

        function getKey(filter) {
            return _.keys(filter)[0];
        }

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

        this.groupTitle = this._getTitle(groupField) + ': ';
        this.group = getValues(group);

        if (filterDef.length > 1) {
            var series = _.last(filterDef);
            var seriesField = parseField(getKey(series));
            this.seriesTitle = this._getTitle(seriesField) + ': ';
            this.series = getValues(series);
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
    _getTitle: function(field) {
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
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        this._super('_render');
        // Fix a bug where border is 2 pixels lower when labels contain a group and a series
        if (!_.isUndefined(this.series)) {
            this.$el.parents('.record-cell').css('padding-top', '16px');
        }
    }
})
