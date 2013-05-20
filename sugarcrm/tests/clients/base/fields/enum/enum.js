describe("enum field", function() {
    var app, field, stub_appListStrings,
        fieldName = 'test_enum';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'list');
        SugarTest.testMetadata.set();
        stub_appListStrings = sinon.stub(app.lang, 'getAppListStrings', function() {
            return {"":"","Defect":"DefectValue","Feature":"FeatureValue"};
        });

        if (!$.fn.select2) {
            $.fn.select2 = function(options) {
                var obj = {
                    on : function() {
                        return obj;
                    }
                };
                return obj;
            };
        }

        // Hack to workaround latest select2 chrome "Oh Snap" crashes
        // Tracked here: SP-805
        // https://codereview.chromium.org/14060024
        // https://github.com/ivaynberg/select2/issues/1099
        // TODO: We need to remove this when fix is pushed to stable chrome released
        WebKitMutationObserver = function() {};
        WebKitMutationObserver.prototype.observe = function() {};
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
        multiEnumField = null;
        stub_appListStrings.restore();
    });

    it("should format a labeled select and have option selected on edit template", function() {
        field = SugarTest.createField("base", fieldName, "enum", "edit", {options: "bugs_type_dom"});
        var original = 'Defect',
            expected = 'DefectValue';
        field.model.set(fieldName, original);
        field.render();
        var actual = field.$('option[value=Defect]').text();
        expect(actual).toEqual(expected);
        expect(field.$('option[value=Defect]').is(':selected')).toBeTruthy();
    });

    it("should format a labeled string for detail template", function() {
        field = SugarTest.createField("base", fieldName, "enum", "detail", {options: "bugs_type_dom"});
        var original = 'Defect',
            expected = 'DefectValue';
        field.model.set(fieldName, original);
        field.render();
        var actual = field.$el.text().replace(/(\r\n|\n|\r)/gm,"");
        expect($.trim(actual)).toEqual(expected);
    });

    it("should call loadEnumOptions and set enumOptions during render", function() {
        var field = SugarTest.createField("base", fieldName, "enum", "edit", {options: "bugs_type_dom"});
        var loadEnumSpy = sinon.spy(field, "loadEnumOptions");
        field.render();
        expect(loadEnumSpy.called).toBe(true);
        expect(field.enumOptions).toEqual(app.lang.getAppListStrings());
        loadEnumSpy.restore();
    });

    it("should load options from enum API if options is undefined", function(){
        var callStub = sinon.stub(app.api,"enum", function(module, field, callbacks){
            expect(field).toEqual("test_enum");
            //Call success callback
            callbacks.success(app.lang.getAppListStrings());
        });
        field = SugarTest.createField("base", fieldName, "enum", "detail", {/* no options */});
        var renderSpy = sinon.spy(field, "_render");
        field.render();
        expect(callStub).toHaveBeenCalled();
        expect(renderSpy.calledTwice).toBe(true);
        expect(field.enumOptions).toEqual(app.lang.getAppListStrings());
        callStub.restore();
        renderSpy.restore();
    });

    describe("multi select enum", function() {

        it("should display a labeled comma list for detail template", function() {
            field = SugarTest.createField("base", fieldName, "enum", "detail", {isMultiSelect: true, options: "bugs_type_dom"});
            var original = ["Defect", "Feature"],
                expected = 'DefectValue, FeatureValue';
            field.model.set(fieldName, original);
            field.render();
            var actual = field.$el.text().replace(/(\r\n|\n|\r)/gm,"");
            expect($.trim(actual)).toEqual(expected);
        });

        it("should display a labeled comma list for list template", function() {
            field = SugarTest.createField("base", fieldName, "enum", "list", {isMultiSelect: true, options: "bugs_type_dom"});
            var original = ["Defect", "Feature"],
                expected = 'DefectValue, FeatureValue';
            field.model.set(fieldName, original);
            field.render();
            var actual = field.$el.text().replace(/(\r\n|\n|\r)/gm,"");
            expect($.trim(actual)).toEqual(expected);
        });

        it("should format server's default value into a string array", function(){
            field = SugarTest.createField("base", fieldName, "enum", "list", {isMultiSelect: true, options: "bugs_type_dom"});
            var original = "^Weird^";
            var expected = ["Weird"];
            var original2 = "^Very^,^Weird^";
            var expected2 = ["Very", "Weird"];
            expect(field.format(original)).toEqual(expected);
            expect(field.format(original2)).toEqual(expected2);
        });

        it("should unformat nulls into server equivalent format of array with empty string", function(){
            // Backbone.js won't sync null values so server doesn't pick up on change and clear multi-select field
            field = SugarTest.createField("base", fieldName, "enum", "list", {isMultiSelect: true, options: "bugs_type_dom"});
            var original = null;
            var expected = [];
            expect(field.unformat(original)).toEqual(expected);
        });

    });
});
