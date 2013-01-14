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

    events:{
        'change .existingAddress':'updateExistingAddress',
        'click .btn-edit':'updateExistingProperty',
        'click .removeEmail':'remove',
        'click .addEmail':'_add',
        'change .newEmail': '_newEmailChanged'
    },
    /**
     * On change of the .newEmail input, we want to test if a new e-mail needs to be added
     * @param {Event} evt
     * @private
     */
    _newEmailChanged:function(evt){
        if($(evt.currentTarget).val().length > 0){
            this._add(evt, $(evt.currentTarget).val());
        }
    },
    /**
     * Debounced call to add() method to prevent multiple duplicate e-mails from being added at once
     * @param {Event} evt DOM event
     * @param {String} newEmail e-mail address to add
     * @private
     */
    _add: _.debounce(function(evt, newEmail){
        _.bind(this.add, this, evt, newEmail)();
    }, 100),
    /**
     * Adds email address to dom and model. Note added emails only get checked
     * upon Save button being clicked (which triggers the model validations).
     * @param {Event} evt DOM event
     * @param {String} [newEmail] E-Mail string to be added, will default to value currently in .newEmail input if not provided
     */
    //
    add:function (evt, newEmail) {
        if (!evt) return;
        // Destroy the tooltips open on this button because they wont go away if we re-render
        if ($(evt.currentTarget).tooltip) $(evt.currentTarget).tooltip('hide');
        var newAddress = (newEmail) ? newEmail : this.$('.newEmail').val();
        var existingAddresses = _.clone(this.model.get(this.name)) || [];
        var newObj = {email_address:newAddress};
        if (existingAddresses.length < 1) {
            newObj.primary_address = "1";
        }
        existingAddresses.push(newObj);

        this.updateModel(existingAddresses);
    },
    /**
     * On render, determine which e-mail addresses need anchor tag included
     * @param {Array} value set of e-mail addresses
     * @private
     */
    _render:function() {
        var emails = this.model.get('email');
        _.each(emails, function(emailAddress){
            emailAddress.hasAnchor = emailAddress.opt_out != "1" && emailAddress.invalid_email != "1";
        }, this);
        app.view.Field.prototype._render.call(this);
    },
    /**
     * Removes email address from dom and model
     * @param {Object} evt DOM event
     */
    remove:function (evt) {
        if (!evt) return;
        // Destroy the tooltips open on this button because they wont go away if we rerender
        if ($(evt.currentTarget).tooltip) $(evt.currentTarget).tooltip('hide');
        var emailAddress = $(evt.target).data('parentemail') || $(evt.target).parent().data('parentemail'),
            existingAddresses = _.clone(this.model.get(this.name));
        var wasPrimary = false;
        _.each(existingAddresses, function (emailInfo, index) {
            if (emailInfo.email_address == emailAddress) {
                wasPrimary = existingAddresses[index]['primary_address'] == '1';
                existingAddresses[index] = false;
            }
        });
        // If a removed address was the primary e-mail, we need to pick an existing e-mail and make it the new primary
        if(wasPrimary){
            var address = _.find(existingAddresses, function (emailInfo) {
                return emailInfo;
            });
            if(address){
                address['primary_address'] = '1';
            }
        }
        this.updateModel(existingAddresses);
    },
    /**
     * Updates true false properties on field
     * @param {Event} evt DOM event
     */
    updateExistingProperty:function (evt) {
        if (!evt) return;
        // Destroy the tooltips open on this button because they wont go away if we rerender
        if ($(evt.currentTarget).tooltip) $(evt.currentTarget).tooltip('hide');
        var existingAddresses, emailAddress, parent, target, property;
        target = $(evt.currentTarget);
        parent = target.parent();
        emailAddress = parent.data('parentemail') || parent.parent().data('parentemail');
        property = target.data('emailproperty') || parent.data('emailproperty');
        // need a shallow clone or we won't update the model later
        existingAddresses = _.clone(this.model.get(this.name));

        // Remove all active classes and set all with emails with this property false
        if (property == 'primary_address') {
            existingAddresses = this.massUpdateProperty(existingAddresses, property, "0");
        }

        // Toggle property for clicked button
        _.each(existingAddresses, function (emailInfo, index) {
            if (emailInfo.email_address == emailAddress) {
                if (existingAddresses[index][property] == "1") {
                    existingAddresses[index][property] = "0";
                } else {
                    existingAddresses[index][property] = "1";
                }
            }
        });

        this.updateModel(existingAddresses);
    },
    /**
     * Updates model and triggers appropriate change events;
     * @param value
     */
    updateModel:function(value) {
        this.model.set(this.name, _.compact(value));
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
    massUpdateProperty:function (emails, propName, value) {
        _.each(emails, function (emailInfo, index) {
            emails[index][propName] = value;
        });
        return emails;
    },
    /**
     * Updates existing address that change event was fired on
     * @param {Object} evt DOM event
     */
    updateExistingAddress:function (evt) {
        if (evt && $(evt.currentTarget).val() != $(evt.currentTarget).data('id')) {
            var oldEmail = $(evt.currentTarget).data('id');
            var newEmail = $(evt.currentTarget).val();
            var existingEmails = _.clone(this.model.get(this.name));
            _.each(existingEmails, function (emailInfo, index) {
                if (emailInfo.email_address == oldEmail) {
                    existingEmails[index].email_address = newEmail;
                }
            });
            this.updateModel(existingEmails);
        }
    },
    /**
     * Binds DOM changes to set field value on model.
     * @param {Backbone.Model} model model this field is bound to.
     * @param {String} fieldName field name.
     */
    bindDomChange:function () {

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
    },
    focus:function () {
        this.$('input').first().focus();
    }
})
