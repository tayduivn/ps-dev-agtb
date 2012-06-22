(function(app) {

    Handlebars.registerHelper('listItem', function(model, view, fields) {
        var template = app.template.get("list.item");

        return new Handlebars.SafeString(template({
            model: model,
            view: view,
            context: app.controller.context,
            fields: fields
        }));
    });

    Handlebars.registerHelper('include', function(templateName, model, view, fields) {
        var template = (view.options.templateOptions && view.options.templateOptions.partials) ?
            view.options.templateOptions.partials[templateName] :
            app.template.get(templateName);

        return new Handlebars.SafeString(template({
            model: model,
            view: view,
            context: view.context || app.controller.context,
            fields: fields
        }));
    });

    Handlebars.registerHelper('linkRoute', function(context, link, relatedId, action) {
        action = _.isString(action) ? action : null;
        var model = context.get("model");
        return new Handlebars.SafeString(app.nomad.buildLinkRoute(model.module, model.id, link, relatedId, action));
    });

    Handlebars.registerHelper('relatedRoute', function(relatedModel, action) {
        action = _.isString(action) ? action : null;
        var model = relatedModel.link.bean;
        var link = relatedModel.link.name;
        return new Handlebars.SafeString(app.nomad.buildLinkRoute(model.module, model.id, link, relatedModel.id, action));
    });

})(SUGAR.App);