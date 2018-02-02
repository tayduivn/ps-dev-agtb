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
 * @class View.Views.Base.PiiView
 * @alias SUGAR.App.view.views.BasePiiView
 * @extends View.Views.Base.FilteredListView
 */
({
    extendsFrom: 'FilteredListView',

    fallbackFieldTemplate: 'list-header',

    /**
     * @inheritdoc
     * Initialize and override the Pii collection.
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.baseModule = this.context.get('pModule');
        this.baseRecord = this.context.get('pId');
        if (!this.collection) {
            this._initCollection();
        }
    },

    /**
     * Initialize the collection.
     * @private
     */
    _initCollection: function() {
        var PiiCollection = app.BeanCollection.extend({
            baseModule: this.baseModule,
            baseRecordId: this.baseRecord,
            sync: function(method, model, options) {
                var url = app.api.buildURL(this.baseModule, 'pii', {id: this.baseRecordId}, options.params);
                var callbacks = app.data.getSyncCallbacks(method, model, options);
                var defaultSuccessCallback = app.data.getSyncSuccessCallback(method, model, options);
                callbacks.success = function(data, request) {
                    data.records = data.fields;
                    return defaultSuccessCallback(data, request);
                };
                app.api.call(method, url, options.attributes, callbacks);
            }
        });
        this.collection = new PiiCollection();
    },

    /**
     * @inheritdoc
     */
    loadData: function() {
        if (this.collection.dataFetched) {
            return;
        }
        this.collection.fetch();
    },

    /**
     * @inheritdoc
     *
     * Patch pii models fields with information of
     * original field available within parent model, in order to render
     * properly.
     */
    _renderData: function() {
        var fields = app.metadata.getModule(this.baseModule).fields;

        _.each(this.collection.models, function(model) {
            model.fields = app.utils.deepCopy(this.metaFields);

            var value = _.findWhere(model.fields, {name: 'value'});
            _.extend(value, fields[model.get('field_name')], {name: 'value'});
            if (_.contains(['multienum', 'enum'], value.type) && value.function) {
                value.type = 'base';
            }

            model.fields = app.metadata._patchFields(
                this.module,
                app.metadata.getModule(this.module),
                model.fields
            );
        }, this);

        this._super('_renderData');
    },

    /**
     * @override
     *
     * Overriding to return Pii view metadata, so filteredListView
     * can properly initialize filter when vardef is not available.
     */
    getFields: function() {
        return this._super('getFields', ['Pii']);
    },
})
