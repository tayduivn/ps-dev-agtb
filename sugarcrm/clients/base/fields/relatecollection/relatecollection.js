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
    /**
     * By default we do not allow the creation of new related
     * objects. This depends on the implementing field itself.
     * Tags for example can be related/created on the fly.
     */
    collectionCreate: false,

    /**
     * Name of relate collection module, needs to be set
     * by implementing field.
     */
    relateModule : null,

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        this._super('initialize', [options])
        this.collectionCreate = options.def.collection_create || this.collectionCreate;

        if (!this.relateModule) {
            app.logger.error('Child view should specify relate Module name on field initialization');
            return ;
        }

        // init bean collection used for type aheads
        this.filterResults = app.data.createBeanCollection(this.relateModule);
    },

    /**
     * {@inheritDoc}
     */
    _render: function() {
        app.view.Field.prototype._render.call(this);
        if (!$('.select2-container').hasClass('select2field')) {
            this.initializeSelect2();
        }

        if (this.$select2) {
            this.$select2.on("change", _.bind(this.storeValues, this));
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
            containerCssClass: 'select2-choices-pills-close select2-choices-pills-square',

            initSelection: function(element, callback) {
                var data = self.parseRecords(self.value);
                callback(data);
            },

            createSearchChoice: function(term) {
                var selectedRecord = _.filter(self.filterResults.models, function(record) {
                    return term == record.get("name");
                });

                // existing item
                if (selectedRecord.length !== 0) {
                    return self.parseRecords(selectedRecord);
                }

                // new item
                if (selectedRecord.length === 0 && self.collectionCreate) {
                    return {id: term, text: term, locked: false};
                }
            },

            query: _.debounce(function(query) {

                var shortlist = {results: []};

                self.filterResults.filterDef = {
                    "filter": [{
                            "name": { "$starts": query.term }
                    }]
                };

                self.filterResults.fetch({
                    success: function(data) {
                        shortlist.results = self.parseRecords(data.models);
                        query.callback(shortlist);
                    },
                    error: function() {
                        app.alert.show('collections_error', {level: 'error', messages: 'There was an issue retrieving the collection.'});
                    }
                });
            }, 300),

            sortResults: function(results, container, query) {
                results = _.sortBy(results, 'text');
                return results;
            }
        });
        records = _.pluck(self.value, "id").join();
        if (records.length) {
            self.$select2.select2("val", records);
        }
    },

    /**
     * Format related records in select2 format
     * @param list Array of objects/beans
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
            select2.push({id: record.id, text: record.name, locked: false});
        })

        return select2;
    },

    /**
     * Store selected/removed values on our field which is put to the server
     * @param e Event
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
            _.each(this.value, function(record) {
                if (record.name === e.removed.text) {
                    record.removed = true;
                }
            });
        }

        this.model.set('tag', this.value);
    },

    /**
     * Massage model's tag values in order to make the filter work correctly.
     *
     * Return the model's tag value pre-change so we can set it up correctly again after the filter does its thing
     */
    formatDataForFilter: function() {
        // Turn the current array of Tag objects into a list of tag names. If a tag object has the removed flag set,
        //it will not be added to the list.
        var collection = _.filter(this.model.get('tag'), function(tag) {
                if (!tag.removed) {
                    return true;
                }}) || [],
            filterTags = _.pluck(collection, 'name');

        this.model.set('tag', filterTags);
        return collection;
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
