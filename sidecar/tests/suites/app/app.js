describe("App", function() {

    var authStub, metaStub, isGetMetadataSucceeded;

    beforeEach(function() {
        isGetMetadataSucceeded = true;
        authStub = sinon.stub(SUGAR.App.api, "isAuthenticated", function() {
            return true;
        });

        metaStub = sinon.stub(SUGAR.App.api, "getMetadata", function(hash, modules, filters, callbacks) {
            if (isGetMetadataSucceeded) {
                var metadata = fixtures.metadata;
                callbacks.success(metadata, "", {status: 200});
            } else {
                callbacks.error({
                    status: 500,
                    textStatus: "error"
                });
            }
        });
    });

    afterEach(function() {
        authStub.restore();
        metaStub.restore();
    });

    describe("when an instance is requested", function() {

        it("should return a new instance if none exists", function() {
            expect(SugarTest.app).toBeTruthy();
        });

        it("should return an existing instance", function() {
            SugarTest.seedFakeServer();
            SugarTest.server.respondWith("GET", /.*\/rest\/v10\/metadata\/public\?typeFilter=&moduleFilter.*/,
                [304, {"Content-Type": "application/json"}, JSON.stringify({modules:{}})]);
            var app = SUGAR.App.init({el: "body"});
            SugarTest.server.respond();
            SugarTest.server.restore();
            SugarTest.seedFakeServer();
            SugarTest.server.respondWith("GET", /.*\/rest\/v10\/metadata\/public\?typeFilter=&moduleFilter.*/,
                [304, {"Content-Type": "application/json"}, JSON.stringify({modules:{}})]);
            var app2 = SUGAR.App.init({el: "body"});
            SugarTest.server.respond();
            SugarTest.server.restore();
            expect(app2).toEqual(app);
        });

        it("should fire a app:init event when initialized", function() {
            var cbSpy = sinon.spy(function() {});
            SugarTest.app.events.on("app:init", cbSpy);
            SugarTest.app.trigger("app:init", SugarTest.app, {modules: cbSpy});
            expect(cbSpy).toHaveBeenCalled();
        });
    });

    it("should initialize addtional components", function() {
        var components = {login:{target:'#footer'}};
        SugarTest.app.controller.loadAdditionalComponents(components);
        expect(SugarTest.app.additionalComponents.login).toBeDefined();
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
            SugarTest.app.augment("test", module, true);

            expect(mock.verify()).toBeTruthy();
        });
    });

    describe("when a data sync is required", function() {
        it("should fire a sync:complete event when all of the sync jobs have finished", function() {
            var cbSpy = sinon.spy(function() {
                SugarTest.setWaitFlag();
            });

            SugarTest.app.events.off("app:sync:complete"); // clear the app sync complete events
            SugarTest.app.on("app:sync:complete", cbSpy);
            SugarTest.app.sync();
            SugarTest.wait();

            runs(function() {
                expect(cbSpy).toHaveBeenCalled();
            });
        });
        it('should call sync after login', function() {
            var cbSpy = sinon.stub(SUGAR.App, 'sync', function() { return true; });
            SugarTest.app.trigger("app:login:success");
            expect(cbSpy).toHaveBeenCalled();
            SugarTest.app.sync.restore();
        });

        it("should fire a sync:error event when one of the sync jobs have failed", function() {
            var cbSpy = sinon.spy(function() {
                SugarTest.setWaitFlag();
            });

            SugarTest.app.on("app:sync:error", cbSpy);
            isGetMetadataSucceeded = false;
            SugarTest.app.sync();
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
            context = SugarTest.app.context.getContext(),
            routerSpy = sinon.spy(SugarTest.app.router, "navigate");

        model.set("id", "1234");
        model.module = "Contacts";
        context.set("model", model);
        SugarTest.app.navigate(context, model, action, options);

        expect(routerSpy).toHaveBeenCalledWith("Contacts/1234/edit");

        routerSpy.restore();
    });

    it("should login", function() {
        SugarTest.seedApp();
        var app         = SugarTest.app,
            mock        = sinon.mock(app.api),
            successFn   = function() {},
            credentials = {user:'dauser',pass:'dapass'},
            callbacks   = {success: successFn};

        mock.expects("login").once().withArgs(
            'foo', credentials);
        app.login('foo', credentials, callbacks );
        expect(mock.verify()).toBeTruthy();
    });

    it("should logout", function() {
        SugarTest.seedApp();
        var app         = SugarTest.app,
            mock        = sinon.mock(app.api),
            successFn   = function() {},
            callbacks   = {success: successFn};

        mock.expects("logout").once().withArgs(callbacks);
        app.logout( callbacks );
        expect(mock.verify()).toBeTruthy();
    });
});
