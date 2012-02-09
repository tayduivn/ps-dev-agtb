describe("Application context manager", function() {
    it("should exist within the framework", function() {
        expect(SUGAR.App.context).toBeTruthy();
    });

    it("should return a new context object", function() {
        var context = SUGAR.App.context.getContext({}, {});
        expect(context).toBeTruthy();
    });

    describe("Context Object", function() {
        var context = SUGAR.App.context.getContext({module: "test module"}, {model: {}});

        describe("when a new state is required", function() {
            var obj = {
                url: "someurl",
                module: "test_module"
            };


            var data = {
                model: {},
                collection: {}
            };

            var stub = sinon.spy();

            context.init(obj, data);
            context.bind("context:change", stub);

            it("should take context parameters from another source and set them", function() {
                expect(context.get()).toEqual({url: "someurl", module: "test_module", model: {}, collection: {}});
            });

            it("should fire off a context event", function() {
                expect(stub.called()).toBeTruthy();
            });

            it("should reset the state", function() {
                expect(context.get()).not.toEqual({});
                context.reset();
                expect(context.get()).toEqual({});
            });
        });

        it("should be able to generate sub-contexts by passing in a parent context", function() {
            var subcontext = context.getContext(context, {model: {name: "Some Model"}});
        });
    });
});