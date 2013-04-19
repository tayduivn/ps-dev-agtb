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

    /**
     * Initializes the field's value to be an empty collection.
     *
     * @param options
     */
    initialize: function(options) {
        app.view.Field.prototype.initialize.call(this, options);
        this.model.set(this.name, new Backbone.Collection()); // initialize the value to be an empty collection
    },

    /**
     * Adding, removing and replacing recipients should be done via events on the context. The following are examples
     * of how these events could be triggered:
     *
     * var recipients = [
     *      {email: "foo@bar.com", name: "Foo Bar"},
     *      {email: "biz@baz.com", name: "Biz Baz"}
     * ];
     * this.context.trigger("recipients:to_addresses:add", recipients);
     * this.context.trigger("recipients:to_addresses:remove", recipients[1]);
     * this.context.trigger("recipients:to_addresses:replace", recipients[1]);
     *
     * Change events for the field's value will replace the existing contents of the collection. In most cases this
     * comes from a DOM-change event. The update to the collection is done silently to avoid triggering circular change
     * events. Rendering the field must happen immediately after the collection is updated, so that the contents of the
     * field are synchronized with the collection.
     */
    bindDataChange: function() {
        if (this.model) {
            this.model.on("change:" + this.name, function() {
                this.model.set(this.name, this.unformat(this.model.get(this.name)), {silent: true});
                this.render();
            }, this);
        }

        this.context.on("recipients:" + this.name + ":add", function(recipients) {
            this.model.set(this.name, this._addRecipients(recipients));
        }, this);
        this.context.on("recipients:" + this.name + ":remove", function(recipients) {
            this.model.set(this.name, this._removeRecipients(recipients));
        }, this);
        this.context.on("recipients:" + this.name + ":replace", function(recipients) {
            this.model.set(this.name, this._replaceRecipients(recipients));
        }, this);
    },

    /**
     * All recipients in the collection will be formatted in a comma-delimited string like:
     *
     * "Foo Bar" <foo@bar.com>,"biz@baz.com"<biz@baz.com>
     *
     * @param {Backbone.Collection} value
     * @returns {string}
     */
    format: function(value) {
        var formattedRecipients = [];

        value.each(function(recipient) {
            formattedRecipients.push(this._formatRecipient(recipient));
        }, this);

        return formattedRecipients.join(",");
    },

    /**
     * Returns a collection of recipients that can be used to replace the current collection of recipients.
     *
     * @param value A Backbone Collection of Backbone Models, a single Backbone Model or standard JavaScript object,
     *              a string of one or more comma-delimited recipients, or an array of Backbone Models or standard
     *              JavaScript objects.
     * @returns {Backbone.Collection}
     */
    unformat: function(value) {
        return this._replaceRecipients(value);
    },

    /**
     * Add zero or more recipients to a collection. No matter the format, "models" should become an array of one or
     * more models. This array will be iterated over, adding each individual recipient to the clone of the referenced
     * collection. Once all new recipients have been added, the new collection is returned.
     *
     * Any incoming Backbone Model should at least have an "email" attribute. If a name is associated with the
     * recipient, then the model should have a "name" attribute.
     *
     * @param models A Backbone Collection of Backbone Models, a single Backbone Model or standard JavaScript object,
     *               a string of one or more comma-delimited recipients, or an array of Backbone Models or standard
     *               JavaScript objects.
     * @returns {Backbone.Collection}
     * @private
     */
    _addRecipientsToCollection: function(collection, models) {
        // a clone of the existing recipients stored in the field to protect against triggering events on the field
        // when those events should be deferred
        collection = collection.clone() || new Backbone.Collection();

        if (models instanceof Backbone.Collection) {
            // get the raw array of models to be added since collection.add takes an a single model or array of models
            models = models.models;
        } else if (models instanceof Backbone.Model || (_.isObject(models) && !_.isArray(models))) {
            // wrap the single model in an array so the code below behaves the same whether its a model or a collection
            models = [models];
        } else if (_.isString(models)) {
            // it should be a string like:
            // "Foo Bar" <foo@bar.com>,<foo@bar.com>,foo@bar.com
            // that we want to turn into an array like:
            // [
            //     {email:"foo@bar.com", name:"Foo Bar"},
            //     {email:"foo@bar.com", name:""},
            //     {email:"foo@bar.com", name:""}
            // ]
            models = this._splitRecipients(models);
        } else {
            // it's most likely, and hopefully, an array of objects like:
            // [
            //     {email:"foo@bar.com", name:"Foo Bar"},
            //     {email:"foo@bar.com", name:""},
            //     {email:"foo@bar.com", name:""}
            // ]
            // nothing to do but let the rest of the method iterate over the models
        }

        if (_.isArray(models)) {
            _.each(models, function(recipient) {
                recipient = this._translateRecipient(recipient);

                // only add the recipient if...
                // 1. there is an email address
                // 2. the email address doesn't already exist in the collection
                //TODO: there might be new data that we want, so merge instead of ignore
                if (!_.isEmpty(recipient.email) && collection.where({email: recipient.email}).length == 0) {
                    collection.add(recipient);
                }
            }, this);
        }

        return collection;
    },

    /**
     * Add one or more recipients to the field's value. Once all new recipients have been added, the new collection is
     * returned, which can be used to update the field's value.
     *
     * @param models A Backbone Collection of Backbone Models, a single Backbone Model or standard JavaScript object,
     *               a string of one or more comma-delimited recipients, or an array of Backbone Models or standard
     *               JavaScript objects.
     * @returns {Backbone.Collection}
     * @private
     */
    _addRecipients: function(models) {
        return this._addRecipientsToCollection(this.model.get(this.name), models);
    },

    /**
     * Remove one or more recipients found in the field's value. Once the recipient(s) has(have) been removed, the new
     * collection is returned, which can be used to update the field's value.
     *
     * @param models A Backbone Collection, a single Backbone Model or standard JavaScript object, or an array of
     *               Backbone Models or standard JavaScript objects.
     * @returns {Backbone.Collection}
     * @private
     */
    _removeRecipients: function(models) {
        // a clone of the existing recipients stored in the field to protect against triggering events on the field
        // when those events should be deferred
        var recipients = this.model.get(this.name).clone();

        // removing of a string should only happen in the DOM, which doesn't result in triggering the event that calls
        // this method
        if (!_.isString(models)) {
            if (models instanceof Backbone.Collection) {
                // get the raw array of models to be added since collection.remove takes an a single model or array of
                // models
                models = models.models;
            } else if (models instanceof Backbone.Model || (_.isObject(models) && !_.isArray(models))) {
                // wrap the single model in an array so the code below behaves the same whether its a model or a
                // collection
                models = [models];
            } else {
                // it's most likely, and hopefully, an array of objects
                // nothing to do but let the rest of the method iterate over the models
            }

            _.each(models, function(recipient) {
                recipient = this._translateRecipient(recipient);

                // Backbone.Collection.remove will only remove a recipient if there is a model with a matching ID
                // so, it will fail to remove the recipient if no ID attribute exists
                if (!_.isEmpty(recipient.id)) {
                    recipients.remove(recipient.id);
                }
            }, this);
        }

        return recipients;
    },

    /**
     * Remove all recipients from the field's value and add new recipients if new recipients are passed in. The new
     * collection that is returned can be used to update the field's value.
     *
     * @param models A Backbone Collection of Backbone Models, a single Backbone Model or standard JavaScript object,
     *               a string of one or more comma-delimited recipients, or an array of Backbone Models or standard
     *               JavaScript objects.
     * @returns {Backbone.Collection}
     * @private
     */
    _replaceRecipients: function(models) {
        var collection    = this.model.get(this.name) || new Backbone.Collection(),
            // a clone of the existing recipients stored in the field to protect against triggering events on the field
            // when those events should be deferred
            recipients    = collection.clone(),
            newRecipients = this._addRecipientsToCollection(new Backbone.Collection(), models);

        if (recipients.length > 0) {
            // remove from the existing-collection any existing recipients that are not in the new-collection
            // so that those recipients don't continue to persist with the updated collection
            var notInNewRecipients = recipients.filter(function(recipient) {
                return (newRecipients.where({email: recipient.get("email")}).length == 0);
            });
            recipients.remove(notInNewRecipients);

            // remove from the new-collection any recipients whose email addresses are already found in the
            // existing-collection so that those recipients don't get added by mistake when their ID's don't match
            //TODO: there might be new data that we want, so merge instead of ignore
            var inExistingRecipients = newRecipients.filter(function(recipient) {
                return (recipients.where({email: recipient.get("email")}).length > 0);
            });
            newRecipients.remove(inExistingRecipients);
        }

        // there may be zero or more models to add
        recipients.add(newRecipients.models);

        // guarantees that the data-change event will be triggered even when there are no changes to the collection
        // this is necessary when DOM changes occur that don't actually result in a change to the collection, but
        // the user input should be cleared from the field's value
        collection.reset();

        return recipients;
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
     * Translate a recipient from a standard JavaScript object or Backbone Model an object that the recipients field
     * can understand.
     *
     * @param model
     * @returns {Object}
     * @private
     */
    _translateRecipient: function(model) {
        var recipient = {};

        // can't do anything with the model if it's not an object
        if (_.isObject(model)) {
            var bean,
                id,
                module,
                name,
                email;

            if (model instanceof Backbone.Model) {
                bean   = this._getDataFromBean(model);
                id     = bean.id || bean.email;
                module = bean.module;
                name   = bean.name;
                email  = bean.email;
            } else {
                bean = {};

                // grab values off the bean
                if (model.hasOwnProperty("bean") && model.bean instanceof Backbone.Model) {
                    bean = this._getDataFromBean(model.bean);
                }

                // try to grab values directly first, otherwise use the bean
                id     = model.id || bean.id || model.email || bean.email;
                module = model.module || bean.module;
                name   = model.name || bean.name;
                email  = model.email || bean.email;
            }

            // don't bother with the recipient unless an id is present
            if (!_.isEmpty(id)) {
                recipient.id = id;

                if (!_.isEmpty(email)) {
                    // only set the email if it's actually available
                    recipient.email = email;
                }

                if (!_.isEmpty(module)) {
                    // only set the module if it's actually available
                    recipient.module = module;
                }

                if (!_.isEmpty(name)) {
                    // only set the name if it's actually available
                    recipient.name = name;
                }
            }
        }

        return recipient;
    },

    /**
     * Constructs a formatted string, for display purposes, from a recipient model.
     *
     * If the recipient has attributes for both "email" and "name", then the formatted string will look like:
     * "Name" <email>
     *
     * If the recipient only has an "email" attribute or has an "email" attribute and the "name" attribute is empty,
     * then the formatted string will look like:
     * "email" <email>
     *
     * @param recipient A Backbone Model representing a recipient. Must contain at least an "email" attribute.
     * @return {String}
     * @private
     */
    _formatRecipient: function(recipient) {
        var email = recipient.get("email"),
            name  = recipient.get("name");

        if (_.isEmpty(name)) {
            name = email;
        }

        return '"' + name + '" <' + email + '>';
    },

    /**
     * Converts a string representation of a recipient into an object. Walks the string to parse out the elements that
     * equate to an email address and a name. Valid strings would look like:
     *
     * "Name" <email>
     *     or
     * "email" <email>
     *     or
     * email
     *
     * @param recipient A formatted string representation of a recipient.
     * @return {Object}
     * @private
     */
    _unformatRecipient: function(recipient) {
        var openBracket,
            closeBracket,
            email = "",
            name  = "";

        recipient = recipient.trim();
        openBracket  = recipient.indexOf("<");
        closeBracket = recipient.indexOf(">");

        if (openBracket < 0 && closeBracket < 0) {
            email = recipient;
        } else {
            email = recipient.substring(openBracket + 1, closeBracket).trim();
            name  = recipient.substr(0, openBracket).trim();

            var lastCharPos = name.length - 1;

            if (name.charAt(0) == '"' && name.charAt(lastCharPos)) {
                name = name.substring(1, lastCharPos).trim();
            }
        }

        return {
            email: email,
            name:  name
        };
    },

    /**
     * Use a regular expression to split a string of comma-delimited recipients into an array of recipient objects.
     *
     * @param recipients A string of one or more comma-delimited recipients.
     * @return {Array}
     * @private
     */
    _splitRecipients: function(recipients) {
        var regex   = /(@.*?)\s*?,\s*?/g,
            replace = "::;::";

        if (_.isString(recipients)) {
            // replace comma delimiters with the delimiter defined by "replace" and then split the string on the new
            // delimiter
            recipients = recipients.replace(regex, "$1" + replace).split(replace);

            _.each(recipients, function(recipient, index) {
                recipients[index] = this._unformatRecipient(recipient); // get an object with the recipient's attributes
            }, this);
        } else {
            recipients = [];
        }

        return recipients;
    }
})
