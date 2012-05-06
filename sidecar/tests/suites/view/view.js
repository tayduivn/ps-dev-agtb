describe("View.View", function() {
    var app, bean, collection, context;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;

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
    });

    it('should render edit views', function() {
        var aclSpy = sinon.spy(app.acl,'hasAccess'), html,
            view = app.view.createView({
                context: context,
                name: "edit"
            });

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
