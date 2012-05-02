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

})(SUGAR.App);