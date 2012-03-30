describe("Controller", function() {
    var controller = SUGAR.App.controller,
        layoutManager = SUGAR.App.layout,
        dataManager = SUGAR.App.dataManager;

    SUGAR.App.init({el: "body"});

    describe("when a route is matched", function() {
        var params, layout, dataMan, layoutMan, layoutSpy, dataSpy, renderSpy, collectionSpy;

        beforeEach(function() {
            params = {
                module: "main",
                url: "test/url",
                id: "1234"
            };

            // Overload the data manager
            dataMan = {
                createBean: function() {
                    return new SUGAR.App.Bean;
                },
                createBeanCollection: function() {
                    return new SUGAR.App.BeanCollection;
                }
            };

            // Overload the layout manager
            layoutMan = {
                get: function() {
                    return layout;
                },
                render: function() {
                }
            };

            layout = {
                render: function() {},
                getFields : function(){}
            };

            layoutSpy = sinon.spy(layoutMan, "get");
            renderSpy = sinon.spy(layout, "render");
            dataSpy = sinon.spy(dataMan, "createBean");
            collectionSpy = sinon.spy(dataMan, "createBeanCollection");

            SUGAR.App.layout = layoutMan;
            SUGAR.App.dataManager = dataMan;
            //TODO dont pass in SUGAR.App
            controller.initialize(SUGAR.App);
            controller.setElement("body");
        });

        afterEach(function() {
            SUGAR.App.layout = layoutManager;
            SUGAR.App.dataManager = dataManager;
        });

        it("should load the view properly", function() {
            controller.loadView(params);

            // Check to make sure it loads the proper data
            expect(dataSpy).toHaveBeenCalled();
            expect(collectionSpy).toHaveBeenCalled();
            //expect(_.isEmpty(controller.context.get("model"))).toBeFalsy();

            // Check to make sure we have set the context
            expect(controller.context).toBeDefined();
            expect(controller.context.get("module")).toEqual("main");
            expect(controller.context.get("url")).toEqual("test/url");

            // Check to make sure we have loaded a layout
            expect(controller.layout).toBeDefined();
            expect(layoutSpy).toHaveBeenCalled();
        });
    });
});