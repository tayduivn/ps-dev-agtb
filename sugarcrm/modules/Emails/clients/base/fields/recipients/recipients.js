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

    initialize: function(options) {
        _.bindAll(this);
        app.view.Field.prototype.initialize.call(this, options);

        this.context.off("recipients:" + this.name + ":add", null, this);
        this.context.off("recipients:" + this.name + ":remove", null, this);
        this.context.off("recipients:" + this.name + ":replace", null, this);

        this._replaceRecipients(); // initialize the value to be empty
    },

    bindDataChange: function() {
        if (this.model) {
            this.model.on("change:" + this.name, function() {
                // an array of the field's recipients is maintained alongside the string that is the field's true value
                // update this collection (aka array) anytime the field's value is changed
                this.model.set(this.name + "_collection", this._splitRecipients(this.model.get(this.name)));
                this.render();
            }, this);
        }

        this.context.on("recipients:" + this.name + ":add", this._addRecipients);
        this.context.on("recipients:" + this.name + ":remove", this._removeRecipients);
        this.context.on("recipients:" + this.name + ":replace", this._replaceRecipients);
    },

    /**
     * Add one or more recipients to the field's value. No matter the parameter's format, "models" should become an
     * array of one or more models. This array will be iterated over, adding each individual recipient to the local
     * copy of the existing recipients. Once all new recipients have been added, the field's value is updated.
     *
     * Any incoming Backbone Model should at least have an "email" attribute. If a name is associated with the
     * recipient, then the model should have a "name" attribute.
     *
     * @param models A Backbone Collection of Backbone Models or a single Backbone Model or a string of one or more
     *               comma-delimited recipients.
     * @private
     */
    _addRecipients: function(models) {
        var existingRecipients = this.model.get(this.name);

        if (models instanceof Backbone.Collection) {
            // get the raw array of models to be added since collection.add takes an a single model or array of models
            models = models.models;
        } else if (models instanceof Backbone.Model) {
            // wrap the single model in an array so the code below behaves the same whether its a model or a collection
            models = [models];
        } else {
            // it's probably a string like:
            // "Foo Bar" <foo@bar.com>,<foo@bar.com>,foo@bar.com
            // that we want to turn into an array like:
            // [
            //     new Backbone.Model({email:"foo@bar.com", name:"Foo Bar"}),
            //     new Backbone.Model({email:"foo@bar.com", name:""}),
            //     new Backbone.Model({email:"foo@bar.com", name:""})
            // ]
            models = this._splitRecipients(models);
        }

        _.each(models, function(recipient) {
            recipient = this._formatRecipient(recipient);

            // only the add the recipient if the recipient isn't already in the field's value
            if (!this._hasRecipient(recipient, existingRecipients)) {
                if (!_.isEmpty(existingRecipients)) {
                    existingRecipients += ",";
                }

                existingRecipients += recipient;
            }
        }, this);

        this.model.set(this.name, existingRecipients);
    },

    /**
     * Works similarly to _addRecipients. Removes one or more recipients found in the field's value. Updates the
     * field's value with the recipient(s) removed.
     *
     * @param models A Backbone Collection of Backbone Models or a single Backbone Model.
     * @private
     */
    _removeRecipients: function(models) {
        var existingRecipients = this.model.get(this.name);

        if (models instanceof Backbone.Collection) {
            // get the raw array of models to be added since collection.remove takes an a single model or array of models
            models = models.models;
        } else if (models instanceof Backbone.Model) {
            // wrap the single model in an array so the code below behaves the same whether its a model or a collection
            models = [models];
        } else {
            // removing of recipients is only supported for a recipient that is represented as a Backbone model or a
            // collection of recipients represented as a Backbone collection
            // removing of a string happens in the DOM, which doesn't result in triggering the event that calls
            // this method
            return;
        }

        _.each(models, function(recipient) {
            existingRecipients = this._findAndRemoveRecipient(this._formatRecipient(recipient), existingRecipients);
        }, this);

        this.model.set(this.name, existingRecipients);
    },

    /**
     * Removes all recipients from the field's value and adds new recipients if new recipients are passed in.
     *
     * @param models A Backbone Collection of Backbone Models or a single Backbone Model or a string of one or more
     *               comma-delimited recipients.
     * @private
     */
    _replaceRecipients: function(models) {
        this.model.set(this.name, "");

        if (!_.isEmpty(models)) {
            this._addRecipients(models);
        }
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
            this._addressbookDrawerCallback
        );
    },

    _addressbookDrawerCallback: function(recipients) {
        this._addRecipients(recipients);
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
            name  = recipient.get("name"),
            emailAddress;

        if (_.isEmpty(name)) {
            name = email;
        }

        emailAddress = '"' + name + '" <' + email + '>';

        return emailAddress;
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
     * Use a regular expression to split a string of comma-delimited recipients into an array of Backbone Models.
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
                var attributes = this._unformatRecipient(recipient); // get an object with the recipient's attributes

                recipients[index] = new Backbone.Model(attributes);
            }, this);
        } else {
            recipients = [];
        }

        return recipients;
    },

    /**
     * Find a recipient (needle) in a string (haystack) and remove the recipient, returning the new string.
     *
     * @param needle   A formatted string representation of a recipient.
     * @param haystack A string of one or more comma-delimited recipients.
     * @return {String}
     * @private
     */
    _findAndRemoveRecipient: function(needle, haystack) {
        // add the comma-prefix back in case the string was removed from the middle
        var result = haystack.replace(this._formatRecipientForRegex(needle), "$1");

        // need to remove a comma if it exists at the beginning of the string since it's a remnant of putting back the
        // $1 match in String.replace above
        if (result.charAt(0) == ",") {
            result = result.substr(1);
        }

        return result.trim();
    },

    /**
     * Determine if a recipient (needle) is found in a string (haystack).
     *
     * @param needle   A formatted string representation of a recipient.
     * @param haystack A string of one or more comma-delimited recipients.
     * @return {Boolean}
     * @private
     */
    _hasRecipient: function(needle, haystack) {
        if (haystack.search(this._formatRecipientForRegex(needle)) != -1) {
            return true;
        }

        return false;
    },

    /**
     * A formatted string representation of a recipient is likely to contain characters that must be escaped before the
     * string can be used in a regular expression. This method builds a RegExp object that can be used to locate a
     * recipient within a string of one or more comma-delimited recipients.
     *
     * @param recipient A formatted string representation of a recipient.
     * @return {RegExp}
     * @private
     */
    _formatRecipientForRegex: function(recipient) {
        // need to escape special characters in a string to be used within a regular expression
        // source: http://stackoverflow.com/a/13157996/1771599
        recipient = recipient.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&");

        // build the string to pass into the RegExp constructor
        recipient = "(?:^|[,])" + recipient + "([,]|$)";

        return new RegExp(recipient, "gi");
    }
})
