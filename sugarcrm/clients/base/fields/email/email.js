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
    fieldTag: "input",

    events: {
        'change .existing': 'updateExistingAddress',
        'click .btn-edit': 'updateExistingProperty',
        'click .removeEmail': 'remove',
        'change .newEmail': 'add'
    },
    /**
     * Adds email address to dom and model. Note added emails only get checked
     * upon Save button being clicked (which triggers the model validations).
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

        this.$('.newEmail').removeClass('newEmail');//Bug 56555
        this.render();
    },
    /**
     * Removes email address from dom and model
     * @param {Object} event
     */
    remove: function(evt) {
        if(evt) {
            var emailAddress = $(evt.target).data('parentemail') || $(evt.target).parent().data('parentemail'),
                existingAddresses = this.model.get(this.name);

            _.each(existingAddresses, function(emailInfo, index) {
                if (emailInfo.email_address == emailAddress) {
                    existingAddresses[index] = false;
                }
            });
            this.model.set(this.name, _.compact(existingAddresses));
            this.$('[data-emailaddress="' + emailAddress + '"]').remove();
        }
    },
    /**
     * Updates true false properties on field
     * @param event
     */
    updateExistingProperty: function(evt) {
        var existingAddresses, emailAddress, parent, target, property;
        evt.stopPropagation();
        evt.preventDefault();
        target = $(evt.target);
        parent = target.parent();
        emailAddress = parent.data('parentemail') || parent.parent().data('parentemail');
        property = $(evt.target).data('emailproperty') || parent.data('emailproperty');
        existingAddresses = this.model.get(this.name);

        // Remove all active classes and set all with emails with this property false
        if (property == 'primary_address') {
            existingAddresses=this.massUpdateProperty(existingAddresses, property, "0");
            this.$('.is_primary').removeClass('active');
        }

        // Now toggle currently clicked
        $(target).toggleClass('active');
        $(parent).toggleClass('active');

        // Toggle property for clicked button
        _.each(existingAddresses, function(emailInfo, index) {
            if (emailInfo.email_address == emailAddress) {
                if (existingAddresses[index][property] == "1") {
                    existingAddresses[index][property] = "0";
                } else {
                    existingAddresses[index][property] = "1";
                }
            }
        });
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
    updateExistingAddress: function(evt) {
        if ($(evt.target).val() != $(evt.target).attr('id')) {
            var oldEmail = $(evt.target).attr('id');
            var newEmail = $(evt.target).val();
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
    bindDomChange: function() {
        // This condition allows you to create a custom edit template for the `email` field, and let it behave as a
        // generic `text` field. You should attach a `textField` class to the input element, and on 'save' action
        // format the email as an array with sugar parameters.

        if (this.$el.find(this.fieldTag).length === 1 && this.$el.find(this.fieldTag).hasClass('textField')) {
            app.view.Field.prototype.bindDomChange.call(this);
        } else {
            // Bind all tooltips on page
            function bindAll(sel) {
                this.$(sel).each(function(index) {
                    $(this).tooltip({
                        placement: "bottom"
                    });
                });
            }
            bindAll('.btn-edit');
            bindAll('.addEmail');
            bindAll('.removeEmail');

            this.delegateEvents();
        }
    }
})
