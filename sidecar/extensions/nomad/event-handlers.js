(function(app) {

    app.events.on("app:init", function() {
        app.metadata.set(app.baseMetadata);
        app.data.declareModels();

        // Register relationship routes
        app.router.route(":module/:id/link/:link", "relationships:list", app.relRoutes.list);
        app.router.route(":module/:id/link/:link/:relatedId", "relationships:detail", app.relRoutes.record);
        app.router.route(":module/:id/link/:link/:relatedId/:action", "relationships:action", app.relRoutes.record);
        app.router.route(":module/:id/link/:link/create?depth=:depth", "relationships:create", app.relRoutes.create);
        app.router.route(":module/:id/link/:link/associate?depth=:depth", "relationships:associate", app.relRoutes.associate);
        app.router.route(":module/:id/links/:action","relationships:picker", app.relRoutes.pickerList);

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

    }).on("data:sync:start", function(method, model, options) {
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

})(SUGAR.App);