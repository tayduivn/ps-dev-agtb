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
(function(app) {
    app.events.on('app:init', function() {
        app.plugins.register('CollectionFieldLoadAll', ['field'], {
            /**
             * @inheritdoc
             */
            onAttach: function(component, plugin) {
                this.on('init', function() {
                    var onComplete = _.bind(function() {
                        if (this.disposed === true) {
                            return;
                        }

                        this.view.trigger('loaded_collection_field', this.name);
                    }, this);

                    /**
                     * Paginates the collection until all records have been fetched.
                     *
                     * @param {Data.BeanCollection} collection
                     */
                    var fetchAll = _.bind(function(collection) {
                        var offsets;
                        var hasMore;
                        var options;
                        var fields;

                        if (this.disposed === true) {
                            return;
                        }

                        offsets = collection.next_offset || [0];
                        hasMore = _.some(offsets, function(offset) {
                            return offset > -1;
                        });
                        fields = _.map(this.def.fields, function(field) {
                            return _.has(field, 'name') ? field.name : field;
                        });
                        options = {
                            success: function() {
                                fetchAll(collection);
                            }
                        };

                        if (!_.isEmpty(fields)) {
                            options.fields = fields;
                        }

                        if (hasMore) {
                            collection.paginate(options);
                        } else {
                            onComplete();
                        }
                    }, this);

                    if (this.model) {
                        // Each time the model is synchronized after the initial fetch, the
                        // response will not include data for the collection. Since this
                        // field requires all records in the collection, we must reload it.
                        // Even after the initial fetch, we must continue to load the rest
                        // of the records. This achieves both.
                        this.listenTo(this.model, 'sync', function() {
                            var collection = this.model.get(this.name);

                            if (this.disposed === true) {
                                return;
                            }

                            if (!this.model.isNew()) {
                                this.view.trigger('loading_collection_field', this.name);
                                //FIXME: Pagination doesn't currently work. Fix in MAR-4522.
                                // collection.resetPagination();
                                // fetchAll(collection);
                                // In the meantime, call onComplete.
                                onComplete();
                            }
                        });
                    }
                });
            }
        });
    });
})(SUGAR.App);
