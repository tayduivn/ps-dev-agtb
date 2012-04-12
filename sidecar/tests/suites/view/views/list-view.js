describe("listView", function() {
    describe("should set order by", function() {
            var collection = {
                orderBy: {
                    field: "",
                    direction: ""
                },
                fetch: function() {
                    return true;
                }
            };
            var options = {
                context: {},
                id: "1",
                template: "asdf"
            };

            var view = new SUGAR.App.view.views.ListView(options);
            view.$el.html('<div id="test" data-fieldname="bob"></div>');
            //var x = view.$el.("#test");
            var x = view.$el.children('#test');
            var event = {target:x};
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
    )
});