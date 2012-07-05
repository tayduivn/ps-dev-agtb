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
            _renderSelf: function() {
                app.view.View.prototype._renderSelf.call(this, { prop: "kommunizma"});
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

    it('should return its fields and dispose them when re-rendering', function(){
        var view = app.view.createView({
                context: context,
                name: "detail"
            });
        var fields = [ 'first_name', 'last_name', 'phone_work', 'phone_home', 'email1', 'account_name' ];
        var mock = sinon.mock(app.view.Field.prototype);
        mock.expects("dispose").exactly(9);

        expect(view.getFieldNames()).toEqual([ 'first_name', 'last_name', 'phone_work', 'phone_home', 'email1', 'account_name', 'account_id' ]);

        expect(_.isEmpty(view.getFields())).toBeTruthy();
        expect(_.isEmpty(view.fields)).toBeTruthy();
        view.render();
        expect(_.keys(view.fields).length).toEqual(9);
        expect(_.pluck(view.getFields(), "name")).toEqual(fields);

        // Make sure the number of fields is still the same
        view.render();
        expect(_.keys(view.fields).length).toEqual(9);
        expect(_.pluck(view.getFields(), "name")).toEqual(fields);
        mock.verify();
    });


});
