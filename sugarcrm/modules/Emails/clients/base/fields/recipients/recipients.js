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
     * Sets up event handlers for adding and replacing recipients on the field.
     *
     * ADD: this.context.trigger('recipients:to_addresses:add', recipients);
     * REPLACE: this.model.set('to_addresses', recipients);
     *
     * Recipients should be either an object or an array of objects defined by
     * _addRecipients() method.
     */
    bindDataChange: function() {
        this.context.on("recipients:" + this.name + ":add", function(recipients) {
            this._addRecipients(recipients);
        }, this);

        this.model.on("change:" + this.name, function(model,recipients) {
            this._replaceRecipients(recipients);
        }, this);
    },

    /**
     * Render field with select2 widget
     *
     * @private
     */
    _render: function() {
        app.view.Field.prototype._render.call(this);

        var $recipientsField = this._getEmailField();
        if ($recipientsField.length > 0) {
            $recipientsField.select2({
                allowClear: true,
                multiple: true,
                query: _.bind(this.loadOptions, this)
            });
        }
    },

    /**
     * Placeholder for fetching additional recipients from the server
     *
     * @param query
     */
    loadOptions: function(query) {
        var data = {
            results: [{
                id: '1',
                name: 'one',
                email: 'one@email.com',
                text: 'One'
            }, {
                id: '2',
                name: 'two',
                email: 'two@email.com',
                text: 'Two'
            }, {
                id: '3',
                name: 'three',
                email: 'three@email.com',
                text: 'Three'
            }]
        };

        query.callback(data);
    },

    /**
     * Gets the recipients directly from select2
     *
     * @returns {Array}
     */
    unformat: function() {
        return this._getEmailField().select2('data');
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
        var existingRecipients = this.model.get(this.name) || [],
            filteredRecipients = [];

        if (_.isObject(newRecipients) && !_.isArray(newRecipients)) {
            newRecipients = [newRecipients];
        }

        _.each(newRecipients, function(recipient, index) {
            var translatedRecipient = this._translateRecipient(recipient);
            if (_.where(existingRecipients, {id: translatedRecipient.id}).length === 0) {
                filteredRecipients.push(translatedRecipient);
            }
        }, this);

        this._getEmailField().select2('data', _.union(existingRecipients, filteredRecipients));
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

        this._getEmailField().select2('data', recipients);
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
                    mixed:    true,
                    forceNew: true
                }
            },
            _.bind(this._addressbookDrawerCallback, this)
        );
    },

    _addressbookDrawerCallback: function(recipients) {
        this.model.set(this.name, this._addRecipients(recipients));
    },

    /**
     * Gets the recipients DOM field
     *
     * @returns {*}
     * @private
     */
    _getEmailField: function() {
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
            bean = {},
            id,
            module,
            name,
            email;

        // grab values off the bean
        if (recipient.hasOwnProperty("bean") && recipient.bean instanceof Backbone.Model) {
            bean = this._getDataFromBean(recipient.bean);
        }

        // try to grab values directly first, otherwise use the bean
        id     = recipient.id || bean.id || recipient.email || bean.email;
        module = recipient.module || bean.module;
        name   = recipient.name || bean.name;
        email  = recipient.email || bean.email;

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

            translatedRecipient.text = this._formatRecipient(translatedRecipient);
        }

        return translatedRecipient;
    },

    /**
     * Constructs a formatted string, for display purposes, from a recipient object.
     *
     * @param recipient
     * @return {String}
     * @private
     */
    _formatRecipient: function(recipient) {
        return recipient.name ? recipient.name : recipient.email;
    }
})
