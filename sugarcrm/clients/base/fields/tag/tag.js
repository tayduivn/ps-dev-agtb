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
/**
 * @class View.Fields.Base.TagField
 * @alias SUGAR.App.view.fields.BaseTagField
 * @extends View.Fields.Base.BaseField
 */
({
    plugins: ['EllipsisInline'],

    /**
     * HTML tag of the append tag checkbox.
     *
     * @property {String}
     */
    appendTagInput: 'input[name=append_tag]',

    events: {
        'click [data-action=tag-search]': 'linkToSearch'
    },

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        // init bean collection used for type aheads
        this.filterResults = app.data.createBeanCollection('Tags');

        // initialize value to empty array
        if (!this.model.has(this.name)) {
            this.model.setDefault(this.name, []);
        }

        // Set append as default when mass updating tags
        this.appendTagValue = true;
        this.model.set('tag_type', this.appendTagValue ? '1' : '0');
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

        this.initializeSelect2();
        this.$select2.on('change', _.bind(this.storeValues, this));
        this.$select2.on('select2-selecting', this.handleNewSelection);
    },

    /**
     * Overrides select2 function. For more details, check out select2's documentation
     *
     * @param term
     * @param results
     * @returns {*}
     * @private
     */
    _createSearchChoice: function(term, results) {
        // If tag is for filter, don't allow new choices to be selected
        if (this.view.action === 'filter-rows') {
            return false;
        }

        // Trim up the term for sanity sake
        term = $.trim(term);

        // Check previously found results to see tag exists with different casing
        if (results && results.length) {
            if (_.find(results, function(tag) {
                return tag.text.toLowerCase() === term.toLowerCase();
            })) {
                return false;
            }
        }
        
        // Check if input is empty after trim
        if (term === '') {
            return false;
        }

        // Check for existence amongst tags that exist but haven't been saved yet
        if (this.checkExistingTags(term)) {
            return false;
        }

        return {
            id: term,
            text: term + ' ' + app.lang.get('LBL_TAG_NEW_TAG'),
            locked: false,
            newTag: true
        };
    },

    /**
     * Check tag select2's currently selected tags for term to see if it already exists (case insensitive)
     * @param term term to be checked
     * @return boolean - true if tag exists already
     */
    checkExistingTags: function(term) {
        if (this.$select2 && _.isFunction(this.$select2.val)) {
            var currentSelections = this.$select2.val().split(',');
        }
        if (currentSelections && currentSelections.length) {
            if (_.find(currentSelections, function(tag) {
                return tag.toLowerCase() === term.toLowerCase();
            })) {
                return true;
            }
        }

        return false;
    },

    /**
     * Overrides select2 function. For more details, check out select2's documentation
     * @param query
     * @private
     */
    _query: function(query) {
        var self = this,
            shortlist = {results: []};

        // Trim the query term right up front since it needs to be clean
        query.term = $.trim(query.term);

        // If tag already exists, no need to query db
        if (this.checkExistingTags(query.term)) {
            query.callback(shortlist);
            return;
        }

        this.filterResults.filterDef = {
            'filter': [{
                'name_lower': { '$starts': query.term.toLowerCase() }
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
                    messages: 'LBL_TAG_FETCH_ERROR'
                });
            }
        });
    },

    /**
     * Upon selection of a tag, if it's a new tag, get rid of the text indicating new tag
     * @param {event} e
     */
    handleNewSelection: function(e) {
        // For new tags, look for New Tag indicator and remove it if it's there
        if (e.object.newTag) {
            var newTagIdx = e.object.text.lastIndexOf(' ' + app.lang.get('LBL_TAG_NEW_TAG'));
            e.object.text = e.object.text.substr(0, newTagIdx);
        }
    },

    /**
     * Initialize select2 jquery widget
     */
    initializeSelect2: function() {
        var self = this,
            escapeChars = '!\"#$%&\'()*+,./:;<=>?@[\\]^`{|}~';

        this.$select2 = this.$('.select2field').select2({
            placeholder: '',
            minimumResultsForSearch: 5,
            minimumInputLength: 1,
            tags: true,
            multiple: true,
            closeOnSelect: false,
            width: '100%',
            containerCssClass: 'select2-choices-pills-close',
            tokenSeparators: [','],

            initSelection: function(element, callback) {
                var data = self.parseRecords(self.value);
                callback(data);
            },

            createSearchChoice: _.bind(this._createSearchChoice, this),
            query: _.debounce(_.bind(this._query, this), 300),

            sortResults: function(results, container, query) {
                results = _.sortBy(results, 'text');
                return results;
            }
        });

        var records = _.map(this.value, function(record) {
            // If a special character is the first character of a tag, it breaks select2 and jquery and everything
            // So escape that character if it's the first char
            if (escapeChars.indexOf(record.name.charAt(0)) >= 0) {
                return '\\\\' + record.name;
            }
            return record.name;
        });

        if (records.length) {
            this.$select2.select2('val', records);
        }

        // Workaround to make select2 treat enter the same as it would a comma (INT-668)
        this.$('.select2-search-field > input.select2-input').on('keyup', function(e) {
            if (e.keyCode === 13) {
                var val = self.$('input.select2-input').val();

                // Trim the tag
                val = $.trim(val);

                // Prevent blank tags
                if (val === '') {
                    return;
                }

                // Sanitize input
                if (escapeChars.indexOf(val.charAt(0)) >= 0) {
                    val = '\\\\' + val;
                }

                var tags = self.$select2.select2('data');

                // If the current input already exists as a tag (case insensitive), just exit
                var exists = _.find(tags, function(tag) {
                    return tag.id.toLowerCase() === val.toLowerCase();
                });
                if (exists) {
                    // Close the search box and return
                    self.$select2.select2('close');
                    // Re-opens the search box with the default message
                    // (this is to maintain consistency with select2's OOB tokenizer)
                    self.$select2.select2('open');
                    return;
                }

                // Otherwise, create a tag out of current input
                tags.push({id: val, text: val, locked: false});
                self.$select2.select2('data', tags, true);
                e.preventDefault();

                // Close the search box
                self.$select2.select2('close');
            }
        });
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
            // Check if added is an array or a single object
            if (_.isArray(e.added)) {
                // Even if it is an array, only one object gets added at a time,
                // so we just need it to be the first element
                e.added = e.added[0];
            }

            // Check to see if the tag we're adding has already been added.
            var valFound = _.find(this.value, function(vals) {
                return vals.name === e.added.text;
            });

            if (!valFound) {
                this.value.push({id: e.added.id, name: e.added.text});
            }
        } else if (e.removed) {
            // Remove the tag
            this.value = _.reject(this.value, function(record) {
                return record.name === e.removed.text;
            });
        }
        this.model.set('tag', this.value);
    },

    /**
     * Avoid rendering process on Select2 change in order to keep focus
     * @override
     */
    bindDataChange: function() {
        if (this.model) {
            this.model.on('change:' + this.name, function() {
                // only re-render the field if we are on merge-duplicates view
                // it allows tags to copy over when choosing primaryRecord's tags
                if (this.context.get('selectedDuplicates')) {
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
        // Borrowed from team set
        var $el = this.$(this.appendTagInput);
        if ($el.length) {
            $el.on('change', _.bind(function() {
                this.appendTagValue = $el.prop('checked');
                this.model.set('tag_type', this.appendTagValue ? '1' : '0');
            }, this));
        }
    },

    /**
     * {@inheritDoc}
     */
    unbindDom: function() {
        // This line is likewise borrowed from team set
        this.$(this.appendTagInput).off();
        this.$('.select2field').select2('destroy');
        this._super('unbindDom');
    },

    /**
     * Link to globalsearch page
     *
     * @param {Event} e
     */
    linkToSearch: function(e) {
        var tagName = e.currentTarget.dataset['tag'];
        var route = app.utils.GlobalSearch.buildSearchRoute('', {
            tags: [tagName]
        });

        app.router.navigate(route, {trigger: true});
    }

})
