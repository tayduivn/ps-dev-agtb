/**
 * Created by JetBrains PhpStorm.
 * User: dtam
 * Date: 2/2/12
 * Time: 5:15 PM
 * To change this template use File | Settings | File Templates.
 */
describe("SugarFieldManager", function () {
        var app = SUGAR.App;
        app.metadata.set(fixtures.metadata);
        app.metadata.set(sugarFieldsFixtures.fieldsData, "sugarFields");
        app.data.declareModels(fixtures.metadata);

        //Need a sample Bean
        var bean = app.data.createBean("Contacts", {
            first_name: "Foo",
            last_name: "Bar"
        });
        var collection = new app.BeanCollection([bean]);
        //Setup a context
        var context = app.context.getContext({
            url: "someurl",
            module: "Contacts",
            model : bean,
            collection : collection
        });

        var view = {
            name : "test"
        }

        it("should get a sugar field", function () {
                var result = app.sugarFieldManager.get({
                    def : {
                        type : 'varchar',
                        name : "description"
                    },
                    context : context,
                    view : view
                });

                expect(result).toBeDefined();
                expect(result.render).toBeDefined();
            }
        );

        it("should return a new Sugar field class when the field has a custom controller", function () {
            var result = app.sugarFieldManager.get({
                def : {
                    type : 'varchar',
                    name : "description"
                },
                context : context,
                view : view
            });

            expect(result).toBeDefined();
            expect(result.customCallback).toBeDefined();
            expect(app.sugarField.text).toBeDefined();
        });

        it('should register sugar_field Handlebars helper', function () {
                expect(Handlebars.helpers.sugar_field).not.toBe(null);
            });


    }
);