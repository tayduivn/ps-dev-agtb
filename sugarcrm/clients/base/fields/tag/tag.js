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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

({
    plugins: ['EllipsisInline'],

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        // init bean collection used for type aheads
        this.filterResults = app.data.createBeanCollection('Tags');
    },

    /**
     * {@inheritDoc}
     */
    _render: function() {
        // Set up tagList variable for use in the list view
        this.value = this.getFormattedValue();
        if (this.value) {
            this.tagList = _.pluck(this.value, 'name').join(', ');
        }

        this._super('_render');

        if (!this.$el.hasClass('select2field')) {
            this.initializeSelect2();
        }

        if (this.$select2) {
            this.$select2.on('change', _.bind(this.storeValues, this));
            this.$select2.on('select2-selecting', this.handleNewSelection);
        }

    },

    /**
     * Upon selection of a tag, if it's a new tag, get rid of the text indicating new tag
     * @param {event} e
     */
    handleNewSelection: function(e) {
        // For new tags, look for New Tag indicator and remove it if it's there
        if (e.object.newTag) {
            var newTagIdx = e.object.text.lastIndexOf(' (New Tag)');
            e.object.text = e.object.text.substr(0, newTagIdx);
        }
    },

    /**
     * Initialize select2 jquery widget
     */
    initializeSelect2: function() {
        var self = this;

        this.$select2 = this.$('.select2field').select2({
            placeholder: '',
            minimumResultsForSearch: 5,
            minimumInputLength: 1,
            tags: true,
            multiple: true,
            closeOnSelect: false,
            width: '100%',
            containerCssClass: 'select2-choices-pills-close',

            initSelection: function(element, callback) {
                var data = self.parseRecords(self.value);
                callback(data);
            },

            createSearchChoice: function(term) {
                // If tag is for filter, don't allow new choices to be selected
                if (self.view.action === 'filter-rows') {
                    return false;
                }

                var selectedRecord = _.filter(self.filterResults.models, function(record) {
                    return term == record.get('name');
                });
                if (selectedRecord.length !== 0) {
                    // Search term exists
                    return self.parseRecords(selectedRecord);
                } else {
                    // Search term is new
                    return {id: term, text: term + ' (New Tag)', locked: false, newTag: true};
                }
            },

            query: _.debounce(function(query) {
                var shortlist = {results: []};

                self.filterResults.filterDef = {
                    'filter': [{
                        'name': { '$starts': query.term }
                    }]
                };

                self.filterResults.fetch({
                    success: function(data) {
                        shortlist.results = self.parseRecords(data.models);
                        query.callback(shortlist);
                    },
                    error: function() {
                        app.alert.show('collections_error',
                            {level: 'error',
                                messages: 'There was an issue retrieving the collection.'});
                    }
                });
            }, 300),

            sortResults: function(results, container, query) {
                results = _.sortBy(results, 'text');
                return results;
            }
        });

        records = _.pluck(this.value, 'name').join();
        if (records.length) {
            this.$select2.select2('val', records);
        }
    },

    /**
     * Format related records in select2 format
     * @param {array} list of objects/beans
     */
    parseRecords: function(list) {

        var select2 = [];

        _.each(list, function(item) {

            var record = item;

            // we may have a bean from a collection
            if (_.isFunction(record.toJSON)) {
                record = record.toJSON();
            }

            // locked parameter can be used in the future to prevent removal
            select2.push({id: record.name, text: record.name, locked: false});
        });

        return select2;
    },

    /**
     * Store selected/removed values on our field which is put to the server
     * @param {event} e - event data
     */
    storeValues: function(e) {
        this.value = app.utils.deepCopy(this.value) || [];
        if (e.added) {
            // Check to see if the tag we're adding has already been added.
            // If it already does, and it's been flagged for removal, remove the removal flag
            var valFound = false;
            _.each(this.value, function(vals) {
                if (vals.name === e.added.text) {
                    valFound = true;
                    if (vals.removed) {
                        delete vals.removed;
                    }
                }
            });

            if (!valFound) {
                // If ID = text, then it is a new tag. Set ID to false for the backend to create a new tag out of it.
                var id = (e.added.id === e.added.text) ? false : e.added.id;
                this.value.push({id: id, name: e.added.text});
            }
        } else if (e.removed) {
            // Straight up delete the tag if we're using the tag to filter
            if (this.view.action === 'filter-rows') {
                this.value = _.reject(this.value, function(record) {
                    return record.name === e.removed.text;
                });
            } else {
                _.each(this.value, function(record) {
                    if (record.name === e.removed.text) {
                        record.removed = true;
                    }
                });
            }
        }

        this.model.set('tag', this.value);
    },

    /**
     * Avoid rendering process on Select2 change in order to keep focus
     * @override
     */
    bindDataChange: function() {
    },

    /**
     * Override to remove default DOM change listener, we use Select2 events instead
     * @override
     */
    bindDomChange: function() {
    },

    /**
     * {@inheritDoc}
     * @override
     */
    unbindDom: function() {
        this.$(this.fieldTag).select2('destroy');
        app.view.Field.prototype.unbindDom.call(this);
    }
})
