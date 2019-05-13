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
 * Contextual dashlet to show comments for a record
 *
 * @class View.Views.Base.CommentlogDashletView
 * @alias SUGAR.App.view.views.BaseCommentlogDashletView
 * @extends View.View
 */
({
    plugins: ['Dashlet'],

    /**
     * Default settings for dashlet
     */
    _defaultSettings: {
        limit: 3
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.meta = _.extend(this.meta, app.metadata.getView(null, this.name));
    },

    /**
     * Set up the comment log collection when init dashlet
     */
    initDashlet: function() {
        this.setUpCollection();
    },

    /**
     * Set up the comment log collection
     */
    setUpCollection: function() {
        this.collection = app.data.createRelatedCollection(this._getClonedModel(), 'commentlog_link');
    },

    /**
     * Get the contextual model for the dashlet
     *
     * @return {Data.Bean|undefined} The context, if it exists.
     * @private
     */
    _getContextModel: function() {
        if (this._contextModel) {
            return this._contextModel;
        }
        var model;
        var baseModule = this.context.get('module');
        var currContext = this.context;
        while (currContext) {
            var contextModel = currContext.get('rowModel') || currContext.get('model');

            if (contextModel && contextModel.get('_module') === baseModule) {
                model = contextModel;
                break;
            }

            currContext = currContext.parent;
        }
        return this._contextModel = model || app.controller.context.get('model');
    },

    /**
     * Create a new model with the id of the context model to stop changing the context model
     *
     * @return {Data.Bean}
     * @private
     */
    _getClonedModel: function() {
        var model = this._getContextModel();
        return app.data.createBean(model.module, {id: model.get('id')});
    },

    /**
     * Load the comment log collection with proper limits
     *
     * @param {string} options.loadAll `true` to load all comments
     */
    loadData: function(options) {
        var limit = options && options.loadAll ? -1 : this._defaultSettings.limit;
        this.collection.fetch({limit: limit});
    }
})
