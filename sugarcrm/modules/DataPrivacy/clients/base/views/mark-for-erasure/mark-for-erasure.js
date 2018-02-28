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
 * @class View.Views.Base.DataPrivacy.MarkForErasureView
 * @alias SUGAR.App.view.views.BaseDataPrivacyMarkForErasureView
 * @extends View.Views.Base.PiiView
 */
({
    extendsFrom: 'PiiView',

    fallbackFieldTemplate: 'list-header',

    /**
     * @inheritdoc
     * Initialize and override the Pii collection.
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.baseModule = this.context.get('modelForErase').module;
        this.baseRecord = this.context.get('modelForErase').id;
        this.context.set('piiModule', this.baseModule);
        if (this.collection.length === 0) {
            this._initCollection();
        }
    },
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
                app.metadata.getModule(this.baseModule),
                model.fields
            );
        }, this);

        this._super('_renderData');
    },
})
