({
    events: {
        "click .btn": "_showAddressBook"
    },

    initialize: function(options) {
        app.view.Field.prototype.initialize.call(this, options);

        this.context.off("recipients:" + this.name + ":add", null, this);
        this.context.off("recipients:" + this.name + ":remove", null, this);
        this.context.off("recipients:" + this.name + ":replace", null, this);

        this._replaceRecipients();
    },

    bindDataChange: function() {
        if (this.model) {
            this.model.on("change:" + this.name, function() {
                this.model.set(this.name + "_collection", this._splitRecipients(this.model.get(this.name)));
                this.render();
            }, this);
        }

        this.context.on("recipients:" + this.name + ":add", this._addRecipients, this);
        this.context.on("recipients:" + this.name + ":remove", this._removeRecipients, this);
        this.context.on("recipients:" + this.name + ":replace", this._replaceRecipients, this);
    },

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
            // that we want to turn into:
            // [{email:"foo@bar.com", name:"Foo Bar"},{email:"foo@bar.com", name:""},{email:"foo@bar.com", name:""}]
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

    _removeRecipients: function(models) {
        var existingRecipients = this.model.get(this.name);

        if (models instanceof Backbone.Collection) {
            // get the raw array of models to be added since collection.remove takes an a single model or array of models
            models = models.models;
        } else if (models instanceof Backbone.Model) {
            // wrap the single model in an array so the code below behaves the same whether its a model or a collection
            models = [models];
        } else {
            // there is no concept of removing a recipient represented as a Backbone model or a collection of
            // recipients represented as a Backbone collection
            // removing of a string happens in the DOM, which doesn't result in triggering the event that calls
            // this method
            return;
        }

        _.each(models, function(recipient) {
            existingRecipients = this._findAndRemoveRecipient(this._formatRecipient(recipient), existingRecipients);
        }, this);

        this.model.set(this.name, existingRecipients);
    },

    _replaceRecipients: function(models) {
        this.model.set(this.name, "");

        if (!_.isEmpty(models)) {
            this._addRecipients(models);
        }
    },

    _showAddressBook: function(evt) {
        // the first step is to inject the target field name into the drawer's context that originates in metadata
        // clone the metadata because you don't want to permanently change the actual context as it exists in the
        // metadata
        // additionally, cloning and extending must happen at a shallow level because deep cloning is not currently
        // possible with underscorejs

        // get the drawer component so the metaComponents' context can be cloned
        var composeAddressBookDrawer = this.view.layout.getComponent("compose-addressbook-drawer");

        // merge the name of the target field into the context to be passed to the show event
        var context = _.clone(composeAddressBookDrawer.metaComponents[0].context);
            context = _.extend(context, { target: this.name });

        // build a new metaComponents to be passed into the show event
        var metaComponents = _.clone(composeAddressBookDrawer.metaComponents[0]);
            metaComponents = [_.extend(metaComponents, { context: context })];

        // open the drawer layout
        this.view.layout.trigger("compose:addressbook:open", { components: metaComponents }, this);
    },

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

    _unformatRecipient: function(recipient) {
        var openBracket  = recipient.indexOf("<"),
            closeBracket = recipient.indexOf(">"),
            email        = "",
            name         = "";

        recipient = recipient.trim();

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

    _splitRecipients: function(recipients) {
        var regex   = /(@.*?)\s*?,\s*?/g,
            replace = "::;::";

        recipients = recipients.replace(regex, "$1" + replace).split(replace);

        _.each(recipients, function(recipient, index) {
            var attributes = this._unformatRecipient(recipient);

            recipients[index] = new Backbone.Model(attributes);
        }, this);

        return recipients;
    },

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

    _hasRecipient: function(needle, haystack) {
        if (haystack.search(this._formatRecipientForRegex(needle)) != -1) {
            return true;
        }

        return false;
    },

    _formatRecipientForRegex: function(recipient) {
        if (!RegExp.escape) {
            // needed for escaping special characters in a string to be used within a regular expression
            // source: http://stackoverflow.com/a/13157996/1771599
            RegExp.escape = function(value) {
                return value.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&");
            }
        }

        recipient = RegExp.escape(recipient);
        recipient = "(?:^|[,])" + recipient + "([,]|$)";

        return new RegExp(recipient, "gi");
    }
})
