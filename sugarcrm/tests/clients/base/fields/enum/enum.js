describe("enum field", function() {
    var app, field, stub_appListStrings,
        module = 'Contacts',
        fieldName = 'test_enum';

    beforeEach(function() {
        delete Handlebars.templates;
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'list');
        SugarTest.testMetadata.set();
        SugarTest.testMetadata._addDefinition(fieldName, 'fields', {
        }, module);

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
    });

    afterEach(function() {
        if (field) {
            field.dispose();
        }
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
        stub_appListStrings.restore();
    });

    it("should format a labeled select and have option selected on edit template", function() {
        field = SugarTest.createField("base", fieldName, "enum", "edit", {options: "bugs_type_dom"});
        var original = 'Defect',
            expected = 'DefectValue';
        field.model.set(fieldName, original);
        field.render();
        var actual = field.$('input').select2('data');
        expect(actual.id).toEqual(original);
        expect(actual.text).toEqual(expected);
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

    it("should call loadEnumOptions and set items during render", function() {
        var field = SugarTest.createField("base", fieldName, "enum", "edit", {options: "bugs_type_dom"});
        var loadEnumSpy = sinon.spy(field, "loadEnumOptions");
        field.render();
        expect(loadEnumSpy.called).toBe(true);
        expect(field.items).toEqual(app.lang.getAppListStrings());
        loadEnumSpy.restore();
    });

    it("should default the value of the field to the first option if undefined", function() {
        var field = SugarTest.createField("base", fieldName, "enum", "edit", {options: "bugs_type_dom"});
        field.items = {'first': 'first', 'second': 'second'};
        var loadEnumSpy = sinon.spy(field, "loadEnumOptions");
        field.render();
        loadEnumSpy.restore();
        expect(field.model.get(field.name)).toEqual('first');
    });

    it("should not default the value of the field to the first option if defaultOnUndefined is false", function() {
        var field = SugarTest.createField("base", fieldName, "enum", "edit", {options: "bugs_type_dom"});
        field.items = {'first': 'first', 'second': 'second'};
        field.defaultOnUndefined = false;
        var loadEnumSpy = sinon.spy(field, "loadEnumOptions");
        field.render();
        loadEnumSpy.restore();
        expect(field.model.get(field.name)).toBeUndefined();
    });

    it("should not default the value of the field to the first option if multi select", function() {
        var field = SugarTest.createField("base", fieldName, "enum", "edit", {isMultiSelect: true, options: "bugs_type_dom"});
        field.items = {'first': 'first', 'second': 'second'};
        var loadEnumSpy = sinon.spy(field, "loadEnumOptions");
        field.render();
        loadEnumSpy.restore();
        expect(field.model.get(field.name)).toBeUndefined();
    });

    describe('enum API', function() {
        it('should load options from enum API if options is undefined', function() {
            var callStub = sinon.stub(app.api, 'enumOptions', function(module, field, callbacks) {
                expect(field).toEqual('test_enum');
                //Call success callback
                callbacks.success(app.lang.getAppListStrings());
            });
            field = SugarTest.createField('base', fieldName, 'enum', 'detail', {/* no options */});
            var renderSpy = sinon.spy(field, '_render');
            field.render();
            expect(callStub).toHaveBeenCalled();
            expect(renderSpy.calledTwice).toBe(true);
            expect(field.items).toEqual(app.lang.getAppListStrings());
            callStub.restore();
            renderSpy.restore();
        });
        it('should avoid duplicate enum api call', function() {
            var apiSpy = sinon.spy(app.api, 'enumOptions');
            field = SugarTest.createField('base', fieldName, 'enum', 'detail', {}, module);
            var field2 = SugarTest.createField('base', fieldName, 'enum', 'detail', {}, module, null, field.context),
                expected = {
                    aaa: 'bbb',
                    fake1: 'fvalue1',
                    fake2: 'fvalue2'
                };
            //setup fake REST end-point for enum
            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('GET', /.*rest\/v10\/Contacts\/enum\/test_enum.*/,
                [200, { 'Content-Type': 'application/json'}, JSON.stringify(expected)]);
            field.render();
            SugarTest.server.respond();
            field2.render();
            field.render();

            expect(apiSpy.calledOnce).toBe(true);
            //second field should be ignored, once first ajax called is being called
            expect(apiSpy.calledTwice).toBe(false);
            _.each(expected, function(value, key) {
                expect(field.items[key]).toBe(value);
                expect(field2.items[key]).toBe(value);
            });
            apiSpy.restore();
            field.dispose();
            field2.dispose();
        });
    });

    describe("getSelect2Options", function() {
        it("should allow separator to be configured via metadata", function(){
            field = SugarTest.createField("base", fieldName, "enum", "detail", {isMultiSelect: true, separator: '|', options: "bugs_type_dom"});
            var select2opts = field.getSelect2Options([]);
            expect(select2opts.separator).toEqual('|');
            expect(select2opts.multiple).toBe(true);
        });
        it("should allow multiselect to be configured via metadata", function(){
            field = SugarTest.createField("base", fieldName, "enum", "detail", {isMultiSelect: true, options: "bugs_type_dom"});
            var select2opts = field.getSelect2Options([]);
            expect(select2opts.multiple).toBe(true);
        });
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

        describe("blank value on multi select", function() {
            it('should transform the empty key on render', function() {
                field = SugarTest.createField("base", fieldName, "enum", "list", {isMultiSelect: true, options: "bugs_type_dom"});
                field.render();
                expect(field.items['']).toBeUndefined();
            });
        it('should prevent focus otherwise the dropdown is opened and it\'s impossible to remove an item', function() {
            field = SugarTest.createField("base", fieldName, "enum", "detail", {isMultiSelect: true, options: "bugs_type_dom"});
            var jQueryStub = sinon.stub(field, '$');
            field.focus();
            expect(jQueryStub).not.toHaveBeenCalled();
            jQueryStub.restore();
        });

    });
});
