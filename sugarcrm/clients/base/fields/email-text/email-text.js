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
    // The purpose of email-text is to provide a simpler textfield email
    // when our main email widget is overkill. For example, the first time
    // login wizard uses email-text. Note that the email mutated is the
    // primary_address email.
    useSugarEmailClient: false,
    initialize: function(options) {
        options     = options || {};
        options.def = options.def || {};
        if (_.isUndefined(options.def.link)) {
            options.def.link = true;
        }
        app.view.Field.prototype.initialize.call(this, options);
        this.useSugarEmailClient = (app.user.getPreference("use_sugar_email_client") === "true");
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
            this.model.trigger('change:'+this.name);
        }
        return emails;
    },
    /**
     * Custom error styling for the e-mail field
     * @param {Object} errors
     * @override BaseField
     */
    decorateError: function(errors){
        this.$el.closest('.record-cell').addClass("error");
        _.each(errors, function(errorContext, errorName) {
            this._addErrorDecoration(errorName, errorContext);
        }, this);
    },
    /**
     * Custom error styling for the e-mail field
     * @param {Object} errors
     * @override BaseField
     */
    _addErrorDecoration: function(errorName, errorContext) {
        // this.$el.closest('.record-cell').addClass("error");
        var inp = this.$('input[name=email]');
        var parent = $(inp).parent();
        var isWrapped = parent.hasClass('input-append error');
        if (!isWrapped) {
            parent.wrap('<div class="input-append error '+this.fieldTag+'">');
        }
        inp.next('.error-tooltip').remove();
        inp.after(this.exclamationMarkTemplate([app.error.getErrorString(errorName, errorContext)]));
        var tooltip = inp.next('.error-tooltip');
        if (_.isFunction(tooltip.tooltip)) {
            tooltip.tooltip({
                container:'body',
                placement:'top',
                trigger:'click'
            });
        }
    }
})
