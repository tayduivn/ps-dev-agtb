describe("Application context manager", function() {
    it("should exist within the framework", function() {
        expect(SUGAR.App.context).toBeTruthy();
    });

    it("should return a new context object", function() {
        var context = SUGAR.App.context.getContext({}, {});
        expect(context).toBeTruthy();
    });

    describe("Context Object", function() {
        describe("when a new state is required", function() {
            var obj = {
                url: "someurl",
                module: "test_module"
            };

            var data = {
                model: {name: "sample"},
                collection: {name: "sample collection"}
            };

            var stub = sinon.spy();
            var context = SUGAR.App.context.getContext();
            context.bind(context.contextId + ":change", stub);
            context.init(obj, data);

            it("should take context parameters from another source and set them", function() {
                expect(context.get()).toEqual({url: "someurl", module: "test_module", model: {name: "sample"}, collection: {name: "sample collection"}});
            });

            it("should fire off a context event", function() {
                expect(stub.called).toBeTruthy();
            });

            it("should reset the state", function() {
                expect(context.get()).not.toEqual({});
                context.reset();
                expect(context.get()).toEqual({});
            });

            describe("and when a subcontext is required", function() {
                it("should be able to generate sub-contexts from a parent context", function() {
                    var context = SUGAR.App.context.getContext({module: "my module", url: "this url"}, {collection: {name: "some collection"}}),
                        subcontext = SUGAR.App.context.getContext(context, {model: {name: "Some Model"}}),
                        state = context.get(),
                        state2 = subcontext.get();

                    expect(state.module).toEqual(state2.module);
                    expect(state.url).toEqual(state2.url);
                    expect(state.model).not.toEqual(state2.model);
                    expect(state.collection).not.toEqual(state2.collection);
                });
            });
        });
    });
});