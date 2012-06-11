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

    Handlebars.registerHelper('buildLinkRoute', function(context, model, link, action) {
        model = model || context.get("model");
        var id = model.id;

        return new Handlebars.SafeString(app.nomad.buildLinkRoute(context.get("module"), id, link, action));
    });
})(SUGAR.App);