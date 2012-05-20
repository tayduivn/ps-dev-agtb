(function(app) {

    var _rrh = {

        list: function(module, id, link) {
            app.logger.debug("Route changed to list rels: " + module + "/" + id + "/" + link);
            app.controller.loadView({
                parentModule: module,
                parentModelId: id,
                link: link,
                layout: "list"
            });
        },

        create: function(module, id, link) {
            app.logger.debug("Route changed to create rel: " + module + "/" + id + "/" + link + "/create");
            app.controller.loadView({
                parentModule: module,
                parentModelId: id,
                link: link,
                create: true,
                layout: "edit"
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
        app.data.declareModels(app.baseMetadata);

        // Register relationship routes
        app.router.route(":module/:id/link/:link", "relationships", _rrh.list);
        app.router.route(":module/:id/link/:link/:relatedId", "relationships", _rrh.record);
        app.router.route(":module/:id/link/:link/create", "relationships", _rrh.create);
    });

    app.augment("nomad", {

        deviceReady: function() {
            app.init({el: "#nomad" });
            app.logger.debug('App initialized');
            app.api.debug = app.config.debugSugarApi;
            app.start();
            app.logger.debug('App started');
        },

        /**
         * Displays email chooser UI.
         * @param {Array} emails
         * @param {String} subject(optional)
         * @param {String} body(optional)
         */
        sendEmail: function(emails, subject, body) {
            // TODO: Implement HTML action sheet view
        },

        /**
         * Displays phone chooser UI.
         * @param {Array} phones
         */
        callPhone: function(phones) {
            // TODO: Implement HTML action sheet view
        }

    });

})(SUGAR.App);