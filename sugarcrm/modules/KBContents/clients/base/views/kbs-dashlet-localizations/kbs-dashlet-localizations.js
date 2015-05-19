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
    plugins: ['Dashlet', 'Timeago'],

    events: {
        'click [data-action=show-more]': 'loadMoreData'
    },

    /**
     * {@inheritDoc}
     *
     * @property {number} _defaultSettings.limit Maximum number of records to
     *   load per request, defaults to '5'.
     */
    _defaultSettings: {
        limit: 5
    },

    /**
     * KBContents bean collection.
     *
     * @property {Data.BeanCollection}
     */
    collection: null,

    /**
     * {@inheritDoc}
     *
     * Init collection.
     */
    initDashlet: function () {
        this._initSettings();
        this._initCollection();
    },

    /**
     * Sets up settings, starting with defaults.
     *
     * @return {View.Views.BaseRelatedDocumentsView} Instance of this view.
     * @protected
     */
    _initSettings: function () {
        this.settings.set(
            _.extend(
                {},
                this._defaultSettings,
                this.settings.attributes
            )
        );
        return this;
    },

    /**
     * Initialize feature collection.
     */
    _initCollection: function () {
        var self = this;
        this.collection = app.data.createBeanCollection(this.module);
        this.collection.sync = _.wrap(
            this.collection.sync,
            function (sync, method, model, options) {
                options = options || {};
                if (!options.error) {
                    options.error = function(error) {
                        if (error.status == 412) {
                            app.once('app:sync:complete', function() {
                                self.loadData(options);
                            });
                        }
                    }
                }
                options.endpoint = function (method, model, options, callbacks) {
                    var url = app.api.buildURL(model.module, null, {}, options.params);
                    return app.api.call('read', url, {}, callbacks);
                };
                sync(method, model, options);
            }
        );

        this.context.set('collection', this.collection);
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
     * Load more data (paginate).
     */
    loadMoreData: function () {
        if (this.collection.next_offset > 0) {
            this.collection.paginate({add: true});
        }
    },

    /**
     * {@inheritDoc}
     */
    loadData: function (options) {
        options = options || {};
        if (this.collection.dataFetched) {
            return;
        }
        this.collection.options = {
            limit: this.settings.get('limit'),
            fields: [
                'id',
                'name',
                'date_entered',
                'created_by',
                'created_by_name'
            ],
            filter: {
                'kbdocument_id' : {
                    '$equals': this.model.get('kbdocument_id')
                },
                'id' : {
                    '$not_equals': this.model.get('id')
                },
                'status': {
                    '$equals': 'published'
                },
                'active_rev': {
                    '$equals': 1
                }
            }
        };
        this.collection.fetch(options);
    }
})
