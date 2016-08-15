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
/**
 * @class View.Fields.Base.Emails.EmailRecipientsField
 * @alias SUGAR.App.view.fields.BaseEmailsEmailRecipientsField
 * @extends View.Fields.Base.BaseField
 */
({
    /**
     * @inheritdoc
     *
     * This field doesn't support `showNoData`.
     */
    showNoData: false,

    fieldTag: 'input.select2',

    events: {
        'click .btn': '_showAddressBook'
    },

    /**
     * @inheritdoc
     *
     * @param {Object} options
     */
    initialize: function(options) {
        var fieldValue;

        this.plugins = _.union(this.plugins || [], ['DragdropSelect2']);

        this._super('initialize', [options]);

        if (this.model.isNew()) {
            try {
                fieldValue = this._getFieldValue();
            } catch (e) {
                // create a new virtual collection
                this.model.set(this.name, []);
            }
        } else if (!this.model.get(this.name)) {
            this.model.once('sync', this._fetchAllRecipients, this);
        } else {
            this._fetchAllRecipients();
        }

        this._initSelect2Templates();
    },

    /**
     * Initialize selection and result templates for Select2.
     *
     * @param {string} [type] Field type where these templates are located
     * @protected
     */
    _initSelect2Templates: function(type) {
        type = type || this.type;
        this.select2ResultTemplate = app.template.getField(
            type,
            'select2-result',
            this.module
        );
        this.select2SelectionTemplate = app.template.getField(
            type,
            'select2-selection',
            this.module
        );
    },

    /**
     * Returns the collection stored for this field.
     *
     * @throws An exception when the value is not a collection
     * @return {VirtualCollection}
     * @private
     */
    _getFieldValue: function() {
        var value = this.model.get(this.name);

        if (!(value instanceof app.BeanCollection)) {
            throw 'the value must be a BeanCollection';
        }

        return value;
    },

    /**
     * @inheritdoc
     *
     * Sets up event handlers for syncing between the model and the recipients
     * field.
     *
     * See {@link #format} for the acceptable formats for recipients.
     */
    bindDataChange: function() {
        var value = this.model.get(this.name);

        // Set up event handlers that allow external forces to manipulate the
        // contents of the collection, while maintaining the requirement for
        // storing formatted recipients.
        if (value instanceof Backbone.Collection) {
            // on "add" we want to force the collection to be reset to guarantee
            // that all models in the collection have been properly formatted
            // for use in this field
            value.on('add', function(models, collection) {
                if (this.action === 'edit') {
                    this._formatCollectionModels(collection);
                }
            }, this);

            // on "remove" the requisite models have already been removed, so we
            // only need to bother updating the value in the DOM
            value.on('remove', function(models, collection) {
                if (this.action === 'edit') {
                    // format the recipients and put them in the DOM
                    this._updateSelect2(this.getFormattedValue());
                }
            }, this);

            // on "reset" we want to replace all models in the collection with
            // their formatted versions
            value.on('reset', function(collection) {
                if (this.action === 'edit') {
                    this._formatCollectionModels(collection);
                }
            }, this);
        }

        this.model.on('change:' + this.name, function(model, value) {
            if (this.action !== 'edit') {
                this.render();
            }
        }, this);
    },

    /**
     * Format all the models on the collection
     *
     * @param {Data.MixedBeanCollection} collection
     * @private
     */
    _formatCollectionModels: function(collection) {
        var recipients = this.format(collection.models);

        // do this silently so we don't trigger another reset event
        collection.reset(recipients, {silent: true});

        // put the newly formatted recipients in the DOM
        this._updateSelect2(recipients);
    },

    /**
     * Update select2 with a list of recipients
     * @param {Array} recipients
     * @private
     */
    _updateSelect2: function(recipients) {
        // put the formatted recipients in the DOM
        this.$(this.fieldTag).select2('data', recipients);

        if (!this.def.readonly) {
            this.setDragDropPluginEvents(this.$(this.fieldTag));
        }
    },

    /**
     * @inheritdoc
     *
     * Remove events from the field value if it is a collection
     */
    unbindData: function() {
        var value = this.model.get(this.name);
        if (value instanceof Backbone.Collection) {
            value.off(null, null, this);
        }

        this._super('unbindData');
    },

    /**
     * @inheritdoc
     *
     * Render field with select2 widget
     *
     * @private
     */
    _render: function() {
        var $recipientsField;

        this._addAddressBookIconPadding();

        this._super('_render');

        $recipientsField = this.$(this.fieldTag);

        if ($recipientsField.length > 0) {
            $recipientsField.select2(this._getSelect2Options());

            if (!!this.def.disabled) {
                $recipientsField.select2('disable');
            }

            if (!this.def.readonly) {
                this.setDragDropPluginEvents(this.$(this.fieldTag));
            }

            this._updateSelect2(this.getFormattedValue());
        }
    },

    /**
     * Add appropriate classes to leave padding for the Address Book icon to
     * be inserted after the field.
     *
     * @protected
     */
    _addAddressBookIconPadding: function() {
        var $controlsEl;

        if (this.$el) {
            $controlsEl = this.$el.closest('.controls');
            if ($controlsEl.length) {
                $controlsEl.addClass('controls-one btn-fit');
            }
        }
    },

    /**
     * Build the Select2 initialization options.
     *
     * @return {Object} Any acceptable Select2 options that can be passed in
     *   during initialization described in the library's documentation.
     * @protected
     */
    _getSelect2Options: function() {
        return {
            allowClear: true,
            multiple: true,
            width: 'off',
            containerCssClass: 'select2-choices-pills-close',
            containerCss: {'width': '100%'},
            minimumInputLength: 1,
            query: _.bind(function(query) {
                this._loadOptions(query);
            }, this),
            id: _.bind(this._getSelect2Id, this),
            createSearchChoice: _.bind(this._createOption, this),
            formatSelection: _.bind(this._formatSelection, this),
            formatResult: _.bind(this._formatResult, this),
            formatSearching: _.bind(this._formatSearching, this),
            formatInputTooShort: _.bind(this._formatInputTooShort, this),
            selectOnBlur: true
        };
    },

    /**
     * Fetches additional recipients from the server.
     *
     * See [Select2 Documentation of `query` parameter](http://ivaynberg.github.io/select2/#doc-query).
     *
     * @param {Object} query Possible attributes can be found in select2's
     *   documentation.
     * @private
     */
    _loadOptions: _.debounce(function(query) {
        var self = this;
        var data = {
            results: [],
            // only show one page of results
            more: false
        };
        var options = {};
        var callbacks = {};
        var url;

        // add the search term to the URL params
        options.q = query.term;
        // the first 10 results should be enough
        options.max_num = 10;
        // build the URL for fetching recipients that match the search term
        url = app.api.buildURL('Emails', 'recipients/find', null, options);
        // create the callbacks
        callbacks.success = function(result) {
            // the api returns objects formatted such that sidecar can convert
            // them to beans we need the records to be in a standard object
            // format (@see RecipientsField::format) and the records
            // need to be converted into beans before we can format them
            var records = app.data.createMixedBeanCollection(result.records);
            // format and add the recipients that were found via the select2
            // callback
            data.results = self.format(records);
        };
        callbacks.error = function() {
            // don't add any recipients via the select2 callback
            data.results = [];
        };
        callbacks.complete = function() {
            // execute the select2 callback to add any new recipients
            query.callback(data);
        };
        app.api.call('read', url, null, callbacks);
    }, 300),

    /**
     * Get a identifier for the recipient that select2 can use to determine
     * uniqueness.
     *
     * @param {Data.Bean} recipient
     * @return {string}
     * @private
     */
    _getSelect2Id: function(recipient) {
        return recipient.get('id') || recipient.get('email_address');
    },

    /**
     * Create additional select2 options when _loadOptions returns no matches
     * for the search term.
     *
     * See [Select2 Documentation](http://ivaynberg.github.io/select2/#documentation).
     *
     * @param {string} term
     * @param {Array} data The options in the select2 drop-down after the query
     *   callback has been executed.
     * @return {Object}
     * @private
     */
    _createOption: function(term, data) {
        if (data.length === 0) {
            return app.data.createBean('EmailAddresses', {email_address: term});
        }
    },

    /**
     * Formats a recipient object for displaying selected recipients.
     *
     * See [Select2 Documentation](http://ivaynberg.github.io/select2/#documentation).
     *
     * @param {Object} recipient
     * @return {string}
     * @private
     */
    _formatSelection: function(recipient) {
        var value = recipient.get('name') || recipient.get('email_address');
        if (this.select2SelectionTemplate) {
            return this.select2SelectionTemplate({
                id: this._getSelect2Id(recipient),
                name: value,
                email: recipient.get('email_address')
            });
        }
        return value;
    },

    /**
     * Formats a recipient object for displaying items in the recipient options
     * list.
     *
     * See [Select2 Documentation](http://ivaynberg.github.io/select2/#documentation).
     *
     * @param {Object} recipient
     * @return {string}
     * @private
     */
    _formatResult: function(recipient) {
        var value;
        var module = recipient.get('module') || '';

        if (recipient.get('name')) {
            value = '"' + recipient.get('name') + '" <' + recipient.get('email_address') + '>';
        } else {
            value = recipient.get('email_address');
        }

        return this.select2ResultTemplate({
            value: value,
            module: module
        });
    },

    /**
     * Returns the localized message indicating that a search is in progress
     *
     * See [Select2 Documentation](http://ivaynberg.github.io/select2/#documentation).
     *
     * @return {string}
     * @private
     */
    _formatSearching: function() {
        return app.lang.get('LBL_LOADING', this.module);
    },

    /**
     * Suppresses the message indicating the number of characters remaining
     * before a search will trigger
     *
     * See [Select2 Documentation](http://ivaynberg.github.io/select2/#documentation).
     *
     * @param {string} term Search string entered by user.
     * @param {number} min Minimum required term length.
     * @return {string}
     * @private
     */
    _formatInputTooShort: function(term, min) {
        return '';
    },

    /**
     * Formats a set of recipients into an array of objects that select2
     * understands.
     *
     * See {@link #_formatRecipient} for the acceptable/expected attributes to
     * be found on each recipient.
     *
     * @param {Mixed} data A Backbone collection, a single Backbone model or
     *   standard JavaScript object, or an array of Backbone models or standard
     *   JavaScript objects.
     * @return {Array}
     */
    format: function(data) {
        var formattedRecipients = [];

        // the lowest common denominator of potential inputs is an array of
        // objects force the parameter to be an array of either objects or
        // Backbone models so that we're always dealing with one data-structure
        // type
        if (data instanceof Backbone.Collection) {
            // get the raw array of models
            data = data.models;
        } else if (data instanceof Backbone.Model || (_.isObject(data) && !_.isArray(data))) {
            // wrap the single model in an array so the code below behaves the
            // same whether it's a model or a collection
            data = [data];
        }
        if (_.isArray(data)) {
            _.each(data, function(recipient) {
                var formattedRecipient;
                if (!(recipient instanceof Backbone.Model)) {
                    // force the object to be a Backbone.Model to allow for
                    // certain assumptions to be made there is no harm in this
                    // because the recipient will not be added to the return
                    // value if no email address is found on the model
                    recipient = new Backbone.Model(recipient);
                }
                formattedRecipient = this._formatRecipient(recipient);
                // only add the recipient if there is an email address
                if (formattedRecipient.get('email_address')) {
                    formattedRecipients.push(formattedRecipient);
                }
            }, this);
        }
        return formattedRecipients;
    },

    /**
     * Synchronize the recipient field value with the model and setup tooltips
     * for email pills.
     */
    bindDomChange: function() {
        var self = this;
        this.$(this.fieldTag)
            .on('change', function(event) {
                var value = $(this).select2('data');
                if (event.removed) {
                    value = _.filter(value, function(d) {
                        return d.id !== event.removed.id;
                    });
                }
                self.model.get(self.name).reset(value);
            })
            .on('select2-selecting', _.bind(this._handleEventOnSelected, this));
    },

    /**
     * Event handler for the Select2 "select2-selecting" event.
     *
     * @param {Event} event
     * @return {boolean}
     * @private
     */
    _handleEventOnSelected: function(event) {
        // only allow the user to select an option if it is determined to be a
        // valid email address returning true will select the option; false will
        // prevent the option from being selected
        var isValidChoice = false;

        // since this event is fired twice, we only want to perform validation
        // on the first event event.object is not available on the second event
        if (event.object) {
            // the object will have an id if it came from the database and we
            // are assuming that email addresses stored in the database have
            // already been validated
            if (_.isEmpty(event.object.get('id'))) {
                // this option must be a new email address that the application
                // does not recognize
                // so validate it
                isValidChoice = this._validateEmailAddress(event.object.get('email_address'));
            } else {
                // the application should recognize the email address, so no
                // need to validate it again just assume it's a valid choice and
                // we'll deal with the consequences later (server-side)
                isValidChoice = true;
            }
        }

        return isValidChoice;
    },

    /**
     * @inheritdoc
     *
     * Destroy all select2 and tooltip plugins
     */
    unbindDom: function() {
        this.$(this.fieldTag).select2('destroy');
        this._super('unbindDom');
    },

    /**
     * Format a recipient from a plain Backbone model into a proper module
     * specific bean.
     *
     * @param {Backbone.Model} recipient
     * @return {Data.Bean} Recipient formatted as a proper bean.
     * @private
     */
    _formatRecipient: function(recipient) {
        var attributes = this._getRecipientAttributes(recipient);
        var formattedRecipient = app.data.createBean(attributes.module, attributes);

        // select2 needs the locked attribute directly on the object
        formattedRecipient.locked = this.def.readonly || false;

        return formattedRecipient;
    },

    /**
     * Extract recipient attributes from a Backbone.Model into a standard
     * JavaScript object with id, module, email, and name attributes. Only id
     * and email are required for the recipient to be considered valid
     * {@link #format}.
     *
     * All attributes are optional. However, if the email attribute is not
     * present, then a primary email address should exist on the bean. Without
     * an email address that can be resolved, the recipient is considered to be
     * invalid. The bean attribute must be a Backbone.Model and it is likely to
     * be a Bean. Data found in the bean is considered to be secondary to the
     * attributes found on its parent model. The bean is a mechanism for
     * collecting additional information about the recipient that may not have
     * been explicitly set when the recipient was passed in.
     *
     * @param {Backbone.Model} recipient
     * @return {Object} Recipient attributes
     * @private
     */
    _getRecipientAttributes: function(recipient) {
        var bean;
        var attributes;
        var emailAddress;

        attributes = {};
        if (recipient instanceof Backbone.Model) {
            bean = recipient.get('bean');
            // if there is a bean attribute, then more data can be extracted
            // about the recipient to fill in any holes if attributes are
            // missing amongst the primary attributes so follow the trail using
            // recursion
            if (bean) {
                attributes = this._getRecipientAttributes(bean);
            }

            // prioritize any values found on recipient over those already
            // extracted from bean
            emailAddress = recipient.get('email_address_used') || recipient.get('email') ||
                recipient.get('email_address') || attributes.email_address;
            attributes = {
                id: recipient.get('id') || attributes.id,
                module: recipient.get('module') || recipient.module || recipient.get('_module') || attributes.module,
                email_address: emailAddress,
                name: app.utils.getRecordName(recipient) || attributes.name
            };

            // extract the primary email address for the recipient
            if (_.isArray(attributes.email_address)) {
                var primaryEmailAddress = _.findWhere(attributes.email_address, {primary_address: true});

                if (!_.isUndefined(primaryEmailAddress) && !_.isEmpty(primaryEmailAddress.email_address)) {
                    attributes.email_address = primaryEmailAddress.email_address;
                }
            }
            // drop any values that are empty or non-compliant
            _.each(attributes, function(val, key) {
                if ((_.isEmpty(attributes[key]) || !_.isString(attributes[key])) && !_.isBoolean(attributes[key])) {
                    delete attributes[key];
                }
            });
        }
        return attributes;
    },

    /**
     * Validates an email address on the server.
     *
     * @param {string} emailAddress
     * @return {boolean}
     * @private
     */
    _validateEmailAddress: function(emailAddress) {
        var isValid = false;
        var callbacks = {};
        var options = {
            // execute the api call synchronously so that the method doesn't
            // return before the response is known
            async: false
        };
        var url = app.api.buildURL('Emails', 'address/validate');

        callbacks.success = function(result) {
            isValid = result[emailAddress];
        };
        callbacks.error = function() {
            isValid = false;
        };
        app.api.call('create', url, [emailAddress], callbacks, options);

        return isValid;
    },

    /**
     * When in edit mode, the field includes an icon button for opening an
     * address book. Clicking the button will trigger an event to open the
     * address book, which calls this method to do the dirty work. The selected
     * recipients are added to this field upon closing the address book.
     *
     * @private
     */
    _showAddressBook: function() {
        // Callback to add recipients, from a closing drawer, to the target
        // Recipients field.
        var addRecipients = _.bind(function(recipients) {
            if (recipients && recipients.length > 0) {
                this.model.get(this.name).add(recipients.models);
            }
            this.context.trigger('address-book-state', 'closed');
        }, this);

        app.drawer.open(
            {
                layout: 'compose-addressbook',
                context: {
                    module: 'Emails',
                    mixed: true
                }
            },
            function(recipients) {
                addRecipients(recipients);
            }
        );
        this.context.trigger('address-book-state', 'open');
    },

    /**
     * Retrieves all records for the to/cc/bcc field if they exceed the maximum
     * config setting from the application
     *
     * @private
     */
    _fetchAllRecipients: function() {
        var collection;

        if (this.model && !this.disposed) {
            try {
                collection = this._getFieldValue();
            } catch (e) {
                // create a new virtual collection
                this.model.set(this.name, []);
                collection = this.model.get(this.name);
            }

            collection.fetchAll({
                fields: ['name', 'email_address_used'],
                success: _.bind(function() {
                    if (this.action === 'edit' && !this.disposed) {
                        // format the recipients and put them in the DOM
                        this._updateSelect2(this.getFormattedValue());
                    }
                }, this)
            });
        }
    }
})
