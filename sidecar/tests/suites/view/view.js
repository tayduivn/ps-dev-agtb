describe("Layout.View", function() {
    var app, bean, collection, context;

    beforeEach(function() {
        app = SUGAR.App.init({el: "#sidecar"});
        app.metadata.set(fixtures.metadata);
        app.data.declareModels(fixtures.metadata);
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
        var aclSpy = sinon.spy(app.acl,'hasAccess');
        var view = app.view.createView({
            context: context,
            name: "edit"
        });

        expect(view.meta).toBeDefined();
        view.render();
        var html = view.$el.html();
        expect(html).toContain('edit');

        expect(view.$el).toContain('input=[value="Foo"]');
        expect(aclSpy).toHaveBeenCalled();
        aclSpy.restore();
    });

    it('should render detail views', function() {
        var view = app.view.createView({
            context: context,
            name: "detail"
        });
        view.render();
        var html = view.$el.html();
        expect(html).toContain('detail');
    });

});