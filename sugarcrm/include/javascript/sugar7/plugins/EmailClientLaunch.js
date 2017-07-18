/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

(function (app) {
    /**
     * Extract and return the email address from the recipient.
     *
     * @param {Object} recipient
     * @param {Data.Bean} [recipient.email] An EmailAddresses bean.
     * @param {Data.Bean} [recipient.bean] A bean with an email address (e.g.,
     * Contacts, Leads, Users, etc.).
     * @return {Data.Bean} An EmailAddresses bean.
     */
    function getEmailAddress(recipient) {
        var email = app.data.createBean('EmailAddresses');

        if (recipient.email) {
            if (_.isString(recipient.email) && !_.isEmpty(recipient.email)) {
                app.logger.warn(
                    'EmailClientLaunch Plugin: An email address string was provided. An EmailAddresses bean was ' +
                    'expected.'
                );
                email.set('email_address', recipient.email);
            } else if (recipient.email instanceof app.Bean && recipient.email.module === 'EmailAddresses') {
                // If there is no `id` or `email_address`, then fall back to
                // using `recipient.bean`, if available.
                if (!recipient.email.isNew() || recipient.email.get('email_address')) {
                    // The email address was specified, so use it.
                    return recipient.email;
                }
            } else {
                app.logger.warn(
                    'EmailClientLaunch Plugin: An unknown email address type was provided. An EmailAddresses bean ' +
                    'was expected.'
                );
            }
        }

        if (recipient.bean && recipient.bean instanceof app.Bean && !email.get('email_address')) {
            email.set('email_address', app.utils.getPrimaryEmailAddress(recipient.bean));
        }

        return email;
    }

    app.events.on("app:init", function () {
        app.plugins.register('EmailClientLaunch', ['view', 'field'], {

            events: {
                'click a[data-action="email"]': 'launchEmailClient'
            },

            /**
             * If Sugar Email Client used, launch email compose drawer
             *
             * @param event
             */
            launchEmailClient: function(event) {
                var $link = $(event.currentTarget);

                if (this.useSugarEmailClient()) {
                    this.launchSugarEmailClient(this._retrieveEmailOptions($link));
                }
            },

            /**
             * Open the email compose drawer, prepopulated with given options
             *
             * @fires emailclient:close on the component after the drawer is
             * closed to allow a custom action to be performed.
             * @param {Object} [options]
             */
            launchSugarEmailClient: function(options) {
                //clean the recipient fields before handing off to email compose
                _.each(['to', 'cc', 'bcc'], function(recipientType) {
                    var recipients;

                    if (options[recipientType]) {
                        recipients = this._retrieveValidRecipients(options[recipientType]);
                        options[recipientType] = _.map(recipients, function(recipient) {
                            recipient.set('_link', recipientType);

                            return recipient;
                        });
                    }
                }, this);

                app.utils.openEmailCreateDrawer(
                    'compose-email',
                    options,
                    _.bind(function(context, model) {
                        if (model) {
                            this.trigger('emailclient:close');

                            // Refresh the current list view if it is the
                            // Emails list view.
                            if (app.controller.context.get('module') === 'Emails') {
                                app.controller.context.reloadData();
                            }
                        }
                    }, this)
                );
            },

            /**
             * Return recipient list for email compose drawer
             *
             * @param {Array|Object} recipients
             * @return {Array}
             * @private
             */
            _retrieveValidRecipients: function(recipients) {
                var validRecipients = [];

                recipients = recipients || [];

                if (!_.isArray(recipients)) {
                    recipients = [recipients];
                }

                _.each(recipients, function(recipient) {
                    var validRecipient = app.data.createBean('EmailParticipants');
                    var email = getEmailAddress(recipient);
                    var primary;

                    // We can only use the email address if it has an `id`.
                    if (!email.isNew()) {
                        validRecipient.set({
                            email_address_id: email.get('id'),
                            email_address: email.get('email_address')
                        });
                    }

                    if (recipient.bean) {
                        primary = app.utils.getPrimaryEmailAddress(recipient.bean);

                        // Set the parent data if the email address is already
                        // defined. Otherwise, only set the parent data if the
                        // bean's primary email address is valid. We can't send
                        // an email to a bean without a valid email address.
                        if (validRecipient.get('email_address_id') || app.utils.isValidEmailAddress(primary)) {
                            validRecipient.set({
                                parent: _.extend({type: recipient.bean.module}, app.utils.deepCopy(recipient.bean)),
                                parent_type: recipient.bean.module,
                                parent_id: recipient.bean.get('id'),
                                parent_name: app.utils.getRecordName(recipient.bean)
                            });
                        }
                    }

                    // We can only use the recipient if there is an email
                    // address to send to or a bean whose primary email address
                    // we can identify and send to at send-time.
                    if (validRecipient.get('email_address_id') || validRecipient.get('parent')) {
                        validRecipients.push(validRecipient);
                    }
                }, this);

                return validRecipients;
            },

            /**
             * Has the user opted to use the Sugar Email Client
             *
             * @returns {boolean}
             */
            useSugarEmailClient: function() {
                var emailClientPreference = app.user.getPreference('email_client_preference');

                return (emailClientPreference && emailClientPreference.type === 'sugar' && app.acl.hasAccess('edit', 'Emails'));
            },

            /**
             * Extends existing email options, adding the specified ones
             * Also clones the related model passed so we don't modify the original
             *
             * @param options
             */
            addEmailOptions: function(options) {
                this.emailOptions = this.emailOptions || {};
                options = options || {};

                if (options.related) {
                    options.related = this._cloneRelatedModel(options.related);
                }

                this.emailOptions = _.extend({}, this.emailOptions, options);
            },

            /**
             * Returns a copy of the related model for adding to email options
             *
             * @param model
             */
            _cloneRelatedModel: function(model) {
                var relatedModel;

                if (model && model.module) {
                    relatedModel = app.data.createBean(model.module);
                    relatedModel.set(app.utils.deepCopy(model));
                }

                return relatedModel;
            },

            /**
             * Get appropriate href value based on the email client
             *
             * @param options
             * @returns {String}
             * @private
             */
            _getEmailHref: function(options) {
                if (this.useSugarEmailClient()) {
                    return 'javascript:void(0)';
                } else {
                    return this._buildMailToURL(options);
                }
            },

            /**
             * Build a mailto: url using the given options
             *
             * @param {Object} [options] Optional email field values to pass to the email client
             * @param {Array} [options.to]
             * @param {Array} [options.cc]
             * @param {Array} [options.bcc]
             * @param {string} [options.name] Subject
             * @param {string} [options.description] Text Body
             */
            _buildMailToURL: function(options) {
                var mailToUrl = 'mailto:',
                    formattedOptions = {},
                    queryParams = [];

                if (options.to) {
                    mailToUrl += this._formatRecipientsToString(options.to);
                }

                formattedOptions.cc = this._formatRecipientsToString(options.cc);
                formattedOptions.bcc = this._formatRecipientsToString(options.bcc);
                formattedOptions.subject = options.name;
                formattedOptions.body = options.description;

                _.each(['cc', 'bcc', 'subject', 'body'], function(option) {
                    var param;
                    if (!_.isEmpty(formattedOptions[option])) {
                        param = option + '=' + encodeURIComponent(formattedOptions[option]);
                        queryParams.push(param);
                    }
                });

                if (!_.isEmpty(queryParams)) {
                    mailToUrl = mailToUrl + '?' + queryParams.join('&');
                }

                return mailToUrl;
            },

            /**
             * Turns a single recipient or list of recipients
             * into a comma separated list of recipient email addresses
             * Useful for producing string for mailto: recipients
             *
             * @param {string|Array} recipients
             * @returns {string}
             * @private
             */
            _formatRecipientsToString: function(recipients) {
                var emails = [];

                recipients = recipients || [];

                if (!_.isArray(recipients)) {
                    recipients = [recipients];
                }

                _.each(recipients, function(recipient) {
                    var email = getEmailAddress(recipient);

                    if (email.get('email_address')) {
                        emails.push(email.get('email_address'));
                    }
                }, this);

                return emails.join(',');
            },

            /**
             * Build email options object
             * Use options on controller as a base and lay link specific options on top
             *
             * @param $link jQuery selected link element
             * @returns {Object}
             * @private
             */
            _retrieveEmailOptions: function($link) {
                var optionsFromLink = $link.data() || {},
                    optionsFromController = this.emailOptions || {};

                // allow the component implementing this plugin to override optionsFromLink
                // allows us to pass more complex data like models, which are not easily
                // passed via data- attributes.
                if (_.isFunction(this._retrieveEmailOptionsFromLink)) {
                    optionsFromLink = this._retrieveEmailOptionsFromLink($link);
                }

                return _.extend({}, optionsFromController, optionsFromLink);
            },

            /**
             * Updates all the links in the view with the proper href from the current model
             */
            updateEmailLinks: function() {
                var self = this;
                var $emailLinks = this.$('a[data-action="email"]');

                $emailLinks.each(function() {
                    var options = self._retrieveEmailOptions($(this));
                    var href = self._getEmailHref(options);
                    $(this).attr('href', href);
                });
            },

            /**
             * @inheritdoc
             * On render, modify the href appropriately for the correct email client
             */
            onAttach: function () {
                this.on('render', this.updateEmailLinks, this);
            }
        });
    });
})(SUGAR.App);
