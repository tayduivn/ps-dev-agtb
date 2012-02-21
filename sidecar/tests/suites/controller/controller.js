describe("Controller", function() {
    var controller = SUGAR.App.controller,
        layoutManager = SUGAR.App.Layout,
        dataManager = SUGAR.App.dataManager;

    describe("when a route is matched", function() {
        var params, layout, dataMan, layoutMan, layoutSpy, dataSpy, renderSpy;

        beforeEach(function() {
            params = {
                module: "main",
                url: "test/url",
                id: "1234"
            };

            // Overload the data manager
            dataMan = {
                fetchBean: function() {
                    return {};
                }
            };

            // Overload the layout manager
            layoutMan = {
                get: function() {
                    return layout;
                },
                render: function() {}
            };

            layout = { render: function() {} };

            layoutSpy = sinon.spy(layoutMan, "get");
            renderSpy = sinon.spy(layout, "render");
            dataSpy = sinon.spy(dataMan, "fetchBean");

            SUGAR.App.Layout = layoutMan;
            SUGAR.App.dataManager = dataMan;

            controller.loadView(params);
        });

        it("should fetch the needed data from the data manager", function() {
            expect(controller.data).toBeTruthy();
            expect(controller.data).not.toEqual(_.isEmpty(controller.data));
            expect(dataSpy.called).toBeTruthy();
        });

        it("should set the context", function() {
            expect(controller.context).toBeTruthy();
            expect(controller.context.get("module")).toEqual("main");
            expect(controller.context.get("url")).toEqual("test/url");
        });

        it("should load the appropriate layout", function() {
            expect(controller.layout).toBeTruthy();
            expect(layoutSpy.called).toBeTruthy();
        });

        it("should render the appropriate layout to the specified root div", function() {
            expect(renderSpy.called).toBeTruthy();
        });
    });

    SUGAR.App.Layout = layoutManager;
    SUGAR.App.dataManager = dataManager;
});