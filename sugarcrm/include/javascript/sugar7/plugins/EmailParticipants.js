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
        app.plugins.register('EmailParticipants', ['field'], {
            /**
             * @inheritdoc
             */
            onAttach: function(component, plugin) {
                var relatedModuleToLinkNameMap = [];

                /**
                 * Searches for more participants and loads them into Select2
                 * for selection.
                 *
                 * @param {Object} query See [Select2 Documentation of `query` parameter](http://ivaynberg.github.io/select2/#doc-query).
                 */
                var search = _.debounce(function(query) {
                    var data = {
                        results: [],
                        // Only show one page of results.
                        more: false
                    };
                    var options = {
                        // Add the search term to the URL params.
                        q: query.term,
                        // The first 10 results should be enough.
                        max_num: 10
                    };
                    var callbacks = {};
                    var url = app.api.buildURL('Mail', 'recipients/find', null, options);

                    /**
                     * Format the data in the response to be added to the set
                     * of options.
                     */
                    callbacks.success = function(result) {
                        var records = _.map(result.records, function(record) {
                            record.email_address = record.email;
                            delete record.email;

                            return record;
                        });

                        records = app.data.createMixedBeanCollection(records);
                        data.results = records.map(component.prepareModel, component);
                    };

                    /**
                     * Don't add any options.
                     */
                    callbacks.error = function() {
                        data.results = [];
                    };

                    /**
                     * Execute the query callback to add the results of the
                     * query as options the user can select.
                     */
                    callbacks.complete = function() {
                        query.callback(data);
                    };

                    app.api.call('read', url, null, callbacks);
                }, 300);

                this.on('init', function() {
                    relatedModuleToLinkNameMap = _.chain(this.fieldDefs.links)
                        .map(function(link) {
                            return _.has(link, 'name') ? link.name : link;
                        })
                        .reduce(function(map, link) {
                            var module = app.data.getRelatedModule(this.module, link);

                            if (module) {
                                map[module] = link;
                            }

                            return map;
                        }, {}, this)
                        .value();
                });

                this.hasLink = function(link) {
                    return _.contains(relatedModuleToLinkNameMap, link);
                };

                /**
                 * The name and email address are cached as properties on the
                 * object instead of modifying attributes on each model.
                 *
                 * The name is determined using {@link App.Utils#getRecordName}.
                 * The email address is determined from the attributes
                 * `email_address_used` and `email_address`, in that order,
                 * followed by the model's primary email address via
                 * {@link App.Utils#getPrimaryEmailAddress}.
                 *
                 * @param {Data.Bean} model
                 * @return {Data.Bean}
                 */
                this.prepareModel = function(model) {
                    var link;

                    if (!this.hasLink(model.get('_link'))) {
                        link = relatedModuleToLinkNameMap[model.module] || '';

                        if (!link) {
                            return null;
                        }

                        model.set('_link', link);
                    }

                    // Select2 needs the locked property directly on the object.
                    model.locked = !!this.def.readonly;
                    model.name = app.utils.getRecordName(model);
                    model.email_address = model.get('email_address_used') ||
                        model.get('email_address') ||
                        app.utils.getPrimaryEmailAddress(model);

                    return model;
                };

                /**
                 * Get the base options for initializing Select2.
                 *
                 * @return {Object}
                 */
                this.getSelect2Options = function() {
                    var module = this.module;

                    return {
                        containerCss: {
                            width: '100%'
                        },
                        width: 'off',
                        minimumInputLength: 1,
                        selectOnBlur: true,
                        data: this.getFormattedValue(),

                        /**
                         * Begin with current value of the field.
                         *
                         * See [Select2 Documentation](http://ivaynberg.github.io/select2/#documentation).
                         *
                         * @param {jQuery} element
                         * @param {Function} callback
                         */
                        initSelection: _.bind(function(element, callback) {
                            callback(this.getFormattedValue());
                        }, this),

                        /**
                         * Cannot call the debounced function directly or else
                         * it will not be debounced.
                         *
                         * @param {Object} query See [Select2 Documentation of `query` parameter](http://ivaynberg.github.io/select2/#doc-query).
                         */
                        query: function(query) {
                            search(query);
                        },

                        /**
                         * Use `cid` as a choice's ID. Some models are not yet
                         * synchronized and can only be identified by their
                         * `cid`. All models have a `cid`.
                         *
                         * See [Select2 Documentation](https://select2.github.io/select2/#documentation).
                         *
                         * @param {Object} choice
                         * @return {null|string|number}
                         */
                        id: function(choice) {
                            return _.isEmpty(choice) ? null : choice.cid;
                        },

                        /**
                         * Create an additional option for the email address
                         * when the query returns no matches for the search
                         * term.
                         *
                         * See [Select2 Documentation](http://ivaynberg.github.io/select2/#documentation).
                         *
                         * @param {string} term
                         * @param {Array} data The options in the dropdown after the query
                         * callback has been executed.
                         * @return {undefined|Data.Bean}
                         */
                        createSearchChoice: _.bind(function(term, data) {
                            var choice;

                            if (data.length === 0 && app.utils.isValidEmailAddress(term)) {
                                choice = app.data.createBean('EmailAddresses', {email_address: term});

                                return this.prepareModel(choice);
                            }
                        }, this),

                        /**
                         * Returns the localized message indicating that a
                         * search is in progress.
                         *
                         * See [Select2 Documentation](https://select2.github.io/select2/#documentation).
                         *
                         * @return {string}
                         */
                        formatSearching: function() {
                            return app.lang.get('LBL_LOADING', module);
                        },

                        /**
                         * Suppresses the message indicating the number of
                         * characters remaining before a search will trigger.
                         *
                         * See [Select2 Documentation](https://select2.github.io/select2/#documentation).
                         *
                         * @param {string} term
                         * @param {number} min
                         * @return {string}
                         */
                        formatInputTooShort: function(term, min) {
                            return '';
                        }
                    };
                };
            }
        });
    });
})(SUGAR.App);
