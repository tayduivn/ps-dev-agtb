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

    Handlebars.registerHelper('listMenuItem', function(model, view, fields) {
        var template = app.template.get("list.menu.item");

        return new Handlebars.SafeString(template({
            model: model,
            view: view,
            context: app.controller.context,
            fields: fields
        }));
    });

    Handlebars.registerHelper('include', function(templateName, model, view, fields) {
        var template = (view.options.partials) ? view.options.partials[templateName] : app.template.get(templateName);

        return new Handlebars.SafeString(template({
            model: model,
            view: view,
            context: app.controller.context,
            fields: fields
        }));
    });
})(SUGAR.App);