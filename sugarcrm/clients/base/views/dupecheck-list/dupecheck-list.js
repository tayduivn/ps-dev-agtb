/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    /**
     * @class View.DupecheckListView
     * @alias SUGAR.App.view.views.DupecheckListView
     * @extends View.FlexListView
     */
    extendsFrom: 'FlexListView',
    plugins: ['ellipsis_inline', 'list-column-ellipsis', 'list-disable-sort', 'list-remove-links'],
    collectionSync: null,
    displayFirstNColumns: 4,
    additionalTableClasses: null,

    initialize: function(options) {
        //use dupecheck-list metadata by default - subviews will just extend
        var dupeListMeta = app.metadata.getView(options.module, 'dupecheck-list') || {};
        options.meta = _.extend({}, dupeListMeta, options.meta || {});

        app.view.invokeParent(this, {type: 'view', name: 'flex-list', method: 'initialize', args:[options]});

        this.context.on("dupecheck:fetch:fire", this.fetchDuplicates, this);

        if (this.context.has('dupeCheckModel')) {
            this.model = this.context.get('dupeCheckModel');
        }

        // Create an empty collection if it doesn't exist, since we need it for sync
        if(_.isUndefined(this.collection)){
            this.collection = app.data.createBeanCollection(this.module);
        }

        //save off the collection's sync so we can run our own and then run the original
        //this is so we can switch the endpoint out
        this.collectionSync = this.collection.sync;
        this.collection.sync = _.bind(this.sync, this);
    },

    _renderHtml: function() {
        var classesToAdd = 'duplicates highlight';
        app.view.invokeParent(this, {type: 'view', name: 'flex-list', method: '_renderHtml'});
        if (this.additionalTableClasses) {
            classesToAdd = classesToAdd + ' ' + this.additionalTableClasses;
        }
        this.$('table.table-striped').addClass(classesToAdd);
    },

    sync: function(method, model, options) {
        options = options || {};
        options.endpoint = _.bind(this.endpoint, this);
        this.collectionSync(method, model, options);
    },

    endpoint: function(method, model, options, callbacks) {
        var url = app.api.buildURL(this.module, "duplicateCheck");
        return app.api.call('create', url, this.model.attributes, callbacks); //Dupe Check API requires POST
    },

    fetchDuplicates: function(model, options) {
        this.model = model;
        this.collection.fetch(options);
    }
})
