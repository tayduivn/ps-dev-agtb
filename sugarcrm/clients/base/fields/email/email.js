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
    extendsFrom: 'ListeditableField',
    sendEmailFromApp: false,
    events: {
        'change .existingAddress': 'updateExistingAddress',
        'click  .btn-edit':        'toggleExistingAddressProperty',
        'click  .removeEmail':     'removeExistingAddress',
        'click  .addEmail':        'addNewAddress',
        'change .newEmail':        'addNewAddress',
        'click  .composeEmail':    'composeEmail'
    },
    initialize: function(options) {
        options     = options || {};
        options.def = options.def || {};
        
        if (_.isUndefined(options.def.link)) {
            options.def.link = true;
        }
        
        app.view.Field.prototype.initialize.call(this, options);

        // determine if the app should send email according to the has_outbound_email_config user preference
        var hasOutboundEmailConfig = app.user.getPreference("has_outbound_email_config");
        this.sendEmailFromApp      = (hasOutboundEmailConfig === "true");
    },
    /**
     * Event handlers
     */
    addNewAddress: function(evt){
        if (!evt) return;
        //This event can either be triggered by the newEmail input or the newEmail button
        var email = this.$(evt.currentTarget).val() || this.$('.newEmail').val();

        if (email !== "") {
            this._addNewAddress(email);
        }
    },
    updateExistingAddress: function(evt) {
        if (!evt) return;

        var $inputs = this.$('input'),
            $input = this.$(evt.currentTarget),
            index = $inputs.index($input),
            newEmail = $input.val();
        if (newEmail === "") {
            this._removeExistingAddress(index);
        } else {
            this._updateExistingAddress(index, newEmail);
        }
    },
    removeExistingAddress: function(evt) {
        if (!evt) return;

        this._removeTooltips(evt);

        var $deleteButtons = this.$('.removeEmail'),
            $deleteButton = this.$(evt.currentTarget),
            index = $deleteButtons.index($deleteButton);
        this._removeExistingAddress(index);
    },
    toggleExistingAddressProperty: function(evt) {
        if (!evt) return;

        this._removeTooltips(evt);

        var $property = this.$(evt.currentTarget),
            property = $property.data('emailproperty'),
            $properties = this.$('[data-emailproperty='+property+']'),
            index = $properties.index($property);
        this._toggleExistingAddressProperty(index, property);
    },
    /**
     * Manipulations of the emails object
     */
    _addNewAddress: function(email) {
        var dupeAddress;
        var existingAddresses = _.clone(this.model.get(this.name)) || [];
        var oldAddresses = this.model.get(this.name) || [];
        dupeAddress = _.find(oldAddresses, function(address){
            if (address.email_address == email) {
                return true;
            }
        });

        if (dupeAddress) {
            this.render();
            return false;
        }

        var newObj = {email_address:email};
        //If no address exists, set this one as the primary
        if (existingAddresses.length < 1) {
            newObj.primary_address = "1";
        }
        existingAddresses.push(newObj);

        this.updateModel(existingAddresses);
    },
    _updateExistingAddress: function(index, newEmail) {
        var existingAddresses = _.clone(this.model.get(this.name));
        //Simply update the email address
        existingAddresses[index].email_address = newEmail;
        this.updateModel(existingAddresses);
    },
    _toggleExistingAddressProperty: function(index, property) {
        var existingAddresses = _.clone(this.model.get(this.name));
        //If property is primary_address, we want to make sure one and only one primary email is set
        //As a consequence we reset all the primary_address properties to 0 then we toggle property for this index.
        if (property === 'primary_address') {
            existingAddresses[index][property] = "0";
            _.find(existingAddresses, function(email, i) {
                if (email[property] == "1") {
                    existingAddresses[i][property] = "0";
                }
            })
        }
        // Toggle property for this email
        if (existingAddresses[index][property] == "1") {
            existingAddresses[index][property] = "0";
        } else {
            existingAddresses[index][property] = "1";
        }
        this.updateModel(existingAddresses);
    },
    _removeExistingAddress: function(index) {
        var existingAddresses = _.clone(this.model.get(this.name)),
            wasPrimary = existingAddresses[index]['primary_address'] == '1';

        //Reject this index from existing addresses
        existingAddresses = _.reject(existingAddresses, function (emailInfo, i) { return i == index; });

        // If a removed address was the primary email, we still need at least one address to be set as the primary email
        if (wasPrimary) {
            //Let's pick the first one
            var address = _.first(existingAddresses);
            if (address) {
                address.primary_address = '1';
            }
        }
        this.updateModel(existingAddresses);
    },
    /**
     * Updates model and triggers appropriate change events;
     * @param value
     */
    updateModel: function(value) {
        this.model.set(this.name, value);
        this.model.trigger('change');
        this.model.trigger('change:'+this.name);
    },
    /**
     * Mass updates a property for all email addresses
     * @param {Array} emails emails array off a model
     * @param {String} propName
     * @param {Mixed} value
     * @return {Array}
     */
    massUpdateProperty: function(emails, propName, value) {
        _.each(emails, function (emailInfo, index) {
            emails[index][propName] = value;
        });
        return emails;
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
        var $tooltip = $input.next('.error-tooltip');
        if (_.isFunction($tooltip.tooltip)) {
            $tooltip.tooltip({
                container:'body',
                placement:'top',
                trigger:'click'
            });
        }
    },
    /**
     * Binds DOM changes to set field value on model.
     * @param {Backbone.Model} model model this field is bound to.
     * @param {String} fieldName field name.
     */
    bindDomChange: function() {

        // Bind all tooltips on page
        function bindAll(sel) {
            this.$(sel).each(function (index) {
                $(this).tooltip({
                    placement:"bottom"
                });
            });
        }

        bindAll('.btn-edit');
        bindAll('.addEmail');
        bindAll('.removeEmail');

        if(this.tplName === 'list-edit' || this.view.action === 'modal') {
            app.view.Field.prototype.bindDomChange.call(this);
        }
    },

    /**
     * To API representation
     * @param {String|Array} value single email address or set of email addresses
     */
    format: function(value) {
        if (_.isArray(value) && value.length > 0) {
            // got an array of email addresses
            _.each(value, function(email) {
                // On render, determine which e-mail addresses need anchor tag included
                // Needed for handlebars template, can't accomplish this boolean expression with handlebars
                email.hasAnchor = this.def.link && email.opt_out != "1" && email.invalid_email != "1";
            }, this);
        } else if ((_.isString(value) && value !== "") || this.view.action === 'list' || this.view.action === "modal") {
            // expected an array with a single address but got a string or an empty array
            value = [{
                email_address:value,
                primary_address:"1",
                hasAnchor:false,
                _wasNotArray:true
            }];
        }

        value = this.addFlagLabels(value);
        return value;
    },
    addFlagLabels: function(value) {
        var flagStr = "", flagArray;
        var flag2Lbl = {
            primary_address:"LBL_EMAIL_PRIMARY",
            opt_out:"LBL_EMAIL_OPT_OUT",
            invalid_email:"LBL_EMAIL_INVALID"
        };
        _.each(value, function(emailObj, key) {
            flagStr = "";
            flagArray = _.map(emailObj, function (flagValue, key) {
                if (flag2Lbl[key] && flagValue == "1") {
                    return app.lang.get(flag2Lbl[key]);
                }
            });
            flagArray = _.without(flagArray, undefined);
            if (flagArray.length > 0) {
                flagStr = "(" + flagArray.join(", ") + ")";
            }
            emailObj.flagLabel = flagStr;
        })

        return value;
    },
    /**
     * To display representation
     * @param {String|Array} value single email address or set of email addresses
     */
    unformat: function(value) {
        var originalNonArrayValue = null;
        if(this.view.action === 'list' || this.view.action === 'modal') {
            var emails = this.model.get(this.name),
                changed = false;
            if(!_.isArray(emails)){ // emails is empty, initialize array
                emails = [];
            }
            _.each(emails, function(email, index) {
                if(email.primary_address === '1') {
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
                    primary_address: "1",
                    hasAnchor:       false,
                    _wasNotArray:    true
                });
                changed = true;
            }

            if(changed) {
                this.updateModel(changed);
            }
            return emails;
        }

        _.each(value, function(email, index) {
            if (email._wasNotArray) {
                // copy the original string representation
                originalNonArrayValue = email.email_address;
            } else {
                // Remove handlebars cruft from e-mails so we only send valid fields back on save
                value[index] = _.pick(email, 'email_address', 'primary_address', 'opt_out', 'invalid_email');
            }
        }, this);

        if (!_.isNull(originalNonArrayValue)) {
            // reformat the value back to the original string representation
            value = originalNonArrayValue;
        }

        return value;
    },
    focus: function() {
        // this should be zero but lets make sure
        if (this.focusIndex < 0) {
            this.focusIndex = 0;
        }

        if (this.focusIndex >= this.$inputs.length) {
            // done focusing our inputs return false
            this.focusIndex = -1
            return false;
        } else {
            // focus the next item in our list of inputs
            this.$inputs[this.focusIndex].focus();
            this.focusIndex++;
            return true;
        }
    },
    _render: function() {
        app.view.Field.prototype._render.call(this);
        this.$inputs = this.$('input');
        this.focusIndex = 0;
    },
    composeEmail: function(evt) {
        evt.stopPropagation();
        evt.preventDefault();

        var model = app.data.createBean(this.model.module);
        model.copy(this.model);
        model.set('id', this.model.id);

        app.drawer.open({
            layout : 'compose',
            context: {
                create: 'true',
                module: "Emails",
                recipientModel: model
            }
        });
    },
    /**
     * Destroy the tooltips open on this button because they wont go away if we rerender
     */
    _removeTooltips: function(evt) {
        var $el = this.$(evt.currentTarget);
        if (_.isFunction($el.tooltip)) $el.tooltip('hide');
    },
    unbindDom: function() {
        // Unbind all tooltips on page
        var unbindTooltips = _.bind(function(sel) {
            this.$(sel).each(function() {
                $(this).tooltip('destroy');
            });
        }, this);
        unbindTooltips('.btn-edit');
        unbindTooltips('.addEmail');
        unbindTooltips('.removeEmail');

        app.view.Field.prototype.unbindDom.call(this);
    }
})
