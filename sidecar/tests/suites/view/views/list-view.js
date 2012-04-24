describe("listView", function() {
    describe("should set order by", function() {
        var event, x,
            collection = {
                orderBy: {
                    field: "",
                    direction: ""
                },
                fetch: function() {
                    return true;
                }
            },
            options = {
                context: {},
                id: "1",
                template: "asdf"
            },
            view = new SUGAR.App.view.views.ListView(options);

            view.$el.html('<div id="test" data-fieldname="bob"></div>');

            x = view.$el.children('#test');
            event = {target:x};
            view.context.get = function(args) {
                return collection;
            };
            view.setOrderBy(event);

            expect(collection.orderBy.direction).toEqual('desc');
            expect(collection.orderBy.field).toEqual('bob');
            view.setOrderBy(event);

            expect(collection.orderBy.direction).toEqual('asc');
            expect(collection.orderBy.field).toEqual('bob');
        }
    );
});
