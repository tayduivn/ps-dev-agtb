describe("App", function() {

    var authStub, metaStub, isGetMetadataSucceeded;

    beforeEach(function() {
        isGetMetadataSucceeded = true;
        authStub = sinon.stub(SUGAR.App.api, "isAuthenticated", function() {
            return true;
        });

        metaStub = sinon.stub(SUGAR.App.api, "getMetadata", function(modules, filters, callbacks) {
            if (isGetMetadataSucceeded) {
                var metadata = fixtures.metadata;
                callbacks.success(metadata);
            }
            else {
                callbacks.error({code: 500});
            }
        });
    });

    afterEach(function() {
        authStub.restore();
        metaStub.restore();
    });

    describe("when an instance is requested", function() {

        it("should return a new instance if none exists", function() {
            expect(sugarApp).toBeTruthy();
        });

        it("should return an existing instance", function() {
            var app2 = SUGAR.App.init({el: "body"});
            expect(app2).toEqual(sugarApp);
        });

        it("should fire a app:init event when initialized", function() {
            var cbSpy = sinon.spy(function() {});
            sugarApp.events.on("app:init", cbSpy);
            sugarApp.trigger("app:init", this, {modules: cbSpy});
            expect(cbSpy).toHaveBeenCalled();
        });
    });

    describe("when augmented", function() {
        it("should register a module with itself", function() {
            var mock,
                module = {
                    init: function() {
                    }
                };
            mock = sinon.mock(module);

            mock.expects("init").once();
            sugarApp.augment("test", module, true);

            expect(mock.verify()).toBeTruthy();
        });
    });

    describe("when a data sync is required", function() {
        it("should fire a sync:complete event when all of the sync jobs have finished", function() {
            var cbSpy = sinon.spy(function() {
                SugarTest.setWaitFlag();
            });

            sugarApp.events.off("app:sync:complete"); // clear the app sync complete events
            sugarApp.on("app:sync:complete", cbSpy);

            sugarApp.sync();
            SugarTest.wait();

            runs(function() {
                expect(cbSpy).toHaveBeenCalled();
            });
        });

        it('should start and call sync if authenticated', function() {
            var syncSpy = sinon.spy(SUGAR.App, 'sync');

            sugarApp.start();
            expect(syncSpy.called).toBeTruthy();

            SUGAR.App.sync.restore();
        });

        it("should fire a sync:error event when one of the sync jobs have failed", function() {
            var cbSpy = sinon.spy(function() {
                SugarTest.setWaitFlag();
            });

            sugarApp.on("app:sync:error", cbSpy);
            isGetMetadataSucceeded = false;
            sugarApp.sync();
            SugarTest.wait();

            runs(function() {
                expect(cbSpy).toHaveBeenCalled();
            });
        });
    });

    it('should navigate given context, model and action', function() {
        var model = new Backbone.Model(),
            action = "edit",
            options = {},
            context = {},
            routerSpy = sinon.spy(sugarApp.router, "navigate");

        model.set("id", "1234");
        model.module = "Contacts";
        sugarApp.navigate(context, action, model, options);

        expect(routerSpy).toHaveBeenCalled();

        routerSpy.restore();
    });
});
