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

                        if (this.disposed === true) {
                            return;
                        }

                        offsets = collection.next_offset || [0];
                        hasMore = _.some(offsets, function(offset) {
                            return offset > -1;
                        });
                        options = {
                            // Use the view's metadata to control sort order
                            // and limit, as well as define fields to retrieve.
                            view: this.view.name,
                            success: function() {
                                fetchAll(collection);
                            }
                        };

                        if (hasMore) {
                            collection.paginate(options);
                        } else {
                            onComplete();
                        }
                    }, this);

                    if (this.model) {
                        // Each time the model is synchronized -- including
                        // the initial fetch -- the response may not include
                        // all records in the collection. Since this field
                        // requires all records in the collection, we must load
                        // the rest.
                        this.listenTo(this.model, 'sync', function() {
                            var collection = this.model.get(this.name);

                            if (this.disposed === true) {
                                return;
                            }

                            if (!this.model.isNew()) {
                                this.view.trigger('loading_collection_field', this.name);
                                fetchAll(collection);
                            }
                        });
                    }
                });
            }
        });
    });
})(SUGAR.App);
