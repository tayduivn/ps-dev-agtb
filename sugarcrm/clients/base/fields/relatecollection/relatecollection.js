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
        _.bindAll(this);
        this._super('initialize', [options])

        if (!_.isArray(this.model.get(this.name))) {
            this.model.set(this.name, []);
        }

        // optional vardef flag to be able to create new items during linkage
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
        this._super('_render');
        if (!$('.select2-container').hasClass('select2field-' + this.name)) {
            this.initializeSelect2();
        }
        if (this.$select2) {
            this.$select2.on("change", this.storeValues);
        }
    },

    /**
     * Initialize select2 jquery widget
     */
    initializeSelect2: function() {
        var self = this;

        this.$select2 = this.$('.select2field-' + this.name).select2({
            placeholder: '',
            minimumResultsForSearch: 5,
            minimumInputLength: 1,
            tags: true,
            multiple: true,
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
                    // using text as id for new items
                    return {id: term, text: term, locked: false, create: true};
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
            }, 300)
        });

        // set selected options (need comma separated list of ids)
        var records = _.pluck(self.parseRecords(self.value), "id").join();
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
            if (!record.removed) {
                if (record.id === false) {
                    select2.push({id: record.name, text: record.name, locked: false});
                } else {
                    select2.push({id: record.id, text: record.name, locked: false});
                }
            }
        })

        return select2;
    },

    /**
     * Store selected/removed values on our field which is put to the server
     * @param e Event
     */
    storeValues: function(e) {

        if (e.added) {

            // use empty id for newly created items (if allowed)
            if (e.added.create) {
                this.value.push({id: false, name: e.added.text});
            } else {
                this.value.push({id: e.added.id, name: e.added.text});
            }

        } else if (e.removed) {
            _.each(this.value, function(record) {

                // remove existing records
                if (record.id == e.removed.id) {
                    record.removed = true;
                }

                // remove non-existing records (use name field as id)
                if (record.id == false && record.name == e.removed.id) {
                    record.removed = true;
                }
            });
        }
    },

    /**
     * Avoid rendering process on Select2 change in order to keep focus in edit view
     * @override
     */
    bindDataChange: function() {
        this.model.on('change:' + this.name, function() {
            if (this.action !== 'edit') {
                this.render();
            }
        }, this);
    },

    /**
     * Override to remove default DOM change listener, we use Select2 events instead
     * @override
     */
    bindDomChange: function() {
    }
})
