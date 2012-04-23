describe("Layout.View", function() {
    var app, bean, collection, context;

    beforeEach(function() {
        app = SugarTest.app;
        app.metadata.set(fixtures.metadata);
        app.data.declareModels(fixtures.metadata);
        bean = app.data.createBean("Contacts", {
            first_name: "Foo",
            last_name: "Bar"
        });
        bean.fields = fixtures.metadata.modules.Contacts.fields;
        collection = new app.BeanCollection([bean]);
        context = app.context.getContext({
            url: "someurl",
            module: "Contacts",
            model: bean,
            collection: collection
        });

        app.template.load(fixtures.metadata);
    });

    it('should get metadata from the metdata manager', function() {
        var view = app.view.createView({
            context: context,
            name: "edit"
        });
        expect(view.meta).toEqual(fixtures.metadata.modules.Contacts.views.edit);
    });

    it('should accept metadata overrides', function() {
        var testMeta = {
            "panels": [
                {
                    "label": "TEST",
                    "fields": []
                }
            ]
        };

        var view = app.view.createView({
            context: context,
            name: "edit",
            meta: testMeta
        });
        expect(view.meta).toEqual(testMeta);
    });

    it('should render edit views', function() {
        var aclSpy = sinon.spy(app.acl,'hasAccess'), html,
            view = app.view.createView({
                context: context,
                name: "edit"
            });

        expect(view.meta).toBeDefined();
        view.render();
        html = view.$el.html();
        expect(html).toContain('edit');

        expect(view.$el).toContain('input=[value="Foo"]');
        expect(aclSpy).toHaveBeenCalled();
        aclSpy.restore();
    });

    it('should render detail views', function() {
        var view = app.view.createView({
                context: context,
                name: "detail"
            }), html;
        view.render();
        html = view.$el.html();
        expect(html).toContain('detail');
    });

    it('should return its fields', function(){
        var fields,
            view = app.view.createView({
                context: context,
                name: "detail"
            });
        fields = view.getFields();
        expect(fields).toEqual(["first_name", "last_name", "phone_work", "phone_home", "email1"]);
    });


});
