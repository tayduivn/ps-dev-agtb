describe("List View", function() {
    var app, ListView;
        
    beforeEach(function() {
        var controller;
        //SugarTest.app.config.env = "dev"; // so I can see app.data ;=)
        controller = SugarTest.loadFile('../../../clients/base/views/list', 'list', 'js', function(d){ return d;});
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        ListView = app.view.declareComponent('view', 'List', null, controller);
    });
    
    it("should set order by based on fieldname and orderby field properties", function() {
        var event, x;
        var context = app.context.getContext();
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
            context: context,
            id: "1",
            template: "asdf"
        };

        // test that orderBy property takes precedence over fieldName property
        var dataProvider = [
            {
                'fieldName': 'date_modified',
                'orderBy': '',
                'expectedOrderByField': 'date_modified'
            },
            {
                'fieldName': 'full_name',
                'orderBy': 'last_name',
                'expectedOrderByField': 'last_name'
            }
        ];

        $.each(dataProvider, function(index, value) {
            context.set({collection: collection});
            var view = new app.view.views.ListView(options);

            view.$el.html('<div id="test" data-fieldname="'+value.fieldName+'" data-orderby="'+value.orderBy+'"></div>');

            x = view.$el.children('#test');
            event = {target: x};
            view.setOrderBy(event);

            expect(collection.orderBy.direction).toEqual('desc');
            expect(collection.orderBy.field).toEqual(value.expectedOrderByField);
            expect(collection.orderBy.columnName).toEqual(value.fieldName);
            view.setOrderBy(event);

            expect(collection.orderBy.direction).toEqual('asc');
            expect(collection.orderBy.field).toEqual(value.expectedOrderByField);
            expect(collection.orderBy.columnName).toEqual(value.fieldName);
        });
    });
});
