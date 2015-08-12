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
({
    plugins: ['Dashlet'],

    events: {
        "click [data-action=show-more]": "loadMoreData"
    },

    /**
     * KBContents bean collection.
     *
     * @property {Data.BeanCollection}
     */
    collection: null,

    /**
     * @inheritDoc
     */
    initialize: function (options) {
        var self = this;
        
        options.module = 'KBContents';
        this._super('initialize', [options]);
        this._initCollection();

        if (_.isUndefined(this.meta.config) || this.meta.config === false){
            this.listenTo(this.context.parent.get('collection'), 'sync', function () {
                if (self.collection) {
                    self.collection.dataFetched = false;
                    self.layout.reloadDashlet(options);
                }
            });
        }
    },

    /**
     * Initialize feature collection.
     */
    _initCollection: function () {
        this.collection = app.data.createBeanCollection(this.module);
        this.collection.setOption({
            params: {
                order_by: 'useful:desc,notuseful:asc,viewcount:desc,date_entered:desc',
                mostUseful: true
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
                'useful': {
                    '$gt': {
                        '$field': 'notuseful'
                    }
                },
                'status': {
                    '$equals': 'published'
                }
            }
        });
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
        this.collection.resetPagination();
        this.collection.fetch({
            success: function () {
                if (options && options.complete) {
                    options.complete();
                }
            }
        });
    }
})
