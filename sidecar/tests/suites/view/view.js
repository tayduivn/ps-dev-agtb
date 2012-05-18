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

    it('should render with custom context for its template', function() {
        app.view.views.CustomView = app.view.View.extend({
            _render: function() {
                this._renderWithContext({ prop: "kommunizma"});
            }
        });
        var view = app.view.createView({
                context: context,
                name: "custom"
            }), html;

        view.template = Handlebars.compile("K pobede {{prop}}!");
        view.render();
        html = view.$el.html();
        expect(html).toContain('K pobede kommunizma!');
    });

    it('should return its fields', function(){
        var view = app.view.createView({
                context: context,
                name: "detail"
            });
        expect(view.getFieldNames()).toEqual([ 'first_name', 'last_name', 'phone_work', 'phone_home', 'email1', 'account_name', 'account_id' ]);

        expect(view.getFields()).toEqual({});
        view.render();
        expect(_.isEmpty(view.getFields())).toBeFalsy();
        expect(_.pluck(view.getFields(), "name")).toEqual([ 'first_name', 'last_name', 'phone_work', 'phone_home', 'email1', 'account_name' ]);
    });


});
