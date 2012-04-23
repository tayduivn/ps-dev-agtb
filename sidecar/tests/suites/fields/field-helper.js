var SugarFieldTest = {};

(function(test) {

    test.loadSugarField = function(file) {
        return SugarTest.loadFile("../../../sugarcrm/clients/base/fields", file, "js", function(data) {
            return eval("(" + data + ")");
        });
    }

    test.createField = function(name, type, viewName, fieldDef) {


        var app = SUGAR.App;

        var view = new Backbone.View();
        view.name = viewName;

        var def = { name: name, type: type };

        var model = new Backbone.Model();
        if (fieldDef) {
            model.fields = {};
            model.fields[name] = fieldDef;
        }

        return app.view.createField({
            def: def,
            view: view,
            context: "",
            model: model
        });
    }

})(SugarFieldTest);