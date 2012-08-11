describe("List View", function() {
    var view, app, context, collection, options;

    beforeEach(function() {
        app = SUGAR.App;
        view = SugarTest.createView("base","Cases", "list");
        view.model = new Backbone.Model();
        context = app.context.getContext();
        collection = {
            orderBy: {
                field: "",
                direction: ""
            },
            fetch: function() {
                return true;
            }
        };
        options = {
            context: context,
            id: "1",
            template: "asdf"
        };
        context.set({collection: collection});
        view = new app.view.views.ListView(options); 
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });
    
    it("should set order by", function() {
        var event, x;
        view.$el.html('<div id="test" data-fieldname="bob"></div>');

        x = view.$el.children('#test');
        event = {target: x};
        view.setOrderBy(event);

        expect(collection.orderBy.direction).toEqual('desc');
        expect(collection.orderBy.field).toEqual('bob');
        view.setOrderBy(event);

        expect(collection.orderBy.direction).toEqual('asc');
        expect(collection.orderBy.field).toEqual('bob');
    });
});
