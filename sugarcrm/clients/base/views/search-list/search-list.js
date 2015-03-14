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
 * List view for the {@link View.Layouts.Base.SearchLayout
 * Search layout}.
 *
 * @class View.Views.Base.SearchListView
 * @alias SUGAR.App.view.views.BaseSearchListView
 * @extends View.View
 */
({
    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        /**
         * The fields metadata for this view per module.
         *
         * @property
         * @private
         */
        this._fieldsMeta = {};
    },

    /**
     * Parses models when collection resets and renders the view.
     *
     * @override
     */
    bindDataChange: function() {
        this.collection.on('reset', function() {
            if (this.disposed) {
                return;
            }
            this.parseModels(this.collection.models);
            this.render();
        }, this);
    },

    /**
     * Parses models to generate primary fields and secondary fields based on
     * the metadata and data sent by the globalsearch API. This is used to
     * render them properly in the template.
     *
     * @param {Data.Bean[]} models The models to parse.
     */
    parseModels: function(models) {
        _.each(models, function(model) {
            var moduleMeta = this._getFieldsMeta(model.module);

            model.primaryFields = this._highlightFields(model, moduleMeta.primaryFields);
            model.secondaryFields = this._highlightFields(model, moduleMeta.secondaryFields, true);

            model.primaryFields = this._sortHighlights(model.primaryFields);
            model.secondaryFields = this._sortHighlights(model.secondaryFields);

            model.rowactions = moduleMeta.rowactions;
        }, this);
    },

    /**
     * Gets the view metadata from the given module, patches it to distinguish
     * primary fields from secondary fields and disables the native inline
     * ellipsis feature of fields.
     *
     * @param {string} module The module to get the metadata from.
     * @return {Object} The metadata object.
     * @private
     */
    _getFieldsMeta: function(module) {
        if (this._fieldsMeta[module]) {
            return this._fieldsMeta[module];
        }
        var fieldsMeta = this._fieldsMeta[module] = {};
        var meta = _.extend({}, this.meta, app.metadata.getView(module, 'search-list'));
        _.each(meta.panels, function(panel) {
            if (panel.name === 'primary') {
                fieldsMeta.primaryFields = this._setFieldsCategory(panel.fields, 'primary');
            } else if (panel.name === 'secondary') {
                fieldsMeta.secondaryFields = this._setFieldsCategory(panel.fields, 'secondary');
            }
        }, this);
        fieldsMeta.rowactions = meta.rowactions;

        return fieldsMeta;
    },

    /**
     * Converts a hash of field names and their definitions into an array of
     * field definitions sorted such as:
     *
     *  - avatar field(s) is(are) first (in theory there should be only one),
     *  - highlighted fields are second,
     *  - non highlighted fields are third.
     *
     * @param {Object} fieldsObject The object to transform.
     * @return {Array} fieldsArray The sorted array of objects.
     * @private
     */
    _sortHighlights: function(fieldsObject) {
        var fieldsArray = _.values(fieldsObject);
        fieldsArray = _.sortBy(fieldsArray, function(field) {
            if (field.type === 'avatar') {
                return 0;
            }
            return field.highlighted ? 1 : 2;
        });
        return fieldsArray;
    },

    /**
     * Sets `primary` or `secondary` boolean to fields. Also, we set the
     * `ellipsis` flag to `false` so that the field doesn't render in a div with
     * the `ellipsis_inline` class.
     *
     * @param {Object} fields The fields.
     * @param {String} category The field category. It can be `primary` or
     *   `secondary`.
     * @return {Object} The enhanced fields object.
     * @private
     */
    _setFieldsCategory: function(fields, category) {
        var fieldList = {};

        _.each(fields, function(field) {
            if (!fieldList[field.name]) {
                fieldList[field.name] = _.extend({}, fieldList[field.name], field);
            }
            fieldList[field.name][category] = true;
            fieldList[field.name].ellipsis = false;
        });

        return fieldList;
    },

    /**
     * Adds `highlighted` attribute to fields sent as `highlights` by the
     * globalsearch API for a given model.
     *
     * This method clones viewdefs fields and replace them by
     * the highlighted fields sent by the API.
     *
     * @param {Data.Bean} model The model.
     * @param {Object} viewDefs The view definitions of the fields.
     *   Could be definition of primary fields or secondary fields.
     * @param {boolean} [add=false] `true` to add in the viewdefs the highlighted
     *   fields if they don't already exist. `false` to skip them if they don't
     *   exist in the viewdefs.
     * @private
     */
    _highlightFields: function(model, viewDefs, add) {
        //The array of highlighted fields
        var highlighted = model.get('_highlights');
        //The fields vardefs of the model.
        var varDefs = model.fields;
        viewDefs = _.clone(viewDefs);

        _.each(highlighted, function(field) {
            var x = viewDefs[field.name]; // covers patching existing.
            var y = add; // covers adding in case it doesn't exist.
            var addOrPatchExisting = (x || y); // shall we proceed.

            // We want to patch the field def only if there is an existing
            // viewdef for this field or if we want to add it if it doesn't exist
            // (This is the case for secondary fields).
            if (!addOrPatchExisting) {
                return;
            }
            // Checks if the model has the field in its primary fields, if it
            // does, we don't patch the field def because we don't want it to
            // be in both secondary and primary fields.
            if (!_.isUndefined(model.primaryFields) && model.primaryFields[field.name]) {
                return;
            }
            viewDefs[field.name] = _.extend({}, varDefs[field.name], viewDefs[field.name], field);
        });
        return viewDefs;
    }
})
