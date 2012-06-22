describe("Handlebars Helpers", function() {

    var app;

    beforeEach(function() {
        app = SugarTest.app;
    });

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

        beforeEach(function() {
            SugarTest.seedApp();
        });

        it("should return a sugarfield span element", function() {
            var model = new app.Bean();
            var context = {
                    get: function() {
                        return "Cases";
                    }
                };
            var view = new app.view.View({ name: "detail", context: context});
            var def = {name: "TestName", label: "TestLabel", type: "text"};

            var fieldId = app.view.getFieldId();
            var result = Handlebars.helpers.field.call(def, view, model);
            expect(result.toString()).toMatch(/<span sfuuid=.*(\d+).*/);
            expect(app.view.getFieldId()).toEqual(fieldId + 1);
            expect(view.fields[fieldId + 1]).toBeDefined();
        });
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
                returnFalse = "Failure!",
                returnCb = function() { return returnTrue; };
                returnCb.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.has(val1, val2, returnCb)).toEqual(returnTrue);
        });

        it("should return the false value if the first value is found in the second value (array)", function() {
            var val1 = "hello",
                val2 = ["world", "fizz", "sidecar", "buzz"],
                returnTrue = "Success!",
                returnFalse = "Failure!",
                returnCb = function() { return returnTrue; };
                returnCb.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.has(val1, val2, returnCb)).toEqual(returnFalse);
        });

        it("should return the true value if the first value is found in the second value (scalar)", function() {
            var val1 = "hello",
                val2 = "hello",
                returnTrue = "Success!",
                returnFalse = "Failure!",
                returnCb = function() { return returnTrue; };
                returnCb.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.has(val1, val2, returnCb)).toEqual(returnTrue);
        });
    });

    describe("eachOptions", function() {
        it("should pull options hash from app list strings and return an iterated block string", function() {
            var optionName = "custom_fields_importable_dom",
                blockHtml = "<li>{{this.key}} {{this.value}}</li>"
                template = Handlebars.compile(blockHtml);

            app.metadata.set(fixtures.metadata);
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
                returnFalse = "Failure!",
                returnCb = function() { return returnTrue; };
                returnCb.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.eq(val1, val2, returnCb)).toEqual(returnTrue);
        });

        it("should return the false value if conditional evaluates false", function() {
            var val1 = 1,
                val2 = 2,
                returnTrue = "Success!",
                returnFalse = "Failure!",
                returnCb = function() { return returnTrue; };
                returnCb.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.eq(val1, val2, returnCb)).toEqual(returnFalse);
        });
    });

    describe("notEq", function() {
        it("should return the false value if conditional evaluates true", function() {
            var val1 = 1,
                val2 = 1,
                returnTrue = "Success!",
                returnFalse = "Failure!",
                returnCb = function() { return returnTrue; };
                returnCb.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.notEq(val1, val2, returnCb)).toEqual(returnFalse);
        });

        it("should return the true value if conditional evaluates false", function() {
            var val1 = 1,
                val2 = 2,
                returnTrue = "Success!",
                returnFalse = "Failure!",
                returnCb = function() { return returnTrue; };
                returnCb.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.notEq(val1, val2, returnCb)).toEqual(returnTrue);
        });
    });

    describe("notMatch", function() {
        it("should return inverse of regex evaluation", function() {
            var val1 = "foo-is-not-greedy",
                nonGreedy = "^foo$", 
                greedy = "foo", 
                returnTrue = "Success!",
                returnFalse = "Failure!",
                returnCb = function() { return returnTrue; };
                returnCb.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.notMatch(val1, nonGreedy, returnCb)).toEqual(returnTrue);
            expect(Handlebars.helpers.notMatch(val1, greedy, returnCb)).toEqual(returnFalse);
        });
    });
    
    describe("match", function() {
        it("should return result of regex evaluation", function() {
            var val1 = "foo-is-not-greedy",
                nonGreedy = "^foo$", 
                greedy = "foo", 
                returnTrue = "Success!",
                returnFalse = "Failure!",
                returnCb = function() { return returnTrue; };
                returnCb.inverse = function() { return returnFalse; };

            expect(Handlebars.helpers.match(val1, nonGreedy, returnCb)).toEqual(returnFalse);
            expect(Handlebars.helpers.match(val1, greedy, returnCb)).toEqual(returnTrue);
        });
    });

    describe("isSortable", function() {
        it("should return block if isSortable is true in field viewdef", function() {
            var returnVal = 'Yup',
                block = function() {return returnVal; },
                module = "Cases", 
                fieldViewdef = { 
                    name: 'text',
                    sortable: true,
                },
                getModuleStub = sinon.stub(app.metadata, 'getModule', function() { 
                    return {
                        fields: {
                            text: { 
                                sortable:false
                            }
                        }
                    };
                });
            expect(Handlebars.helpers.isSortable(module, fieldViewdef, block)).toEqual(returnVal);
            getModuleStub.restore();
        });

        it("should not return block if isSortable is false in field viewdef but true in vardef", function() {
            var returnVal = 'Yup',
                block = function() {return returnVal; },
                module = "Cases", 

                fieldViewdef = { 
                    name: 'text',
                    sortable: false,
                },
                getModuleStub = sinon.stub(app.metadata, 'getModule', function() { 
                    return {
                        fields: {
                            text: { 
                                sortable: true
                            }
                        }
                    };
                });
            expect(Handlebars.helpers.isSortable(module, fieldViewdef, block)).not.toEqual(returnVal);
            getModuleStub.restore();
        });
        it("should return block if isSortable not defined in either field viewdef or vardef", function() {
            var returnVal = 'Yup',
                block = function() {return returnVal; },
                module = "Cases", 
                fieldViewdef = { 
                    name: 'text'
                },
                getModuleStub = sinon.stub(app.metadata, 'getModule', function() { 
                    return {
                        fields: {
                            text: {} 
                        }
                    };
                });
            expect(Handlebars.helpers.isSortable(module, fieldViewdef, block)).toEqual(returnVal);
            getModuleStub.restore();
        });
    });
    
    describe("getLabel", function() {
        it("should get a label", function() {
            var lang = SugarTest.app.lang;
            app.metadata.set(fixtures.metadata);
            expect(Handlebars.helpers.getLabel("LBL_ASSIGNED_TO_NAME", "Contacts")).toEqual("Assigned to");
        });
    });

});
