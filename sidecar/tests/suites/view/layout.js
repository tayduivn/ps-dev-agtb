describe("View.Layout", function(){
    var app, context, bean, collection;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        bean = app.data.createBean("Contacts", {
            first_name: "Foo",
            last_name: "Bar"
        });
        collection = new app.BeanCollection([bean]);
        context = app.context.getContext({
            url: "someurl",
            module: "Contacts",
            model: bean,
            collection: collection
        });
    });

    it('should get metadata from the manager', function(){
        var layout = app.view.createLayout({
            context : context,
            name: "edit"
        });
        expect(layout.meta).toEqual(fixtures.metadata.modules.Contacts.layouts.edit);
    });

    it('should accept metadata overrides', function(){
        var layout, 
            testMeta = {
            //Default layout is a single view
            "type" : "simple",
            "module" : "Contacts",
            "components" : [
                {view : "testComp"}
            ]
        };
        layout = app.view.createLayout({
            context : context,
            name: "edit",
            meta: testMeta
        });
        expect(layout.meta).toEqual(testMeta);
    });

    //TODO: Need to defined tests for sublayout, complex layouts, and inline defined layouts

});
