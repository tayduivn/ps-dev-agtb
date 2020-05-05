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
/**
 * @class View.Fields.Base.OutboundEmail.EmailProviderField
 * @alias SUGAR.App.view.fields.BaseOutboundEmailEmailProviderField
 * @extends View.Fields.Base.RadioenumField
 */
({
    extendsFrom: 'RadioenumField',

    'events': {
        'click .btn': 'authorize'
    },

    /**
     * SMTP providers requiring OAuth2.
     *
     * @property {Object}
     */
    oauth2Types: {
        google_oauth2: {
            application: 'GoogleEmail',
            auth_warning: 'LBL_EMAIL_GOOGLE_AUTH_WARNING',
            auth_url: null,
            eapm_id: null,
            authorized_account: null,
            dataSource: 'googleEmailRedirect'
        },
        exchange_online: {
            application: 'MicrosoftEmail',
            auth_warning: 'LBL_EMAIL_MICROSOFT_AUTH_WARNING',
            auth_url: null,
            eapm_id: null,
            authorized_account: null,
            dataSource: 'microsoftEmailRedirect'
        }
    },

    /**
     * Handles auth when the button is clicked.
     */
    authorize: function() {
        var self = this;

        if (this.oauth2Types[this.value] && this.oauth2Types[this.value].auth_url) {
            var listener = function(e) {
                if (!self || self.handleOauthComplete(e)) {
                    window.removeEventListener('message', listener);
                }
            };
            window.addEventListener('message', listener);
            var height = 600;
            var width = 600;
            var left = (screen.width - width) / 2;
            var top = (screen.height - height) / 4;
            window.open(this.oauth2Types[this.value].auth_url, '_blank', 'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',resizable=1');
        }
    },

    /**
     * Handles the oauth completion event.
     * Note that the EAPM bean has already been saved at this point.
     *
     * @param {Object} e
     * @return {boolean} True if success, otherwise false
     */
    handleOauthComplete: function (e) {
        var data = JSON.parse(e.data);
        if (!data.dataSource ||
            !this.oauth2Types[this.value] ||
            data.dataSource !== this.oauth2Types[this.value].dataSource) {
            return false;
        }
        if (data.eapmId && data.emailAddress) {
            this.model.set('eapm_id', data.eapmId);
            this.model.set('authorized_account', data.emailAddress);
            // save data in case user switches to another email provider and back
            this.oauth2Types[this.value].eapm_id = data.eapmId;
            this.oauth2Types[this.value].authorized_account = data.emailAddress;
        } else {
            app.alert.show('error', {
                level: 'error',
                messages: app.lang.get('LBL_EMAIL_AUTH_FAILURE', this.module)
            });
        }
        return true;
    },

    _render: function() {
        this._checkAuth(this.model.get(this.name));
        this._super('_render');
    },

    /**
     * Gets and displays auth info for OAuth2.
     *
     * @param {string} smtpType
     */
    _checkAuth: function(smtpType) {
        var self = this;
        this.authWarning = '';
        this.authButton = false;
        this.model.set('eapm_id', '');
        this.model.set('authorized_account', '');

        if (this.oauth2Types[smtpType]) {
            this.model.set('mail_authtype', 'oauth2');
            if (this.oauth2Types[smtpType].auth_url === null) {

                var options = {
                    showAlerts: false,
                    success: _.bind(function(data) {
                        if (data && data.auth_url) {
                            self.oauth2Types[smtpType].auth_url = data.auth_url;
                            self.authButton = 'enabled';
                        } else {
                            self.oauth2Types[smtpType].auth_url = false
                            self.authWarning = self.oauth2Types[smtpType].auth_warning;
                            self.authButton = 'disabled';
                        }
                        self.render();
                    }),
                    error: function() {
                        self.oauth2Types[smtpType].auth_url = false
                        self.authWarning = self.oauth2Types[smtpType].auth_warning;
                        self.authButton = 'disabled';
                        self.render();
                    }
                };
                var url = app.api.buildURL('EAPM', 'auth', {}, {application: this.oauth2Types[smtpType].application});
                app.api.call('read', url, {}, options);
            } else if (!this.oauth2Types[smtpType].auth_url) {
                this.authWarning = this.oauth2Types[smtpType].auth_warning;
                this.authButton = 'disabled';
            } else {
                if (this.oauth2Types[smtpType].eapm_id) {
                    this.model.set('eapm_id', this.oauth2Types[smtpType].eapm_id);
                }
                if (this.oauth2Types[smtpType].authorized_account) {
                    this.model.set('authorized_account', this.oauth2Types[smtpType].authorized_account);
                }
                this.authButton = 'enabled';
            }
        } else {
            this.model.set('mail_authtype', '');
        }
    },

    /**
     * Falls back to the detail template when attempting to load the disabled
     * template.
     *
     * @inheritdoc
     */
    _getFallbackTemplate: function(viewName) {
        // Don't just return "detail". In the event that "nodata" or another
        // template should be the fallback for "detail", then we want to allow
        // the parent method to determine that as it always has.
        if (viewName === 'disabled') {
            viewName = 'detail';
        }

        return this._super('_getFallbackTemplate', [viewName]);
    },
})
