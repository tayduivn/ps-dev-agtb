describe("Controller", function() {

    var controller = SUGAR.App.controller,
        layoutManager = SUGAR.App.Layout,
        dataManager = SUGAR.App.dataManager;

    afterEach(function() {
        SUGAR.App.destroy();
    });

    it("should exist within the framework", function() {
        expect(controller).toBeDefined();
    });

    describe("when a route is matched", function() {
        var params = {
            module: "main",
            url: "test/url",
            id: "1234"
        };

        // Overload the data manager
        var dataMan = {
            fetchBean: function() {
                return {};
            }
        };

        // Overload the layout manager
        var layoutMan = {
            get: function() {
                return {};
            }
        };
        var layoutMock = sinon.mock(layoutMan);
        var dataMock = sinon.mock(dataMan);
        layoutMock.expects("get").once();
        dataMock.expects("fetchBean").once().withArgs(params.module, params.id);

        SUGAR.App.Layout = layoutMan;
        SUGAR.App.dataManager = dataMan;

        controller.loadView(params);

        it("should fetch the needed data from the data manager", function() {
            expect(controller.data).toBeTruthy();
            expect(controller.data).not.toEqual(_.empty(controller.data));
            expect(dataMock.verity()).toBeTruthy();
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