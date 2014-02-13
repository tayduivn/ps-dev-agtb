/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    events: {
        'change .existingAddress': 'updateExistingAddress',
        'click  .btn-edit':        'toggleExistingAddressProperty',
        'click  .removeEmail':     'removeExistingAddress',
        'click  .addEmail':        'addNewAddress',
        'change .newEmail':        'addNewAddress'
    },
    _flag2Deco: {
        primary_address: {lbl: "LBL_EMAIL_PRIMARY", cl: "primary"},
        opt_out: {lbl: "LBL_EMAIL_OPT_OUT", cl: "opted-out"},
        invalid_email: {lbl: "LBL_EMAIL_INVALID", cl: "invalid"}
    },
    plugins: ['Tooltip', 'ListEditable', 'EmailClientLaunch'],

    /**
     * @inheritdoc
     * @param options
     */
    initialize: function(options) {
        options     = options || {};
        options.def = options.def || {};

        // By default, emails should be links.
        if (_.isUndefined(options.def.link)) {
            options.def.link = true;
        }

        this._super("initialize", [options]);

        //set model as the related record when composing an email (copy is made by plugin)
        this.emailOptions = {related: this.model};
    },

    /**
     * When data changes, re-render the field only if it is not on edit (see MAR-1617).
     * @inheritdoc
     */
    bindDataChange: function() {
        this.model.on('change:' + this.name, function() {
            if (this.action !== 'edit') {
                this.render();
            }
        }, this);
    },

    /**
     * In edit mode, render email input fields using the edit-email-field template.
     * @inheritdoc
     * @private
     */
    _render: function() {
        var emailsHtml = '';

        this._super("_render");

        if (this.tplName === 'edit') {
            // Add email input fields for edit
            _.each(this.value, function(email) {
                emailsHtml += this._buildEmailFieldHtml(email);
            }, this);
            this.$el.prepend(emailsHtml);
        }
    },

    /**
     * Get HTML for email input field.
     * @param {Object} email
     * @returns {Object}
     * @private
     */
    _buildEmailFieldHtml: function(email) {
        var editEmailFieldTemplate = app.template.getField('email', 'edit-email-field'),
            emails = this.model.get(this.name),
            index = _.indexOf(emails, email);

        return editEmailFieldTemplate({
            max_length: this.def.len,
            index: index === -1 ? emails.length-1 : index,
            email_address: email.email_address,
            primary_address: email.primary_address,
            opt_out: email.opt_out,
            invalid_email: email.invalid_email
        });
    },

    /**
     * Event handler to add a new address field.
     * @param {Event} evt
     */
    addNewAddress: function(evt){
        if (!evt) return;

        var email = this.$(evt.currentTarget).val() || this.$('.newEmail').val(),
            currentValue,
            emailFieldHtml,
            $newEmailField;

        email = $.trim(email);

        if ((email !== '') && (this._addNewAddressToModel(email))) {
            // build the new email field
            currentValue = this.model.get(this.name);
            emailFieldHtml = this._buildEmailFieldHtml({
                email_address: email,
                primary_address: currentValue && (currentValue.length === 1),
                opt_out: false,
                invalid_email: false
            });

            // append the new field before the new email input
            $newEmailField = this._getNewEmailField()
                .closest('.email')
                .before(emailFieldHtml);

            // add tooltips
            this.addPluginTooltips($newEmailField.prev());
        }

        this._clearNewAddressField();
    },

    /**
     * Event handler to update an email address.
     * @param {Event} evt
     */
    updateExistingAddress: function(evt) {
        if (!evt) return;

        var $inputs = this.$('.existingAddress'),
            $input = this.$(evt.currentTarget),
            index = $inputs.index($input),
            newEmail = $input.val(),
            primaryRemoved;

        newEmail = $.trim(newEmail);

        if (newEmail === '') {
            // remove email if email is empty
            primaryRemoved = this._removeExistingAddressInModel(index);

            $input
                .closest('.email')
                .remove();

            if (primaryRemoved) {
                this.$('[data-emailproperty=primary_address]')
                    .first()
                    .addClass('active');
            }
        } else {
            this._updateExistingAddressInModel(index, newEmail);
        }
    },

    /**
     * Event handler to remove an email address.
     * @param {Event} evt
     */
    removeExistingAddress: function(evt) {
        if (!evt) return;

        var $deleteButtons = this.$('.removeEmail'),
            $deleteButton = this.$(evt.currentTarget),
            index = $deleteButtons.index($deleteButton),
            primaryRemoved,
            $removeThisField;

        primaryRemoved = this._removeExistingAddressInModel(index);

        $removeThisField = $deleteButton.closest('.email');
        this.removePluginTooltips($removeThisField); // remove tooltips
        $removeThisField.remove();

        if (primaryRemoved) {
            // If primary has been removed, the first email address is the primary address.
            this.$('[data-emailproperty=primary_address]')
                .first()
                .addClass('active');
        }
    },

    /**
     * Event handler to toggle email address properties.
     * @param {Event} evt
     */
    toggleExistingAddressProperty: function(evt) {
        if (!evt) return;

        var $property = this.$(evt.currentTarget),
            property = $property.data('emailproperty'),
            $properties = this.$('[data-emailproperty='+property+']'),
            index = $properties.index($property);

        if (property === 'primary_address') {
            $properties.removeClass('active');
        }

        this._toggleExistingAddressPropertyInModel(index, property);
    },

    /**
     * Add the new email address to the model.
     * @param {String} email
     * @returns {Boolean} Returns true when a new email is added.  Returns false if duplicate is found,
     *          and was not added to the model.
     * @private
     */
    _addNewAddressToModel: function(email) {
        var existingAddresses = this.model.get(this.name) ? app.utils.deepCopy(this.model.get(this.name)) : [],
            dupeAddress = _.find(existingAddresses, function(address){
                return (address.email_address === email);
            }),
            success = false;

        if (_.isUndefined(dupeAddress)) {
            existingAddresses.push({
                email_address: email,
                primary_address: (existingAddresses.length === 0)
            });
            this.model.set(this.name, existingAddresses);
            success = true;
        }

        return success;
    },

    /**
     * Update email address in the model.
     * @param {Number} index
     * @param {String} newEmail
     * @private
     */
    _updateExistingAddressInModel: function(index, newEmail) {
        var existingAddresses = app.utils.deepCopy(this.model.get(this.name));
        //Simply update the email address
        existingAddresses[index].email_address = newEmail;
        this.model.set(this.name, existingAddresses);
    },

    /**
     * Toggle email address properties: primary, opt-out, and invalid.
     * @param {Number} index
     * @param {String} property
     * @private
     */
    _toggleExistingAddressPropertyInModel: function(index, property) {
        var existingAddresses = app.utils.deepCopy(this.model.get(this.name));

        //If property is primary_address, we want to make sure one and only one primary email is set
        //As a consequence we reset all the primary_address properties to 0 then we toggle property for this index.
        if (property === 'primary_address') {
            existingAddresses[index][property] = false;
            _.each(existingAddresses, function(email, i) {
                if (email[property]) {
                    existingAddresses[i][property] = false;
                }
            });
        }

        // Toggle property for this email
        if (existingAddresses[index][property]) {
            existingAddresses[index][property] = false;
        } else {
            existingAddresses[index][property] = true;
        }

        this.model.set(this.name, existingAddresses);
    },

    /**
     * Remove email address from the model.
     * @param {Number} index
     * @returns {Boolean} Returns true if the removed address was the primary address.
     * @private
     */
    _removeExistingAddressInModel: function(index) {
        var existingAddresses = app.utils.deepCopy(this.model.get(this.name)),
            primaryAddressRemoved = !!existingAddresses[index]['primary_address'];

        //Reject this index from existing addresses
        existingAddresses = _.reject(existingAddresses, function (emailInfo, i) { return i == index; });

        // If a removed address was the primary email, we still need at least one address to be set as the primary email
        if (primaryAddressRemoved) {
            //Let's pick the first one
            var address = _.first(existingAddresses);
            if (address) {
                address.primary_address = true;
            }
        }

        this.model.set(this.name, existingAddresses);
        return primaryAddressRemoved;
    },

    /**
     * Clear out the new email address field.
     * @private
     */
    _clearNewAddressField: function() {
        this._getNewEmailField()
            .val('')
            .focus();
    },

    /**
     * Get the new email address input field.
     * @returns {jQuery}
     * @private
     */
    _getNewEmailField: function() {
        return this.$('.newEmail');
    },

    /**
     * Custom error styling for the e-mail field
     * @param {Object} errors
     * @override BaseField
     */
    decorateError: function(errors){
        var emails;

        this.$el.closest('.record-cell').addClass("error");

        //Select all existing emails
        emails = this.$('input:not(.newEmail)');

        _.each(errors, function(errorContext, errorName) {
            //For `email` validator the error is specific to an email
            if (errorName === 'email' || errorName === 'duplicateEmail') {

                // For each of our `sub-email` fields
                _.each(emails, function(e) {
                    var $email = this.$(e),
                        email = $email.val();

                    var isError = _.find(errorContext, function(emailError) { return emailError === email; });
                    // if we're on an email sub field where error occurred, add error styling
                    if(!_.isUndefined(isError)) {
                        this._addErrorDecoration($email, errorName, [isError]);
                    }
                }, this);
            //For required or primaryEmail we want to decorate only the first email
            } else {
                var $email = this.$('input:first');
                this._addErrorDecoration($email, errorName, errorContext);
            }
        }, this);
    },

    _addErrorDecoration: function($input, errorName, errorContext) {
        var isWrapped = $input.parent().hasClass('input-append');
        if (!isWrapped)
            $input.wrap('<div class="input-append error '+this.fieldTag+'">');
        $input.next('.error-tooltip').remove();
        $input.after(this.exclamationMarkTemplate([app.error.getErrorString(errorName, errorContext)]));
        this.createErrorTooltips($input.next('.error-tooltip'));
    },

    /**
     * Binds DOM changes to set field value on model.
     * @param {Backbone.Model} model model this field is bound to.
     * @param {String} fieldName field name.
     */
    bindDomChange: function() {
        if(this.tplName === 'list-edit') {
            this._super("bindDomChange");
        }
    },

    /**
     * To display representation
     * @param {String|Array} value single email address or set of email addresses
     */
    format: function(value) {
        value = app.utils.deepCopy(value);
        if (_.isArray(value) && value.length > 0) {
            // got an array of email addresses
            _.each(value, function(email) {
                // On render, determine which e-mail addresses need anchor tag included
                // Needed for handlebars template, can't accomplish this boolean expression with handlebars
                email.hasAnchor = this.def.link && !email.opt_out && !email.invalid_email;
            }, this);
        } else if ((_.isString(value) && value !== "") || this.view.action === 'list') {
            // expected an array with a single address but got a string or an empty array
            value = [{
                email_address:value,
                primary_address:true,
                hasAnchor:true
            }];
        }

        value = this.addFlagLabels(value);
        return value;
    },

    /**
     * Build label that gets displayed in tooltips.
     * @param {Object} value
     * @returns {Object}
     */
    addFlagLabels: function(value) {
        var flagStr = "", flagArray;
        _.each(value, function(emailObj) {
            flagStr = "";
            flagArray = _.map(emailObj, function (flagValue, key) {
                if (!_.isUndefined(this._flag2Deco[key]) && this._flag2Deco[key].lbl && flagValue) {
                    return app.lang.get(this._flag2Deco[key].lbl);
                }
            }, this);
            flagArray = _.without(flagArray, undefined);
            if (flagArray.length > 0) {
                flagStr = flagArray.join(", ");
            }
            emailObj.flagLabel = flagStr;
        }, this);
        return value;
    },

    /**
     * To API representation
     * @param {String|Array} value single email address or set of email addresses
     */
    unformat: function(value) {
        if(this.view.action === 'list') {
            var emails = this.model.get(this.name),
                changed = false;
            if(!_.isArray(emails)){ // emails is empty, initialize array
                emails = [];
            }
            _.each(emails, function(email, index) {
                if(email.primary_address) {
                    if(email.email_address !== value) {
                        changed = true;
                        emails[index].email_address = value;
                    }
                }
            }, this);

            // Adding a new email
            if (emails.length == 0) {
                emails.push({
                    email_address:   value,
                    primary_address: true
                });
                changed = true;
            }

            if(changed) {
                emails = app.utils.deepCopy(emails);
            }
            return emails;
        }
    },

    /**
     * Apply focus on the new email input field.
     */
    focus: function () {
        if(this.action !== 'disabled') {
            this._getNewEmailField().focus();
        }
    },

    /**
     * Retrieve link specific email options for launching the email client
     * Builds upon emailOptions on this
     *
     * @param $link
     * @private
     */
    _retrieveEmailOptionsFromLink: function($link) {
        return {
            to_addresses: [
                {
                    email: $link.data('email-to'),
                    bean: this.emailOptions.related
                }
            ]
        };
    }
})
