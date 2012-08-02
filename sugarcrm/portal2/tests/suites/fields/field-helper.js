var SugarFieldTest = {};

(function(test) {

    test.loadSugarField = function(file,platform) {
        if ( typeof(platform) == 'undefined' ) {
            platform = 'base';
        }

        return SugarTest.loadFile("../../../clients/"+platform+"/fields", file, "js", function(data) {
            return eval("(" + data + ")");
        });
    };

    test.createField = function(name, type, viewName, fieldDef) {


        var app = SUGAR.App;
        var context = app.context.getContext();
        var view = new app.view.View({ name: viewName, context: context });
        var def = { name: name, type: type };

        var model = new Backbone.Model();

        if (fieldDef) {
            model.fields = {};
            model.fields[name] = fieldDef;
        }

        return app.view.createField({
            def: def,
            view: view,
            context: context,
            model: model
        });
    };

}(SugarFieldTest));
