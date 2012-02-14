describe("Controller", function() {
    var controller = SUGAR.App.controller,
        layoutManager = SUGAR.App.Layout,
        dataManager = SUGAR.App.dataManager;

    it("should exist within the framework", function() {
        expect(controller).toBeDefined();
    });

    describe("when a route is matched", function() {
        var params, dataMan, layoutMan, layoutMock, dataMock;

        params = {
            module: "main",
            url: "test/url",
            id: "1234"
        };

        // Overload the data manager
        dataMan = {
            fetchBean: function() {
                console.log("Fetchign the bean");
                return {};
            }
        };

        // Overload the layout manager
        layoutMan = {
            get: function() {
                console.log("Laying hte manager");
                return {};
            }
        };

        beforeEach(function() {
            layoutMock = sinon.mock(layoutMan);
            dataMock = sinon.mock(dataMan);

            layoutMock.expects("get").once();
            dataMock.expects("fetchBean").once().withArgs(params.module, params.id);

            SUGAR.App.Layout = layoutMan;
            SUGAR.App.dataManager = dataMan;

            controller.loadView(params);
        });

        it("should fetch the needed data from the data manager", function() {
            expect(controller.data).toBeTruthy();
            expect(controller.data).not.toEqual(_.isEmpty(controller.data));
            console.log(dataMock);
            console.log(controller);
            expect(dataMock.verify()).toBeTruthy();
        });

        it("should set the context", function() {
            expect(controller.context).toBeTruthy();
            expect(controller.context.get("module")).toEqual("main");
            expect(controller.context.get("url")).toEqual("test/url");
        });

        it("should load the appropriate layout", function() {
            expect(controller.currentView).toBeTruthy();
            expect(layoutMock.verify()).toBeTruthy();
        });

        it("should render the appropriate layout to the specified root div", function() {

        });
    });

    SUGAR.App.Layout = layoutManager;
    SUGAR.App.dataManager = dataManager;
});