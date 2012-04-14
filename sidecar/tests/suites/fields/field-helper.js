var SugarFieldTest = {};

(function(test) {

    test.loadSugarField = function(file) {
        return SugarTest.loadFile("../../../sugarcrm/clients/base/fields", file, "js", function(data) {
            return eval("(" + data + ")");
        });
    }

    test.createField = function(type, viewName, extraparams) {


        var app = SUGAR.App;

        var view = new Backbone.View();
        view.name = viewName;

        var def = { type: type };

        extraparams = extraparams || {};
        _.extend(def, extraparams);

        return app.view.createField({
            def: def,
            view: view,
            context: "",
            model: new Backbone.Model()
        });
    }

})(SugarFieldTest);