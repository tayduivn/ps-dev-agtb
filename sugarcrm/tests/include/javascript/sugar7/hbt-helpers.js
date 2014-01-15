describe("Handlebar.Helpers", function() {
    var app, savedHelpers;

    beforeEach(function () {
        app = SugarTest.app;
        savedHelpers = Handlebars.helpers;
        SugarTest.loadFile("../include/javascript/sugar7", "hbs-helpers", "js", function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
    });

    afterEach(function() {
        Handlebars.helpers = savedHelpers;
        app = null;
    });

    describe("moduleIconLabel helper", function () {
        beforeEach(function() {
            sinon.stub(app.lang, "getAppListStrings", function(item) {
                switch (item) {
                    case 'moduleIconList':
                        return {
                            TestModule : 'TM'
                        }
                    break;
                    case 'moduleListSingular':
                        return {
                            TestSingleModule : 'Single',
                            TestMultiModule: 'Multiple Names'
                        }
                    break;
                    case 'moduleList':
                        return {
                            TestCustomModule : 'Custom'
                        }
                    break;
                }
            })
        });

        afterEach(function() {
            app.lang.getAppListStrings.restore();
        });

        it("should fill in the icon with value defined in the moduleIconList array", function() {
            expect(Handlebars.helpers.moduleIconLabel('TestModule')).toEqual('TM');
        });

        it("should fill in the icon with the first two letters of singular module name", function() {
            expect(Handlebars.helpers.moduleIconLabel('TestSingleModule')).toEqual('Si');
        });

        it("should fill in the icon with the first letter of the first two words for modules with multiple word names",
            function() {
                expect(Handlebars.helpers.moduleIconLabel('TestMultiModule')).toEqual('MN');
            }
        );

        it("should fill in the icon with the letters of the module in the module list, if it is not in the singular lists",
            function() {
                expect(Handlebars.helpers.moduleIconLabel('TestCustomModule')).toEqual('Cu');
            }
        );

        it("should fill in the icon with the first letter of the module, if it doesn't exist in the module lists",
            function() {
                expect(Handlebars.helpers.moduleIconLabel('NonExistentModule')).toEqual('No');
            }
        );

    });
});
