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
    plugins: ['ErrorDecoration'],
    fallbackFieldTemplate: 'edit',
    /**
     * Login form view.
     * @class View.Views.LoginView
     * @alias SUGAR.App.view.views.LoginView
     */
    events: {
        "click [name=login_button]": "login",
        "keypress": "handleKeypress"
    },

    /**
     * Process login on key 'Enter'
     * @param e
     */
    handleKeypress: function(e) {
        if (e.keyCode === 13) {
            this.$("input").trigger("blur");
            this.login();
        }
    },

    /**
     * Get the fields metadata from panels and declare a Bean with the metadata attached
     * @param meta
     * @private
     */
    _declareModel: function(meta) {
        meta = meta || {};

        var fields = {};
        _.each(_.flatten(_.pluck(meta.panels, "fields")), function(field) {
            fields[field.name] = field;
        });
        /**
         * Fields metadata needs to be converted to this format for App.data.declareModel
         *  {
          *     "username": { "name": "username", ... },
          *     "password": { "name": "password", ... },
          *      ...
          * }
         */
        app.data.declareModel('Login', {fields: fields});
    },

    /**
     * @override
     * @param options
     */
    initialize: function(options) {
        // Declare a Bean so we can process field validation
        this._declareModel(options.meta);

        // Reprepare the context because it was initially prepared without metadata
        options.context.prepare(true);

        app.view.View.prototype.initialize.call(this, options);

        var config = app.metadata.getConfig();
        if (config && app.config.forgotpasswordON === true) {
            this.showPasswordReset = true;
        }

    },

    /**
     * @override
     * @private
     */
    _render: function() {
        this.logoUrl = app.metadata.getLogoUrl();
        app.view.View.prototype._render.call(this);
        this.refreshAddtionalComponents();
        /**
         * Added browser version check for MSIE since we are dropping support
         * for MSIE 9.0 for SugarCon
         */
        if (!this._isSupportedBrowser()) {
            app.alert.show('unsupported_browser', {
                level:'warning',
                title: '',
                messages: [
                    app.lang.getAppString('LBL_ALERT_BROWSER_NOT_SUPPORTED'),
                    app.lang.getAppString('LBL_ALERT_BROWSER_SUPPORT')
                ]
            });
        }
        var config = app.metadata.getConfig();
        if (config.system_status
            && config.system_status.level
            && (config.system_status.level == 'maintenance'
                || config.system_status.level == 'admin_only')) {
            app.alert.show('admin_only', {
                level:'warning',
                title: '',
                messages: [
                    '',
                    app.lang.getAppString(config.system_status.message),
                ]
            });            
        }
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
     * Process Login
     */
    login: function() {
        var self = this;
        // We have to do this because browser autocomplete does not always trigger DOM change events that would propagate changes into the model
        this.model.set({
            password: this.$("input[name=password]").val(),
            username: this.$("input[name=username]").val()
        });
        this.model.doValidate(null,
            _.bind(function(isValid) {
                if (isValid) {
                    app.$contentEl.hide();
                    var args = {password: this.model.get("password"), username: this.model.get("username")};

                    app.alert.show('login', {level: 'process', title: app.lang.getAppString('LBL_LOADING'), autoClose: false});
                    app.login(args, null, {
                        error: function() {
                            app.$contentEl.show();
                            app.logger.debug("login failed!");
                        },
                        success: function() {
                            app.logger.debug("logged in successfully!");
                            app.events.on('app:sync:complete', function() {
                                app.logger.debug("sync in successfully!");
                                _.defer(_.bind(this.postLogin, this));
                            }, self);
                        },
                        complete: function() {
                            app.alert.dismiss('login');
                        }
                    });
                }
            }, self)
        );
    },
    /**
     * After login and app:sync:complete, we need to see if there's any post login setup we need to do prior to
     * rendering the rest of the Sugar app
     */
    postLogin: function(){
        if (!app.user.get('show_wizard')) {
            this.refreshAddtionalComponents();
            
            if (new Date().getTimezoneOffset() != (app.user.getPreference('tz_offset_sec')/-60)) {
                var link = new Handlebars.SafeString('<a href="#' + 
                                                     app.router.buildRoute('Users', app.user.id, 'edit') + '">' + 
                                                     app.lang.get('LBL_TIMEZONE_DIFFERENT_LINK') + '</a>');
                
                var message = app.lang.get('TPL_TIMEZONE_DIFFERENT', null, {link: link});
                
                app.alert.show('offset_problem', {
                    messages: message,
                    closeable: true,
                    level: 'warning'
                });
            }
        }
        app.$contentEl.show();
    },

    /**
     * Taken from sugar_3. returns true if the users browser is recognized
     * @return {Boolean}
     * @private
     */
    _isSupportedBrowser:function () {
        var supportedBrowsers = {
            msie:{min:9},
            mozilla:{min:18},
            // For Safari & Chrome jQuery.Browser returns the webkit revision instead of the browser version
            // and it's hard to determine this number.
            safari:{min:536},
            chrome:{min:537}
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
