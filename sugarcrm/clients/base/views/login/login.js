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
 * Login form view.
 *
 * @class View.Views.LoginView
 * @alias SUGAR.App.view.views.BaseLoginView
 */
({
    /**
     * @inheritDoc
     */
    plugins: ['ErrorDecoration'],

    /**
     * @inheritDoc
     */
    fallbackFieldTemplate: 'edit',

    /**
     * @inheritDoc
     */
    events: {
        'click [name=login_button]': 'login',
        'keypress': 'handleKeypress'
    },

    /**
     * An object containing the keys of the alerts that may be displayed in this
     * view.
     *
     * @type {Object}
     */
    _alertKeys: {
        adminOnly: 'admin_only',
        login: 'login',
        unsupportedBrowser: 'unsupported_browser',
        offsetProblem: 'offset_problem'
    },

    /**
     * Flag to indicate if the link to reset the password should be displayed.
     *
     * @type {Boolean}
     */
    showPasswordReset: false,

    /**
     * The company logo url.
     *
     * @type {String}
     */
    logoUrl: null,

    /**
     * Process login on key `Enter`.
     *
     * @param {Event} event The `keypress` event.
     */
    handleKeypress: function(event) {
        if (event.keyCode === 13) {
            this.$('input').trigger('blur');
            this.login();
        }
    },

    /**
     * Get the fields metadata from panels and declare a Bean with the metadata
     * attached.
     *
     * Fields metadata needs to be converted to {@link Data.Bean#declareModel}
     * format.
     *
     *     @example
     *      {
     *        "username": { "name": "username", ... },
     *        "password": { "name": "password", ... },
     *        ...
     *      }
     *
     * @param {Object} meta The view metadata.
     * @private
     */
    _declareModel: function(meta) {
        meta = meta || {};

        var fields = {};
        _.each(_.flatten(_.pluck(meta.panels, 'fields')), function(field) {
            fields[field.name] = field;
        });
        app.data.declareModel('Login', {fields: fields});
    },

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        if (app.progress) {
            app.progress.hide();
        }
        // Declare a Bean so we can process field validation
        this._declareModel(options.meta);

        // Reprepare the context because it was initially prepared without metadata
        options.context.prepare(true);

        this._super('initialize', [options]);

        var config = app.metadata.getConfig();
        if (config && app.config.forgotpasswordON === true) {
            this.showPasswordReset = true;
        }

    },

    /**
     * @inheritDoc
     */
    _render: function() {
        this.logoUrl = app.metadata.getLogoUrl();

        this._super('_render');

        this.refreshAddtionalComponents();

        if (!this._isSupportedBrowser()) {
            app.alert.show(this._alertKeys.unsupportedBrowser, {
                level: 'warning',
                title: '',
                messages: [
                    app.lang.getAppString('LBL_ALERT_BROWSER_NOT_SUPPORTED'),
                    app.lang.getAppString('LBL_ALERT_BROWSER_SUPPORT')
                ]
            });
        }

        var config = app.metadata.getConfig(),
            level = config.system_status && config.system_status.level;

        if (level === 'maintenance' || level === 'admin_only') {
            app.alert.show(this._alertKeys.adminOnly, {
                level: 'warning',
                title: '',
                messages: [
                    '',
                    app.lang.getAppString(config.system_status.message)
                ]
            });
        }
        app.alert.dismiss(this._alertKeys.offsetProblem);
        return this;
    },

    /**
     * Refresh additional components
     */
    refreshAddtionalComponents: function() {
        _.each(app.additionalComponents, function(component) {
            component.render();
        });
    },

    /**
     * Process login.
     *
     * We have to manually set `username` and `password` to the model because
     * browser autocomplete does not always trigger DOM change events that would
     * propagate changes into the model.
     */
    login: function() {
        this.model.set({
            password: this.$('input[name=password]').val(),
            username: this.$('input[name=username]').val()
        });
        this.model.doValidate(null,
            _.bind(function(isValid) {
                if (isValid) {
                    app.$contentEl.hide();

                    app.alert.show(this._alertKeys.login, {
                        level: 'process',
                        title: app.lang.getAppString('LBL_LOADING'),
                        autoClose: false
                    });

                    var args = {
                        password: this.model.get('password'),
                        username: this.model.get('username')
                    };

                    app.login(args, null, {
                        error: function() {
                            app.$contentEl.show();
                            app.logger.debug('login failed!');
                        },
                        success: _.bind(function() {
                            app.logger.debug('logged in successfully!');

                            app.events.on('app:sync:complete', function() {
                                app.logger.debug('sync in successfully!');
                                _.defer(_.bind(this.postLogin, this));
                            }, this);
                        }, this),
                        complete: _.bind(function() {
                            app.alert.dismiss(this._alertKeys.login);
                        }, this)
                    });
                }
            }, this)
        );

        app.alert.dismiss('offset_problem');
    },

    /**
     * After login and app:sync:complete, we need to see if there's any post
     * login setup we need to do prior to rendering the rest of the Sugar app.
     */
    postLogin: function() {
        if (!app.user.get('show_wizard')) {

            this.refreshAddtionalComponents();

            if (new Date().getTimezoneOffset() != (app.user.getPreference('tz_offset_sec') / -60)) {
                var link = new Handlebars.SafeString('<a href="#' +
                    app.router.buildRoute('Users', app.user.id, 'edit') + '">' +
                    app.lang.get('LBL_TIMEZONE_DIFFERENT_LINK') + '</a>');

                var message = app.lang.get('TPL_TIMEZONE_DIFFERENT', null, {link: link});

                app.alert.show(this._alertKeys.offsetProblem, {
                    messages: message,
                    closeable: true,
                    level: 'warning'
                });
            }
        }
        app.$contentEl.show();
    },

    /**
     * Taken from sugar_3.
     *
     * @return {Boolean} `true` if the browser is supported, `false` otherwise.
     * @private
     */
    _isSupportedBrowser: function() {
        var supportedBrowsers = {
            msie: {min: 9},
            mozilla: {min: 18},
            // For Safari & Chrome jQuery.Browser returns the webkit revision
            // instead of the browser version and it's hard to determine this
            // number.
            safari: {min: 536},
            chrome: {min: 537}
        };
        for (var b in supportedBrowsers) {
            if ($.browser[b]) {
                var current = parseInt($.browser.version);
                var supported = supportedBrowsers[b];
                return current >= supported.min;
            }
        }
    }
})
