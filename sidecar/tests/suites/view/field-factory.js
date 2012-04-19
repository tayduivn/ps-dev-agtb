describe("View Manager", function() {

    describe("should be able to create instances of Field class", function() {

        var app = SUGAR.App;

        app.metadata.set(fixtures.metadata);
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
            model: bean,
            collection: collection
        });

        var view = {
            name: "test"
        };

        beforeEach(function() {
            app.view.fields = {};
        });

        it("with default template", function() {
            var result = app.view.createField({
                def: {
                    type: 'addresscombo',
                    name: "address"
                },
                context: context,
                view: view
            });

            expect(result).toBeDefined();
            expect(result instanceof app.view.Field).toBeTruthy();
        });

        it("with def of a string", function() {
            var model = app.data.createBean("Contacts", {
                first_name: "Foo",
                last_name: "Bar"
            });
            var result = app.view.createField({
                def: "first_name",
                context: context,
                view: view,
                model: model
            });
            expect(result).toBeDefined();
            expect(result.type).toEqual("text");
        });

        it("with custom controller", function() {
            var result = app.view.createField({
                def: {
                    type: 'varchar',
                    name: "description"
                },
                context: context,
                view: view
            });

            expect(result).toBeDefined();
            expect(app.view.fields.TextField).toBeDefined();
            expect(result instanceof app.view.fields.TextField).toBeTruthy();
            expect(result.customCallback).toBeDefined();
        });

    });

});