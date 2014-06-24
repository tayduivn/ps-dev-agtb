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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Fields.Base.EmailTextField
 * @alias SUGAR.App.view.fields.BaseEmailTextField
 * @extends View.Field
 */
({
    // The purpose of email-text is to provide a simpler textfield email
    // when our main email widget is overkill. For example, the first time
    // login wizard uses email-text. Note that the email mutated is the
    // primary_address email.
    initialize: function(options) {
        options     = options || {};
        options.def = options.def || {};
        if (_.isUndefined(options.def.link)) {
            options.def.link = true;
        }
        app.view.Field.prototype.initialize.call(this, options);
    },
   /**
     * Formats for display
     * If we have a proper email value from model we parse out just
     * the primary address part since we're using a simple text field.
     * @param  {Object} value The value retrieved from model for email
     * @return {Object}       Normalized email value for simple field
     */
    format: function(value) {
        if(_.isArray(value)) {
            var primaryEmail = _.find(value, function(email) {
                return email.primary_address && email.primary_address !== "0";
            });
            return primaryEmail ? primaryEmail.email_address : '';
        }
        return value;
    },
    /**
     * Prepares email for going back to API
     * @param  {Object} value The value
     * @return {Object}       API ready value for email
     */
    unformat: function(value) {
        var self = this,
            emails = this.model.get('email'),
            changed = false;
        if(!_.isArray(emails)){emails = [];}
        _.each(emails, function(email, index) {
            // If we find a primary address and its email_address is different
            if(email.primary_address &&
                email.primary_address !== "0" &&
                email.email_address !== value)
            {
                changed = true;
                emails[index].email_address = value;
            }
        }, this);
        // If brand new email we push a primary address
        if (emails.length == 0) {
            emails.push({
                email_address:   value,
                primary_address: "1",
                hasAnchor:       false,
                _wasNotArray:    true
            });
            changed = true;
        }
        if (changed) {
            this.model.set(this.name, emails);
            this.model.trigger('change:' + this.name, this, emails);
        }
        return emails;
    }
})
