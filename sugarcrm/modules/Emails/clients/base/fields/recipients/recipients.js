/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

({
    events: {
        "click .btn": "_showAddressBook"
    },

    fieldTag: 'input.select2',

    /**
     * Sets up event handlers for syncing between the model and the recipients field.
     *
     * Recipients should be either a collection or an array of objects defined by
     * _addRecipients() method.
     */
    bindDataChange: function() {
        this.model.on("change:" + this.name, function(model, recipients) {
            this._replaceRecipients(this.format(recipients));

            if (recipients instanceof Backbone.Collection) {
                recipients.off("add remove", null, this);
                recipients.on("add remove", function(model, collection) {
                    this._replaceRecipients(this.format(collection));
                }, this);
            }
        }, this);
    },

    /**
     * Remove events from the field value if it is a collection
     */
    unbindData: function() {
        var value = this.model.get(this.name);
        if (value instanceof Backbone.Collection) {
            value.off("add remove", null, this);
        }

        app.view.Field.prototype.unbindData.call(this);
    },

    /**
     * Render field with select2 widget
     *
     * @private
     */
    _render: function() {
        app.view.Field.prototype._render.call(this);

        var $recipientsField = this.getFieldElement();
        if ($recipientsField.length > 0) {
            $recipientsField.select2({
                allowClear: true,
                multiple: true,
                width: '100%',
                query: _.bind(this.loadOptions, this),
                formatSelection: _.bind(this.formatSelection, this),
                formatResult:    _.bind(this.formatResult, this)
            });

            if (!!this.def.disabled) {
                $recipientsField.select2('disable');
            }
        }
    },

    /**
     * Placeholder for fetching additional recipients from the server
     *
     * @param query
     */
    loadOptions: function(query) {
        var data = {
            results: [],
            more: false // there are no more results by default
        };

        query.callback(data);
    },

    /**
     * Formats a recipient object for displaying selected recipients.
     *
     * @param recipient
     * @return {String}
     */
    formatSelection: function(recipient) {
        return recipient.name ? recipient.name : recipient.email;
    },

    /**
     * Formats a recipient object for displaying items in the recipient options list.
     *
     * @param recipient
     * @return {String}
     */
    formatResult: function(recipient) {
        return this.formatSelection(recipient); // do the same as formatSelection by default
    },

    /**
     * Translates a collection into an array of objects that select2 understands.
     *
     * @param data {Collection}
     * @returns {Array}
     */
    format: function(data) {
        var results = [];

        if (data instanceof Backbone.Collection) {
            data.each(function(model) {
                results.push(this._translateRecipient(model));
            }, this);
        } else {
            results = data;
        }

        return results;
    },

    /**
     * Translates an array of objects into a collection.
     *
     * @param data {Array}
     * @returns {Collection}
     */
    unformat: function(data) {
        var results = new Backbone.Collection();

        _.each(data, function(recipient) {
            if (recipient.bean) {
                results.add(recipient.bean);
            } else {
                results.add(new Backbone.Model(recipient));
            }
        });

        return results;
    },

    /**
     * Any changes to the recipient field should be reflected in the model
     */
    bindDomChange: function() {
        var self = this;
        this.getFieldElement()
            .on("change", function() {
                var value = $(this).select2('data');
                self.model.set(self.name, self.unformat(value), {silent: true});
            })
            .on("opening", function(event) {
                event.preventDefault();
            });
    },

    unbindDom: function() {
        this.getFieldElement().select2('destroy');
        app.view.Field.prototype.unbindDom.call(this);
    },

    /**
     * Adds the specified array of recipients to the field.  It can be either a single object
     * or an array of objects.  For example,
     *
     * { id: '1', email: 'one@email.com', name: 'One', module: 'Contacts', bean: {Bean} }
     *
     * @param newRecipients
     * @private
     */
    _addRecipients: function(newRecipients) {
        var existingRecipients = this.format(this.model.get(this.name)) || [],
            filteredRecipients = [];

        if (_.isObject(newRecipients) && !_.isArray(newRecipients)) {
            newRecipients = [newRecipients];
        }

        _.each(newRecipients, function(recipient) {
            var translatedRecipient = this._translateRecipient(recipient);

            // only add recipients whose id's are not found among the existing recipients
            if (_.where(existingRecipients, {id: translatedRecipient.id}).length === 0) {
                filteredRecipients.push(translatedRecipient);
            }
        }, this);

        this.getFieldElement()
            .select2('data', _.union(existingRecipients, filteredRecipients))
            .trigger('change');
    },

    /**
     * Replaces the current recipients with the new recipients
     *
     * @param recipients
     * @private
     */
    _replaceRecipients: function(recipients) {
        if (!_.isArray(recipients)) {
            recipients = [recipients];
        }

        _.each(recipients, function(recipient, index) {
            recipients[index] = this._translateRecipient(recipient);
        }, this);

        this.getFieldElement()
            .select2('data', recipients)
            .trigger('change');
    },

    /**
     * Recipient fields can be defined in metadata to include an icon button for opening an address book. When
     * configured to include this button, clicking the button will trigger an event to open the address book, which
     * calls this method to do the dirty work.
     *
     * @private
     */
    _showAddressBook: function() {
        app.drawer.open(
            {
                layout:  "compose-addressbook",
                context: {
                    module:   "Emails",
                    mixed:    true
                }
            },
            _.bind(this._addressbookDrawerCallback, this)
        );
    },

    _addressbookDrawerCallback: function(recipients) {
        this._addRecipients(this.format(recipients));
    },

    /**
     * Gets the recipients DOM field
     *
     * @returns {Object} DOM Element
     * @private
     */
    getFieldElement: function() {
        return this.$(this.fieldTag);
    },

    /**
     * Transpose data from a Backbone model into a standard Javascript object with the data required by the field.
     *
     * @param bean
     * @returns {Object}
     * @private
     */
    _getDataFromBean: function(bean) {
        var model = {
            id:     bean.get("id"),
            module: bean.module || bean.get("module"),
            name:   bean.get("name") || bean.get("full_name"),
            email:  bean.get("email1") || bean.get("email")
        };

        if (_.isArray(model.email)) {
            // grab the primary email address
            var primaryAddress = _.find(model.email, function (emailAddress) {
                return (emailAddress.primary_address == "1");
            });

            if (!_.isUndefined(primaryAddress) && !_.isEmpty(primaryAddress.email_address)) {
                model.email = primaryAddress.email_address;
            }
        }

        if (_.isEmpty(model.email) || !_.isString(model.email)) {
            delete model.email;
        }

        if (_.isEmpty(model.name)) {
            var name      = [],
                firstName = bean.get("first_name"),
                lastName  = bean.get("last_name");

            if (!_.isEmpty(firstName)) {
                name.push(firstName);
            }

            if (!_.isEmpty(lastName)) {
                name.push(lastName);
            }

            if (name.length > 0) {
                model.name = name.join(" ");
            } else {
                delete model.name;
            }
        }

        return model;
    },

    /**
     * Translate a recipient to an object that the recipients field can understand.
     *
     * @param recipient
     * @returns {Object}
     * @private
     */
    _translateRecipient: function(recipient) {
        var translatedRecipient = {},
            bean,
            id,
            module,
            name,
            email;

        if (recipient instanceof Backbone.Model) {
            bean   = this._getDataFromBean(recipient);
            id     = bean.id || bean.email;
            module = bean.module;
            name   = bean.name;
            email  = bean.email;
        } else {
            bean = {};

            // grab values off the bean
            if (recipient.hasOwnProperty("bean") && recipient.bean instanceof Backbone.Model) {
                bean = this._getDataFromBean(recipient.bean);
            }

            // try to grab values directly first, otherwise use the bean
            id     = recipient.id || bean.id || recipient.email || bean.email;
            module = recipient.module || bean.module;
            name   = recipient.name || bean.name;
            email  = recipient.email || bean.email;
        }

        // don't bother with the recipient unless an id is present
        if (!_.isEmpty(id)) {
            translatedRecipient.id = id;

            if (!_.isEmpty(email)) {
                // only set the email if it's actually available
                translatedRecipient.email = email;
            }

            if (!_.isEmpty(module)) {
                // only set the module if it's actually available
                translatedRecipient.module = module;
            }

            if (!_.isEmpty(name)) {
                // only set the name if it's actually available
                translatedRecipient.name = name;
            }

            if (!_.isEmpty(bean)) {
                // only set the name if it's actually available
                translatedRecipient.bean = (recipient instanceof Backbone.Model) ? recipient : recipient.bean;
            }
        }

        return translatedRecipient;
    }
})
