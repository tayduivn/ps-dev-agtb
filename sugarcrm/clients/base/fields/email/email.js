({
    fieldTag: "input",

    events: {
        'change .existing': 'updateExistingAddress',
        'click .btn-edit': 'updateExistingProperty',
        'click .removeEmail': 'remove',
        'change .newEmail': 'add'
    },
    /**
     * Adds email address to dom and mdoel
     */
    add: function() {
        var newAddress = this.$('.newEmail').val(),
            existingAddresses = this.model.get(this.name) || [];
        var newObj = {email_address: newAddress};
        if (existingAddresses.length<1) {
            newObj.primary_address = true;
        }
        existingAddresses.push(newObj);
        this.model.set(this.name, existingAddresses);
        this.render();
    },
    /**
     * Removes email address from dom and model
     * @param {Object} event
     */
    remove: function(event) {
        var emailAddress = $(event.target).data('parentemail') || $(event.target).parent().data('parentemail'),
            existingAddresses = this.model.get(this.name);

        _.each(existingAddresses, function(emailInfo, index) {
            if (emailInfo.email_address == emailAddress) {
                existingAddresses[index] = false;
            }
        });

        this.model.set(this.name, _.compact(existingAddresses));
        this.$('[data-emailaddress="' + emailAddress + '"]').remove();
    },
    /**
     * Updates true false properties on field
     * @param event
     */
    updateExistingProperty: function(event) {
        var emailAddress = $(event.target).parent().data('parentemail') || $(event.target).parent().parent().data('parentemail'),
            property = $(event.target).data('emailproperty') || $(event.target).parent().data('emailproperty'),
            existingAddresses = this.model.get(this.name);

        if (property == 'primary_address') {
            existingAddresses=this.massUpdateProperty(existingAddresses, property, "0");
            this.$('.is_primary').removeClass('active');
        }

        _.each(existingAddresses, function(emailInfo, index) {
            if (emailInfo.email_address == emailAddress) {
                if (existingAddresses[index][property] == "1") {
                    existingAddresses[index][property] = "0";
                } else {
                    existingAddresses[index][property] = "1";
                }
            }
        });

        $(event.target).toggleClass('active');
        $(event.target).parent().toggleClass('active');
    },
    /**
     * Mass updates a property for all email addresses
     * @param {Array} emails emails array off a model
     * @param {String} propName
     * @param {Mixed} value
     * @return {Array}
     */
    massUpdateProperty: function(emails, propName, value) {
        _.each(emails, function(emailInfo, index) {
            emails[index][propName] = value;
        })
        return emails;
    },
    /**
     * Updates existing address that change event was fired on
     * @param {Object} event
     */
    updateExistingAddress: function(event) {
        if ($(event.target).val() != $(event.target).attr('id')) {
            var oldEmail = $(event.target).attr('id');
            var newEmail = $(event.target).val();
            var existingEmails = this.model.get(this.name);
            _.each(existingEmails, function(emailInfo, index) {
                if (emailInfo.email_address == oldEmail) {
                    existingEmails[index].email_address = newEmail;
                }
            });
            this.render();
        }
    },
    /**
     * Binds DOM changes to set field value on model.
     * @param {Backbone.Model} model model this field is bound to.
     * @param {String} fieldName field name.
     */
    bindDomChange: function(model, fieldName) {
        // empty because we are handling this with the events and callbacks above
    },

    /**
     * Handles how validation errors are displayed on fields
     *
     * This method should be implemented in the extension dir per platform
     *
     * @param {Object} errors hash of validation errors
     */
    handleValidationError: function(errors) {
        var self = this;
        _.each(errors,function(emailAddress){
            this.$('[data-emailaddress="' + emailAddress + '"]').addClass("error");
        });

        this.$('.help-block').html("");
        this.$('.help-group').addClass("error");
        _.each(errors, function(errorContext, errorName) {
            self.$('.help-block').append("<br>"+app.error.getErrorString(errorName,errorContext));
        });
    }

})