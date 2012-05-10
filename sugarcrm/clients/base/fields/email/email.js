({
    fieldTag: "input",

    events: {
        'change .existing': 'updateExistingAddress',
        'click button': 'updateExistingProperty',
        'click .removeEmail': 'remove',
        'change .newEmail': 'add'
    },
    /**
     * Adds email address to dom and mdoel
     */
    add: function() {
        var newAddress = this.$('.newEmail').val(),
            newObj = {email: newAddress},
            existingAddresses = this.model.get(this.name) || [];

        if (existingAddresses.length<1) {
            newObj.is_primary = true;
        }
        existingAddresses.push(newObj);
        this.model.set(this.name, existingAddresses);
    },
    /**
     * Removes email address from dom and model
     * @param {Object} event
     */
    remove: function(event) {
        var emailAddress = $(event.target).data('parentemail') || $(event.target).parent().data('parentemail'),
            existingAddresses = this.model.get(this.name);

        _.each(existingAddresses, function(emailInfo, index) {
            if (emailInfo.email == emailAddress) {
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

        if (property == 'is_primary') {
            existingAddresses=this.massUpdateProperty(existingAddresses, property, false);
            this.$('.is_primary').removeClass('active');
        }

        _.each(existingAddresses, function(emailInfo, index) {
            if (emailInfo.email == emailAddress) {
                if (existingAddresses[index][property]) {
                    existingAddresses[index][property] = false;
                } else {
                    existingAddresses[index][property] = true;
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
                if (emailInfo.email == oldEmail) {
                    existingEmails[index].email = newEmail;
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
    }

})