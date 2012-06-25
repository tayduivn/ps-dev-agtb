(function(app) {

    var onPause = function() {
        // This is going to get logged after the app gets resumed
        // See iOS quirks in cordova docs
        app.logger.debug("App was paused");
    };
    var onResume = function(elapsed) {
        app.logger.debug("App resumed after " + elapsed + " seconds");
    };
    var onMemoryWarning = function() {
        app.logger.debug("App received memory warning");
    };

    app.augment("nomad", {

        deviceReady: function(authAccessToken, authRefreshToken) {
            app.logger.debug("Device is ready, layout cache enabled: " + app.config.layoutCacheEnabled);
            app.isNative = !_.isUndefined(window.cordova);

            if (app.config.layoutCacheEnabled !== true) app.NomadController = null;

            if (app.isNative) {
                app.logger.debug("access/refresh tokens: " + authAccessToken + "/" + authRefreshToken);
                app.OAUTH = {};
                app.OAUTH["AuthAccessToken"] = authAccessToken;
                app.OAUTH["AuthRefreshToken"] = authRefreshToken;
                app.config.authStore = "keychain";
            }

            app.init({el: "#nomad" });
            app.api.debug = app.config.debugSugarApi;
            app.start();
            app.logger.debug('App started');

            if (app.isNative) {
                document.addEventListener("pause", onPause, false);
                document.addEventListener("resume", onResume, false);
                document.addEventListener("memoryWarning", onMemoryWarning, false);
            }
        },

        buildLinkRoute: function(moduleOrContext, id, link, relatedId, action) {
            var route = (_.isString(moduleOrContext)) ? moduleOrContext : moduleOrContext.get("module");
            route += "/" + id + "/link/" + link;

            if (relatedId) {
                route += "/" + relatedId;
            }

            if (action) {
                route += "/" + action;
            }

            return route;
        },

        /**
         * Filters out link fields that support multiple relationships and belong to any module managed by the app.
         * @param {Data.Bean} model Instance of the model to
         * @return {Array} Array of filtered link names.
         */
        getLinks: function (model) {
            var modules = app.metadata.getModuleList();
            return _.filter(model.fields, function (field) {
                var relationship;
                return ((field.type == "link") &&
                    (relationship = app.metadata.getRelationship([field.relationship])) && // this check is redundant but necessary 'cause currently the server doesn't return all relationships
                    app.data.canHaveMany(model.module, field.name) &&
                    _.has(modules, relationship.lhs_module) &&
                    _.has(modules, relationship.rhs_module));
            });

        },

        /**
         * Shows a confirmation dialog.
         * @param {String} message
         * @param {Function} confirmCallback callback: `function(index)`. Index will be 1 or 2.
         * @param {String} title(optional) Dialog title.
         * @param {String} buttonLabels(optional) Comma-separated two button labels. `Cancel,OK` if not specified.
         */
        showConfirm: function(message, confirmCallback, title, buttonLabels) {
            this._showConfirm(message, confirmCallback, title, buttonLabels || "Cancel,OK");
        },

        /**
         * Displays email chooser UI and pops up native mailer once email is selected.
         * @param {Array/String} emails email or array of emails
         * @param {String} subject(optional) email subject.
         * @param {String} body(optional) email body.
         */
        sendEmail: function(emails, subject, body) {
            if (_.isArray(emails) && emails.length > 1) {
                this._showActionSheet("Select recepient", emails, function(buttonValue, buttonIndex) {
                    if (buttonIndex < emails.length) this._showEmailComposer(subject, body, buttonValue);
                });
            } else {
                this._showEmailComposer(subject, body, this._extractValue(emails));
            }
        },

        /**
         * Displays phone chooser UI and initiates a phone call once a phone is selected.
         *
         * @param {Array/String} phones phone or array of phone objects.
         * Array of phones consists of objects:
         * <pre><code>
         * [
         *   { name: 'Mobile', value: '(408) 555-7890' },
         *   { name: 'Home', value: '(650) 333-3456' }
         * ]
         * </code></pre>
         */
        callPhone: function(phones) {
            var self = this;
            if (_.isArray(phones) && phones.length > 1) {
                var numbers = this._buildNamedList(phones);
                this._showActionSheet("Select phone number", numbers, function(buttonValue, buttonIndex) {
                    if (buttonIndex < phones.length) self._callPhone(phones[buttonIndex].value);
                });
            } else {
                this._callPhone(this._extractValue(phones));
            }
        },

        /**
         * Displays phone chooser UI and sends SMS once a phone is selected.
         *
         * @param {Array/String} phones phone or array of phone objects.
         * Array of phones consists of objects:
         * <pre><code>
         * [
         *   { name: 'Mobile', value: '(408) 555-7890' },
         *   { name: 'Home', value: '(650) 333-3456' }
         * ]
         * </code></pre>
         * @param {String} message(optional) SMS message to send.
         */
        sendSms: function(phones, message) {
            var self = this;
            if (_.isArray(phones) && phones.length > 1){
                var numbers = this._buildNamedList(phones);
                this._showActionSheet("Select phone number", numbers, function(buttonValue, buttonIndex) {
                    if (buttonIndex < phones.length) self._showSmsComposer(phones[buttonIndex].value, message);
                });
            } else {
                this._showSmsComposer(this._extractValue(phones), message);
            }
        },

        /**
         * Opens URL in mobile Safari.
         *
         * @param {String/Array} urls URL or array of URL objects.
         * Array of URLs consists of objects:
         * <pre><code>
         * [
         *   { name: 'Corporate site', value: 'http://example.com' },
         *   { name: 'Other', value: 'http://example2.com' }
         * ]
         * </code></pre>
         */
        openUrl: function(urls) {
            if (_.isArray(urls) && urls.length > 1){
                var urlNames = _.pluck(urls, "name");
                var self = this;
                this._showActionSheet("Select URL to open", urlNames, function(buttonValue, buttonIndex) {
                    if (buttonIndex < urls.length){
                        self._browseUrl(self._normalizeUrl(urls[buttonIndex].value));
                    }
                });
            } else {
                this._browseUrl(this._normalizeUrl(this._extractValue(urls)));
            }
        },

        /**
         * Opens native map application to display a physical address.
         *
         * @param {String/Array} address Address or array of address objects.
         * <pre><code>
         * [
         *   { name: 'Billing Address', value: '360 Acalanes Dr, Sunnyvale, CA 94086' },
         *   { name: 'Shipping Address: '412 Del Medio Ave, Mountain View, CA 94040' }
         * ]
         * </code></pre>
         */
        openAddress: function(address) {
            if (_.isArray(address) && address.length > 1){
                var self = this;
                var locationNames = _.pluck(address, "name");
                this._showActionSheet("Select location to show", locationNames, function(buttonValue, buttonIndex) {
                    if (buttonIndex < address.length)
                        self._openGoogleMap(address[buttonIndex].value);
                });
            } else {
                this._openGoogleMap(this._extractValue(address));
            }
        },

        // Generates googlemap URL from location data and opens it in external browser
        _openGoogleMap: function(locationData) {
            app.logger.debug("Opening map");
            var qStr = _.reduce(_.values(locationData), function(memo, value) {
                return memo + ",+" + value;
            });
            this._browseUrl("http://maps.google.com/maps?q=" + encodeURI(qStr));
        },

        // Builds an array of named phone numbers: "<phone-name> - <phone-number>"
        _buildNamedList: function(items) {
            return _.map(items, function(item) {
                return item.name + " - " + item.value;
            });
        },

        _extractValue: function(data) {
            return _.isString(data) ? data : (data[0].value || data[0]);
        },

        // Pre-pend with 'http://' is absent
        _normalizeUrl: function(url) {
            if ((url.indexOf("http://") == 0) || (url.indexOf("https://") == 0)) return url;
            return "http://" + url;
        }

    });

})(SUGAR.App);