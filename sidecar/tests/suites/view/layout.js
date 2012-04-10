describe("Layout.Layout", function(){
    var app = SUGAR.App;
    var context, bean, collection;

    beforeEach(function() {
        app.metadata.set(fixtures.metadata);
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
        var layout = app.layout.get({
            context : context,
            layout: "edit"
        });
        expect(layout.meta).toEqual(fixtures.metadata.modules.Contacts.layouts.edit);
    });

    it('should accept metadata overrides', function(){
        var testMeta = {
            //Default layout is a single view
            "type" : "simple",
            "components" : [
                {view : "testComp"}
            ]
        }
        var layout = app.layout.get({
            context : context,
            layout: "edit",
            meta: testMeta
        });
        expect(layout.meta).toEqual(testMeta);
    });

    //TODO: Need to defined tests for sublayout, complex layouts, and inline defined layouts

})