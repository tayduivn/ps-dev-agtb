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
    relateModule: null,

    fieldTag: 'input.select2',

    select2AllowedTemplates: ['edit'],

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        if (!_.isArray(this.model.get(this.name))) {
            this.model.set(this.name, []);
        }
        // optional vardef flag to be able to create new items during linkage
        this.collectionCreate = options.def.collection_create || this.collectionCreate;

        if (!this.relateModule) {
            app.logger.error('Child view should specify relate Module name on field initialization');
            return;
        }

        // init bean collection used for type aheads
        this.filterResults = app.data.createBeanCollection(this.relateModule);
    },

    /**
     * {@inheritDoc}
     */
    _render: function() {
        this._super('_render');

        if (_.indexOf(this.select2AllowedTemplates, this.tplName) !== -1) {
            this.initializeSelect2();
        }
        return this;
    },

    initializeSelect2: function() {
        this.$(this.fieldTag).select2({
            placeholder: '',
            minimumResultsForSearch: 5,
            minimumInputLength: 1,
            tags: true,
            multiple: true,
            width: '100%',
            containerCssClass: 'select2-choices-pills-close',
            initSelection: _.bind(this._initSelection, this),
            createSearchChoice: _.bind(this._createSearchChoice, this),
            query: _.debounce(_.bind(this._query, this), 300)
        });
        this.$(this.fieldTag).on('change', this.storeValues);

        var records = _.pluck(this.parseRecords(this.value), 'id').join();
        if (records.length) {
            this.$(this.fieldTag).select2('val', records);
        }
    },

    /**
     * Set the option selection during select2 initialization.
     * Also used during drag/drop in multiselects.
     * @param {Selector} $ele Select2 element selector
     * @param {Function} callback Select2 data callback
     * @private
     */
    _initSelection: function(el, callback) {
        var data = this.parseRecords(this.value);
        callback(data);
    },

    _createSearchChoice: function(term) {
        var selectedRecord = _.filter(this.filterResults.models, function(record) {
            return term == record.get('name');
        });

        // existing item
        if (selectedRecord.length !== 0) {
            return this.parseRecords(selectedRecord);
        }

        // new item
        if (this.collectionCreate) {
            // using text as id for new items
            return {id: term, text: term, locked: false, create: true};
        }
    },

    /**
     * Select2 callback used for loading the Select2 widget option list
     * @param {Object} query Select2 query object
     * @private
     */
    _query: function(query) {
        var shortlist = {results: []};
        var self = this;

        this.filterResults.filterDef = {
            filter: [{
                name: {
                    $starts: query.term
                }
            }]
        };
        this.filterResults.fetch({
            success: function(data) {
                shortlist.results = self.parseRecords(data.models);
                query.callback(shortlist);
            },
            error: function() {
                app.alert.show('collections_error', {
                    level: 'error',
                    messages: 'There was an issue retrieving the collection.'
                });
            }
        });
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
                    select2.push({
                        id: record.name,
                        text: record.name,
                        locked: false
                    });
                } else {
                    select2.push({
                        id: record.id,
                        text: record.name,
                        locked: false
                    });
                }
            }
        });
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
        if (this.model) {
            this.model.set(this.name, this.unformat(this.value));
        }
    },

    /**
     * Formats the value to be used in handlebars template and displayed on
     * screen.
     *
     * @param {Array} value The value to format.
     * @return {Array} Formatted value.
     */
    format: function(value) {
        return _.clone(value);
    },

    /**
     * Unformats the value for storing in a model. This should do the
     * inverse of {@link #format}.
     *
     * @param {Array} value The value to unformat.
     * @return {Array} Unformatted value.
     */
    unformat: function(value) {
        return _.clone(value);
    },

    /**
     * Avoid rendering process on Select2 change in order to keep focus in edit view
     * @override
     */
    bindDataChange: function() {
        if (this.model) {
            this.model.on('change:' + this.name, function() {
                if (_.indexOf(this.select2AllowedTemplates, this.tplName) === -1) {
                    this.render();
                }
            }, this);
        }
    },

    /**
     * Override to remove default DOM change listener, we use Select2 events instead
     * @override
     */
    bindDomChange: function() {

    },

    /**
     * @inheritdoc
     */
    unbindDom: function() {
        this.$(this.fieldTag).select2('destroy');
        this._super('unbindDom');
    }
})
