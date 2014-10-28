/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
({
    plugins: ['Dashlet'],

    events: {
        "click [data-action=show-more]": "loadMoreData"
    },

    /**
     * KBSContents bean collection.
     *
     * @property {Data.BeanCollection}
     */
    collection: null,

    /**
     * @inheritDoc
     */
    initialize: function (options) {
        var self = this;
        
        options.module = 'KBSContents';
        this._super('initialize', [options]);
        this._initCollection();

        this.listenTo(this.context.parent.get('collection'), 'sync', function () {
            if (self.collection) {
                self.collection.dataFetched = false;
                self.layout.reloadDashlet(options);
            }
        });
    },

    /**
     * Initialize feature collection.
     */
    _initCollection: function () {
        this.collection = app.data.createBeanCollection(this.module);
        this.collection.options = {
            params: {
                order_by: 'useful:desc,notuseful:asc'
            },
            limit: 3,
            fields: [
                'id',
                'name',
                'date_entered',
                'created_by',
                'created_by_name'
            ],
            filter: {
                'active_rev': {
                    '$equals': 1
                },
                'status': {
                    '$in': ['published', 'published-in', 'published-ex']
                }
            }
        };
        this.collection.sync = _.wrap(
            this.collection.sync,
            function (sync, method, model, options) {
                options = options || {};
                options.endpoint = function (method, model, options, callbacks) {
                    var url = app.api.buildURL(model.module, null, {}, options.params);
                    return app.api.call('read', url, {}, callbacks);
                };
                sync(method, model, options);
            }
        );
        return this;
    },

    /**
     * {@inheritDoc}
     *
     * Once collection has been changed, the view should be refreshed.
     */
    bindDataChange: function () {
        if (this.collection) {
            this.collection.on('add remove reset', function () {
                if (this.disposed) {
                    return;
                }
                this.render();
            }, this);
        }
    },

    /**
     * Load more data (paginate)
     */
    loadMoreData: function () {
        if (this.collection.next_offset > 0) {
            this.collection.paginate({add: true});
        }
    },

    /**
     * @inheritDoc
     */
    loadData: function (options) {
        if (this.collection.dataFetched) {
            if (options && options.complete) {
                options.complete();
            }
            return;
        }
        this.collection.fetch({
            success: function () {
                if (options && options.complete) {
                    options.complete();
                }
            }
        });
    }
})
