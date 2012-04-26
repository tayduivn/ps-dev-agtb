describe("View Manager", function() {

    var app;

    describe("should be able to create instances of Field class", function() {

        var bean, collection, context, view, fields;

        beforeEach(function() {
            SugarTest.seedMetadata(true);
            app = SugarTest.app;
       
            //Need a sample Bean
            bean = app.data.createBean("Contacts", {
                first_name: "Foo",
                last_name: "Bar"
            });

            collection = new app.BeanCollection([bean]);

            //Setup a context
            context = app.context.getContext({
                url: "someurl",
                module: "Contacts",
                model: bean,
                collection: collection
            });

            view = {
                name: "test"
            };

            fields = app.view.fields;
        });

        afterEach(function() {
            app.view.fields = fields;
        });

        it("with default template", function() {
            var result = app.view.createField({
                def: {
                    type: 'addresscombo',
                    name: "address",
                    label: "Address"
                },
                context: context,
                view: view
            });

            expect(result).toBeDefined();
            expect(result instanceof app.view.Field).toBeTruthy();
            expect(result.type).toEqual("addresscombo");
            expect(result.name).toEqual("address");
            expect(result.label).toEqual("Address");
            expect(result.context).toEqual(context);
            expect(result.fieldDef).toEqual(fixtures.metadata.modules["Contacts"].fields["address"]);
            expect(result.model).toEqual(bean);
        });

        it("of custom class", function() {
            app.view.fields.AddresscomboField = app.view.Field.extend({
                foo: "foo"
            });

            var result = app.view.createField({
                def: {
                    type: 'addresscombo',
                    name: "address",
                    label: "Address"
                },
                context: context,
                view: view
            });

            expect(result).toBeDefined();
            expect(result instanceof app.view.fields.AddresscomboField).toBeTruthy();
            expect(result.foo).toEqual("foo");
        });

        it("of custom class with controller", function() {
            var result = app.view.createField({
                def: {
                    type: 'text',
                    name: "description"
                },
                context: context,
                view: view
            });

            expect(result).toBeDefined();
            expect(app.view.fields.TextField).toBeDefined();
            expect(result instanceof app.view.fields.TextField).toBeTruthy();
            expect(result.customCallback).toBeDefined();

            // Checking fall back algorithm
            expect(result.label).toEqual('description');
        });

    });

});
