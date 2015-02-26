/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * List view for the {@link View.Layouts.Base.GlobalSearchResultsLayout}
 * GlobalSearch Results layout.
 *
 * @class View.Views.Base.GsrListView
 * @alias SUGAR.App.view.views.BaseGsrListView
 * @extends View.View
 */
({

    /**
     * Parses models when collection resets and renders the view.
     */
    bindDataChange: function() {
        this.collection.on('reset', function() {
            this.parseModels(this.collection.models);
            this.render();
        }, this);
    },

    /**
     * Parses models to split them in primary fields and secondary fields. This
     * will be used to render then properly in the template.
     *
     * @param {Data.Bean[]} models The models to parse.
     */
    parseModels: function(models) {
        _.each(models, function(model) {
            this.setPrimaryFields(model);
            this.setSecondaryFields(model);
        }, this);
    },

    /**
     * Parses the highlighted fields array and the primary fields from the
     * metadata to separate primary fields in 2 distinct primary fields
     * arrays : `primaryFields` and `primaryHighlightedFields`. Then, sets
     * these arrays in the model.
     *
     * @param {Data.Bean} model The model on which highlighted fields are parsed.
     */
    setPrimaryFields: function(model) {
        var highlights = model.get('highlights');
        var primaryFields = _.clone(this.meta.fields.primary);
        var primaryHighlightedFields = [];

        _.each(primaryFields, function(field, index) {
            if (this._isHighlighted(field, highlights)) {
                // We remove highlighted fields from `primary fields`.
                primaryFields.splice(index, 1);
                var highlightedField = _.find(highlights, function(highlight) {
                    return highlight.name === field.name;
                });
                // Then we add the highlighted field to
                // `primary highlighted fields`
                primaryHighlightedFields.push(highlightedField);
            }
        }, this);

        model.set('primaryFields', primaryFields);
        model.set('primaryHighlightedFields', primaryHighlightedFields);
    },

    /**
     * Parses the highlighted fields array and the secondary fields from the
     * metadata to separate secondary fields in 2 distinct secondary fields
     * arrays : `secondaryFields` and `secondaryHighlightedFields`. Then, sets
     * these arrays in the model.
     *
     * @param {Data.Bean} model The model on which highlighted fields are parsed.
     */
    setSecondaryFields: function(model) {
        var highlights = model.get('highlights');
        var secondaryFields = _.clone(this.meta.fields.secondary);
        // First we set `secondary highlighted fields` as all highlighted fields.
        var secondaryHighlightedFields = highlights;

        _.each(highlights, function(highlight, index) {
            //Then we remove those which are already `primary fields`.
            if (this._isPrimary(highlight)) {
                secondaryHighlightedFields.splice(index, 1);
            }
            //Then, we remove from the `secondary fields` the highlighted ones.
            if (this._isSecondary(highlight)) {
                secondaryFields.splice(index, 1);
            }
        }, this);

        model.set('secondaryFields', secondaryFields);
        model.set('secondaryHighlightedFields', secondaryHighlightedFields);
    },

    /**
     * Checks if a field is one of the primary fields set in the metadata.
     *
     * @param {Object} fieldToCheck The field to check.
     * @return {boolean} `true` if the field is one of the primary fields,
     *   `false` otherwise.
     * @private
     */
    _isPrimary: function(fieldToCheck) {
        var primary = false;
        _.each(this.meta.fields.primary, function(field) {
            if (fieldToCheck.name === field.name) {
                primary = true;
            }
        });
        return primary;
    },

    /**
     * Checks if a field is one of the secondary fields set in the metadata.
     *
     * @param {Object} fieldToCheck The field to check.
     * @return {boolean} `true` if the field is one of the secondary fields,
     *   `false` otherwise.
     * @private
     */
    _isSecondary: function(fieldToCheck) {
        var secondary = false;
        _.each(this.meta.fields.secondary, function(field) {
            if (fieldToCheck.name === field.name) {
                secondary = true;
            }
        });
        return secondary;
    },

    /**
     * Checks if a field is one of the highlighted fields returned by
     * elasticsearch.
     *
     * @private
     * @param {Object} field The field to check.
     * @param {Object[]} highlights The array of highlighted fields.
     * @return {boolean} highlighted `true` if the field is one of the
     *   highlighted fields, `false` otherwise.
     */
    _isHighlighted: function(field, highlights) {
        var highlighted = false;
        _.each(highlights, function(highlight) {
            if (highlight.name === field.name) {
                highlighted = true;
            }
        });
        return highlighted;
    }
})
