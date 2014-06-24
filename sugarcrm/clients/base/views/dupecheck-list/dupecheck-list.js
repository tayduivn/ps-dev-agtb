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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.DupecheckListView
 * @alias SUGAR.App.view.views.BaseDupecheckListMenuView
 * @extends View.Views.Base.FlexListView
 */
({
    extendsFrom: 'FlexListView',
    plugins: ['ListColumnEllipsis', 'ListDisableSort', 'ListRemoveLinks', 'Pagination'],
    collectionSync: null,
    additionalTableClasses: null,

    /**
     * @inheritDoc
     *
     * The metadata used is the default `dupecheck-list` metadata, extended by
     * the module specific `dupecheck-list` metadata, extended by subviews
     * metadata.
     */
    initialize: function(options) {
        var dupeListMeta = app.metadata.getView(null, 'dupecheck-list') || {},
            moduleMeta = app.metadata.getView(options.module, 'dupecheck-list') || {};

        options.meta = _.extend({}, dupeListMeta, moduleMeta, options.meta || {});

        this._super('initialize', [options]);
        this.context.on('dupecheck:fetch:fire', this.fetchDuplicates, this);
    },

    /**
     * @inheritDoc
     */
    bindDataChange: function() {
        this.collection.on('reset', function() {
            this.context.trigger('dupecheck:collection:reset');
        }, this);
        this._super('bindDataChange');
    },

    /**
     * @inheritDoc
     */
    _renderHtml: function() {
        var classesToAdd = 'duplicates highlight';
        this._super('_renderHtml');
        if (this.additionalTableClasses) {
            classesToAdd = classesToAdd + ' ' + this.additionalTableClasses;
        }
        this.$('table.table-striped').addClass(classesToAdd);
    },

    /**
     * Fetch the duplicate collection.
     *
     * @param {Backbone.Model} model Duplicate check model.
     * @param {Object} options Fetch options.
     */
    fetchDuplicates: function(model, options) {
        this.collection.dupeCheckModel = model;
        this.collection.fetch(options);
    }
})
