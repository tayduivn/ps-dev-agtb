describe("Handlebars Helpers", function() {

    var app = SUGAR.App;

    // TODO: Create test for each helper

    describe("getFieldValue", function() {

        it("should return value for an existing field", function() {
            var bean = new app.Bean({ foo: "bar"});
            expect(Handlebars.helpers.getFieldValue(bean, "foo")).toEqual("bar");
        });

        it("should return empty string for a non-existing field", function() {
            var bean = new app.Bean();
            expect(Handlebars.helpers.getFieldValue(bean, "foo")).toEqual("");
        });

        it("should return default string for a non-existing field", function() {
            var bean = new app.Bean();
            expect(Handlebars.helpers.getFieldValue(bean, "foo", "bar")).toEqual("bar");
        });

    });

    describe("field", function() {
        it("should return a sugarfield span element", function() {
            var sfid = 0,
                model = new app.Bean(),
                context = {
                    get: function() {
                        return "Cases";
                    }
                },
                field = new Backbone.View(),
                view = {sugarFields: [null, field]},
                sugarField = {name: "TestName", label: "TestLabel", type: "text"};

            expect(Handlebars.helpers.field.call(sugarField, context, view, model).toString()).toEqual('<span sfuuid="1"></span>');
        })
    });

    describe("buildRoute", function() {
        it("should return a create route based on given inputs", function() {
            var model = new app.Bean(),
                context = {
                    get: function() {
                        return "Cases";
                    }
                },
                action = "create",
                params = {};

            expect(Handlebars.helpers.buildRoute(context, model, action, params).toString()).toEqual("Cases/create");
        });

        it("should return a route based on given inputs", function() {
            var model = new app.Bean(),
                context = {
                    get: function() {
                        return "Cases";
                    }
                },
                action = "",
                params = {};

            model.id = "1245";

            expect(Handlebars.helpers.buildRoute(context, model, action, params).toString()).toEqual("Cases/1245");
        });
    });

    describe("has", function() {
        it("should return the true value if the first value is found in the second value (array)", function() {
            var val1 = "hello",
                val2 = ["world", "fizz", "hello", "buzz"],
                returnTrue = "Success!",
                returnFalse = "Failure!";

            expect(Handlebars.helpers.has(val1, val2, returnTrue, returnFalse)).toEqual(returnTrue);
        });

        it("should return the false value if the first value is found in the second value (array)", function() {
            var val1 = "hello",
                val2 = ["world", "fizz", "sidecar", "buzz"],
                returnTrue = "Success!",
                returnFalse = "Failure!";

            expect(Handlebars.helpers.has(val1, val2, returnTrue, returnFalse)).toEqual(returnFalse);
        });

        it("should return the true value if the first value is found in the second value (scalar)", function() {
            var val1 = "hello",
                val2 = "hello",
                returnTrue = "Success!",
                returnFalse = "Failure!";

            expect(Handlebars.helpers.has(val1, val2, returnTrue, returnFalse)).toEqual(returnTrue);
        });
    });

    describe("eachOptions", function() {
        it("should pull options hash from app list strings and return an iterated block string", function() {
            var optionName = "custom_fields_importable_dom",
                blockHtml = "<li>{{this.key}} {{this.value}}</li>",
                template;

            template = Handlebars.compile(blockHtml);

            expect(Handlebars.helpers.eachOptions(optionName, template)).toEqual("<li>true Yes</li><li>false No</li><li>required Required</li>");
        });

        it("should pull options array from app list strings and return an iterated block string", function() {
            var optionName = "custom_fields_merge_dup_dom",
                blockHtml = "<li>{{this}}</li>",
                template;

            template = Handlebars.compile(blockHtml);

            expect(Handlebars.helpers.eachOptions(optionName, template)).toEqual("<li>Disabled</li><li>Enabled</li><li>In Filter</li><li>Default Selected Filter</li><li>Filter Only</li>");
        });
    });

    describe("eq", function() {
        it("should return the true value if conditional evaluates true", function() {
            var val1 = 1,
                val2 = 1,
                returnTrue = "Success!",
                returnFalse = "Failure!";

            expect(Handlebars.helpers.eq(val1, val2, returnTrue, returnFalse)).toEqual(returnTrue);
        });

        it("should return the false value if conditional evaluates false", function() {
            var val1 = 1,
                val2 = 2,
                returnTrue = "Success!",
                returnFalse = "Failure!";

            expect(Handlebars.helpers.eq(val1, val2, returnTrue, returnFalse)).toEqual(returnFalse);
        });
    });

});