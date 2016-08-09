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
             * @param {Object} [options]
             */
            launchSugarEmailClient: function(options) {
                var module = 'Emails';

                //clean the recipient fields before handing off to email compose
                _.each(['to', 'cc', 'bcc'], function(recipientType) {
                    if (options[recipientType]) {
                        options[recipientType] = this._retrieveValidRecipients(options[recipientType]);
                    }
                }, this);

                app.drawer.open({
                    layout: 'create',
                    context: {
                        create: 'true',
                        module: module,
                        prepopulate: options
                    }
                }, _.bind(function(context, model) {
                    if (model) {
                        // Allow for component to perform action after close
                        this.trigger('emailclient:close');

                        // Refresh current list view if it is the Emails list view
                        if (app.controller.context.get('module') === module) {
                            app.controller.context.reloadData();
                        }
                    }
                }, this));
            },

            /**
             * Return recipient list for email compose drawer
             * Strips out any recipients that don't have an email address
             * Picks out primary or first valid address if only bean is specified
             *
             * @param {Array|Object} recipients
             * @return {Array}
             * @private
             */
            _retrieveValidRecipients: function(recipients) {
                var validRecipients = [];

                recipients = _.isArray(recipients) ? recipients : [recipients];
                _.each(recipients, function(recipient) {
                    var validRecipient;
                    var module;

                    if (recipient.bean) {
                        validRecipient = recipient.bean.clone();
                        validRecipient.set('email', this._getEmailAddress(recipient));
                    } else {
                        module = recipient.module || 'EmailAddresses';
                        validRecipient = app.data.createBean(module, recipient);
                    }
                    //only push the recipient if we have a valid email to send to
                    if (validRecipient.get('email')) {
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
                    relatedModel.set(app.utils.deepCopy(model.attributes));
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
                var emailDelim = ',',
                    emails = [],
                    email;

                if (_.isArray(recipients)) {
                    _.each(recipients, function(recipient) {
                        email = this._getEmailAddress(recipient);
                        if (email) {
                            emails.push(email);
                        }
                    }, this);
                } else {
                    emails.push(recipients);
                }

                return emails.join(emailDelim);
            },

            /**
             * Retrieve the best email address off the recipient
             *
             * @param {string|Object} recipient
             * @return {string}
             * @private
             */
            _getEmailAddress: function(recipient) {
                var email;

                if (_.isString(recipient)) {
                    email = recipient;
                } else if (recipient.email) {
                    email = recipient.email;
                } else if (recipient.bean) {
                    email = recipient.bean.get('email_address_used') ||
                        app.utils.getPrimaryEmailAddress(recipient.bean);
                }
                return email;
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
