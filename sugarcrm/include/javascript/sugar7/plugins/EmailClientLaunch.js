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
             * @fires emailclient:close on the component after the drawer is
             * closed to allow a custom action to be performed.
             * @param {Object} [options]
             */
            launchSugarEmailClient: function(options) {
                var fieldMap = {
                    from: 'from_link',
                    to: 'to_link',
                    cc: 'cc_link',
                    bcc: 'bcc_link'
                };

                //clean the recipient fields before handing off to email compose
                _.each(['to', 'cc', 'bcc'], function(recipientType) {
                    var linkName = fieldMap[recipientType];
                    var recipients;

                    if (options[recipientType]) {
                        recipients = this._retrieveValidRecipients(options[recipientType]);
                        options[recipientType] = _.map(recipients, function(recipient) {
                            recipient.set('_link', linkName);

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
                    var validRecipient = app.data.createBean('EmailParticipants');

                    /**
                     * FIXME: MAR-4521
                     * Only set `email_address` if there is an `id` for the
                     * returned EmailAddresses bean. And if setting
                     * `email_address`, also set `email_address_id`.
                     */
                    validRecipient.set({
                        'email_address': this._getEmailAddress(recipient)
                    });

                    if (recipient.bean) {
                        validRecipient.set({
                            parent: _.extend({type: recipient.bean.module}, app.utils.deepCopy(recipient.bean)),
                            parent_type: recipient.bean.module,
                            parent_id: recipient.bean.get('id'),
                            parent_name: app.utils.getRecordName(recipient.bean)
                        });
                    }

                    /**
                     * FIXME: MAR-4521
                     * A recipient is added to `validRecipients` if it
                     * satisfies one of the following conditions:
                     *
                     * - The recipient has a parent.
                     * - The recipient has an `email_address_id`.
                     *
                     * Consider logging an error if the recipient doesn't meet
                     * that criteria.
                     */
                    //only push the recipient if we have a valid email to send to
                    if (validRecipient.get('email_address')) {
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

                if (_.isArray(recipients)) {
                    _.each(recipients, function(recipient) {
                        /**
                         * FIXME: MAR-4521
                         * We don't need to know the `id` of the EmailAddresses
                         * bean. But only add the email address to `emails` if
                         * `email_address` is not empty.
                         */
                        var email = this._getEmailAddress(recipient);

                        if (email) {
                            emails.push(email);
                        }
                    }, this);
                } else {
                    emails.push(recipients);
                }

                return emails.join(',');
            },

            /**
             * Retrieve the best email address off the recipient
             *
             * @param {string|Object} recipient
             * @return {string}
             * @private
             */
            _getEmailAddress: function(recipient) {
                if (recipient.email) {
                    if (_.isString(recipient.email)) {
                        /**
                         * FIXME: MAR-4521
                         * There is nothing we can really do with this value.
                         * We don't know the ID of the email address. We would
                         * need to create the EmailAddresses record through the
                         * REST API to get the ID. This would require us to
                         * return the EmailAddresses bean asynchronously, which
                         * would require quite a bit of refactoring.
                         *
                         * Maybe we don't have to support this case anymore.
                         * OOTB, this use case is limited to the EmailField,
                         * which will be refactored to provide the ID of the
                         * email address. At that point, there are no OOTB use
                         * cases of an email address without its ID in regards
                         * to the EmailClientLaunch plugin. Not supporting this
                         * would break customizations that use this feature,
                         * but we could log a warning and return an
                         * EmailAddresses bean that has `email_address` set,
                         * but not `id`.
                         */
                        return recipient.email;
                    } else {
                        /**
                         * FIXME: MAR-4521
                         * It better be an EmailAddresses bean with an `id` and
                         * `email_address`. If so, we can just return it. If it
                         * has an `email_address`, but no `id`, then we can
                         * still return it. If there is an `id`, but no
                         * `email_address`, then we can still return it. But if
                         * there is no `id` and no `email_address`, then we
                         * should fall back to using `recipient.bean`, if it
                         * exists.
                         */
                        // The email address was specified, so use it.
                        return recipient.email;
                    }
                }

                if (recipient.bean) {
                    /**
                     * FIXME: MAR-4521
                     * Get the bean's primary email address and create an
                     * EmailAddresses bean from it. The bean won't have an
                     * `id`, but that is ok. Any consumers would be responsible
                     * for determining if the email address can be used without
                     * an ID. EmailClientLaunch#_formatRecipientsToString can
                     * use it without an ID.
                     * EmailClientLaunch#_retrieveValidRecipients cannot use it
                     * without an ID.
                     */
                    return app.utils.getPrimaryEmailAddress(recipient.bean);
                }

                /**
                 * FIXME: MAR-4521
                 * Always return an EmailAddresses bean.
                 */
                return recipient;
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
