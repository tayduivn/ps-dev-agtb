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

    app.augment("relRoutes", _rrh, false);

})(SUGAR.App);