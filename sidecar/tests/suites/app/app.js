describe("App", function() {
    var authStub, metaStub, isGetMetadataSucceeded;

    beforeEach(function() {
        isGetMetadataSucceeded = true;
        // Replace sugarauth isAuthenticated method with fake
        authStub = sinon.stub(SUGAR.App.sugarAuth, "isAuthenticated", function() {
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
        var app;

        it("should return a new instance if none exists", function() {
            app = SUGAR.App.init({el: "body", silent: true});
            expect(app).toBeTruthy();
        });

        it("should return an existing instance", function() {
            var app2 = SUGAR.App.init({el: "body"});
            expect(app2).toEqual(app);
        });

        SUGAR.App.destroy();
    });

    describe("when augmented", function() {
        var app = SUGAR.App.init({el: "body", silent: true}),
                mock;
        it("should register a module with itself", function() {
            var module = {
                init: function() {
                }
            }

            mock = sinon.mock(module);
            mock.expects("init").once();

            app.augment("test", module, true);
            expect(mock.verify()).toBeTruthy();
        });

        SUGAR.App.destroy();
    });

    describe("when a data sync is required", function() {
        it("should fire a sync:complete event when all of the sync jobs have finished", function() {
            var cbSpy = sinon.spy(function() {
                SugarTest.setWaitFlag();
            });
            var app = SUGAR.App.init({el: "body"});

            // Add listener onto app for the syncComplete event
            app.on("app:sync:complete", cbSpy);
            app.sync();

            SugarTest.wait();

            runs(function() {
                expect(cbSpy).toHaveBeenCalled();
            });
        });

        it("should fire a sync:error event when one of the sync jobs have failed", function() {
            var cbSpy = sinon.spy(function() {
                SugarTest.setWaitFlag();
            });
            var app = SUGAR.App.init({el: "body"});

            // Add listener onto app for the syncComplete event
            app.on("app:sync:error", cbSpy);

            isGetMetadataSucceeded = false;
            app.sync();

            SugarTest.wait();

            runs(function() {
                expect(cbSpy).toHaveBeenCalled();
            });
        });
    });

    it('should navigate given context, model and action', function() {
        var app = SUGAR.App.init({el: "body"}),
            model = new Backbone.Model(),
            action = "edit",
            options = {},
            context = {},
            routerSpy = sinon.spy(app.router, "navigate");

        model.set("id", "1234");
        model.module = "Contacts";
        app.navigate(context, action, model, options);

        expect(routerSpy).toHaveBeenCalled();

        routerSpy.restore();
    });
});