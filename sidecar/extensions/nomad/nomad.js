(function(app) {

    var _rrh = {

        associate: function(module, id, link, depth) {
            var relatedModule = app.data.getRelatedModule(module, link);
            app.logger.debug("Route changed to associate rels: " + module + "/" + id + "/" + link + "/" + relatedModule);
            app.controller.loadView({
                module: relatedModule,
                layout: "associate",
                viaLink: link,
                toModule: module,
                toId: id,
                depth:depth
            });
        },

        list: function(module, id, link) {
            app.logger.debug("Route changed to list rels: " + module + "/" + id + "/" + link);
            app.controller.loadView({
                parentModule: module,
                parentModelId: id,
                link: link,
                layout: "relationships"
            });
        },
        pickerList: function(module, id, action) {
            app.logger.debug("Route changed to list rels: " + module + "/" + id + "/link/picker/" + action);
            app.controller.loadView({
                module: module,
                modelId: id,
                layout: "pickerlist",
                action: action,
                create: true
            });
        },
        create: function(module, id, link, depth) {
            app.logger.debug("Route changed to create rel: " + module + "/" + id + "/" + link + "/create");
            app.controller.loadView({
                parentModule: module,
                parentModelId: id,
                link: link,
                create: true,
                layout: "edit",
                action:"create",
                depth:depth
            });
        },

        record: function(module, id, link, relatedId, action) {
            app.logger.debug("Route changed to action rel: " + module + "/" + id + "/" + link + "/" + relatedId);

            action = action || "detail";

            app.controller.loadView({
                parentModule: module,
                parentModelId: id,
                link: link,
                modelId: relatedId,
                action: action,
                layout: action
            });
        }

    };

    app.events.on("app:init", function() {
        app.metadata.set(app.baseMetadata);
        app.data.declareModels();

        // Register relationship routes
        app.router.route(":module/:id/link/:link", "relationships:list", _rrh.list);
        app.router.route(":module/:id/link/:link/:relatedId", "relationships:detail", _rrh.record);
        app.router.route(":module/:id/link/:link/:relatedId/:action", "relationships:action", _rrh.record);
        app.router.route(":module/:id/link/:link/create?depth=:depth", "relationships:create", _rrh.create);
        app.router.route(":module/:id/link/:link/associate?depth=:depth", "relationships:associate", _rrh.associate);
        app.router.route(":module/:id/links/:action","relationships:picker" ,_rrh.pickerList);

        app.api.serverUrl = app.isNative ? app.user.get("serverUrl") : app.config.serverUrl;

        app.logger.debug('App initialized in ' + (app.isNative ? "native shell" : "browser"));
        app.logger.debug('REST URL: ' + app.api.serverUrl);
    }).on("app:sync", function() {
        app.alert.show('metadata_syncing', {
                level: 'general',
                messages: 'Please, wait while configuring...',
                autoClose: true
            }
        );
    }).on("app:sync:complete", function() {
        app.alert.dismissAll();
    });

    app.events.on("data:sync:start", function(method, model, options) {
        var message;
        if (method == "read") {
             // We don't want to show the alert when paginating because we show "Loading..." message on the button itself
            if (_.isUndefined(options.offset)) message = "Loading...";
        }
        else if (method == "delete") {
            // options.relate means we are breaking a relationship between two records, not actually deleting a record
            message = options.relate === true ? "Unlinking..." : "Deleting...";
        }
        else {
            message = "Saving...";
        }

        if (message) {
            app.alert.show('data_syncing', {
                level: 'general',
                messages: message,
                autoClose: true
            });
        }

    }).on("data:sync:end", function(method, model, options, error) {
            app.alert.dismiss('data_syncing');
            // Error module will display proper message
            if (error) return;

            var message;
            if (method == "delete") {
                message = options.relate === true ? "Unlinked successfully." : "Deleted successfully.";
            }
            else if (method != "read") {
                message = "Saved successfully.";
            }

            if (message) {
                app.alert.show('data_sync_success', {
                    level: 'success',
                    messages: message,
                    autoClose: true
                });
            }
    });

    app.augment("nomad", {

        deviceReady: function(authAccessToken, authRefreshToken) {
            app.logger.debug("Device is ready");
            app.isNative = !_.isUndefined(window.cordova);

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
         * Displays email chooser UI.
         * @param {Array} emails
         * @param {String} subject(optional)
         * @param {String} body(optional)
         */
        sendEmail: function(emails, subject, body) {
            app.logger.debug("Sending email");
        },

        /**
         * Displays phone chooser UI.
         * @param {Array} phones
         */
        callPhone: function(phones) {
            app.logger.debug("Calling phone");
        },

        /**
         * Displays phone chooser UI.
         * @param {Array} phones
         */
        sendSms: function(phones) {
            app.logger.debug("Sending SMS");
        },

        /**
         * Displays url chooser UI.
         * @param {Array} phones
         */
        openUrl: function(urls) {
            app.logger.debug("Opening URL");
        },

        /**
         * Opens the map with specific address.
         * @param {Array} phones
         */
        openAddress: function(addressObj) {
            app.logger.debug("Open address");
        },

        // -------------------------------------------------
        // Private methods for pure web UI
        // -------------------------------------------------

        _showConfirm: function(message, confirmCallback, title, buttonLabels) {
            // TODO: Implement HTML modal dialog

            // Using standard browser confirm dialog for now
            // Mobile Safari displays buttons in the following order: 'Cancel', 'Confirm'
            // TODO: Test Android
            var confirmed = confirm(message);
            var index = confirmed ? 2 : 1;
            confirmCallback(index);
        }

    });

})(SUGAR.App);