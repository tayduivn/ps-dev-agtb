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
 * Displays notifications on desktop via Notifications API.
 *
 * Because browser vendors currently implement notifications differently, we use different
 * strategies based on browsers.
 *
 * Chrome: Show desktop notification.
 * IE: Show browser alert box. (Notifications API is not implemented in IE)
 * Firefox and Safari: Show both desktop notification and browser alert box, since desktop
 *                     notification is only displayed for a very short time.
 */
(function(app) {
    var isFirefox = $.browser.mozilla,
        isSafari = $.browser.safari && !window.chrome,
        defaultIcon = 'styleguide/assets/img/dark_cube.png';

    app.events.on('app:init', function() {
        app.browserNotification = {
            /**
             * Display notification.
             *
             * @param {string} title notification title to be displayed
             * @param {string} [options.body] body of the notification to be displayed
             * @param {string} [options.icon] location of the notification icon
             *                                (does not work in Safari)
             * @param {Function} [options.onclick] function to call when notification is clicked
             */
            show: function(title, options) {
                options = options || {};

                if (window.Notification) {
                    Notification.requestPermission(function(permission) {
                        var notification;

                        if (permission === 'granted') {
                            notification = new Notification(title, {
                                body: options.body,
                                icon: options.icon || defaultIcon
                            });
                            notification.onclick = options.onclick;

                            if (isFirefox || isSafari) {
                                app.browserNotification._showUsingBrowserAlert(title, options);
                            }
                        } else {
                            app.browserNotification._showUsingBrowserAlert(title, options);
                        }
                    });
                } else {
                    this._showUsingBrowserAlert(title, options);
                }
            },

            /**
             * Display notification via browser alert. If options.onclick is provided,
             * it will use confirm(). If options.onclick is not specified, alert()
             * will be used.
             *
             * @param {string} title notification title to be displayed
             * @param {string} [options.body] body of the notification to be displayed
             * @param {Function} [options.onclick] function to call when OK is clicked
             * @private
             */
            _showUsingBrowserAlert: function(title, options) {
                // Need to defer so that desktop notification and browser alert box can be
                // displayed together.
                _.defer(function() {
                    var text = options.body ? title + '\n\n' + options.body : title;
                    if (_.isFunction(options.onclick)) {
                        if (confirm(text)) {
                            options.onclick();
                        }
                    } else {
                        alert(text);
                    }
                });
            }
        };
    });
})(SUGAR.App);
