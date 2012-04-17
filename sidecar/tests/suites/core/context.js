describe("Application context manager", function() {
    it("should return a new context object", function() {
        var context = SUGAR.App.context.getContext({}, {});
        expect(context).toBeTruthy();
    });

    describe("Context Object", function() {
        describe("when requesting state", function() {
            var context = SUGAR.App.context.getContext({
                prop1: "Prop1",
                prop2: "Prop2",
                prop3: "Prop3"
            });

            it("should return one property if only one is requested", function() {
                var result = context.get("prop1");
                expect(result).toEqual("Prop1");
            });

            it("should return a subset of properties if so requested", function() {
                var result = context.get(["prop1", "prop2"]);
                expect(result).toEqual({prop1: "Prop1", prop2: "Prop2"});
            });

            it("should return all properties if no parameters are provided", function() {
                var result = context.get();
                expect(result).toEqual({prop1: "Prop1", prop2: "Prop2", prop3: "Prop3"});
            });
        });

        describe("when creating a context", function() {
            var getFieldsSpy = sinon.spy(function() { return [1,2]; });
            var renderSpy    = sinon.spy();
            var context      = SUGAR.App.context.getContext();

            it("should load the context for layout path", function() {
                var params = {
                    id: 123,
                    layout: {
                        getFields: getFieldsSpy,
                        render: renderSpy
                    },
                    module: 'Home'
                };

                context.init(params);
                context.loadData();

                expect(getFieldsSpy).toHaveBeenCalled();
                expect(renderSpy).toHaveBeenCalled();
                expect(context.get('fields')).toEqual([1,2]);
            });

            it("should load the context for create path", function() {
                var stub = sinon.spy();
                var params = {
                    create: stub,
                    module: 'Home'
                };

                context.init(params);
                context.loadData();
                expect(context.get().module).toEqual('Home');
            });

            it("should always load bean for context", function() {
                var params = {
                    module: "Home",
                    layout: {
                        getFields: getFieldsSpy,
                        render: renderSpy
                    }
                };
                context.init(params);
                context.loadData();
                expect(context.state.collection).toBeDefined();
                expect(context.state.model).toBeDefined();
            });
            it("should trigger context:focus when focus called", function() {
                var onFocusSpy = sinon.spy();
                var context = SUGAR.App.context.getContext();
                context.bind("context:focus", onFocusSpy);
                context.init({});
                context.focus(onFocusSpy);
                expect(onFocusSpy).toHaveBeenCalled();
            });
        });

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
            var context = SUGAR.App.context.getContext(); // We don't initialize first because we need to attach an event handler first for test.
            context.bind(context.contextId + ":change", stub);
            context.init(obj, data);

            it("should take context parameters from another source and set them", function() {
                expect(context.get()).toEqual({url: "someurl", module: "test_module", model: {name: "sample"}, collection: {name: "sample collection"}});
            });

            it("should fire off a context event", function() {
                expect(stub).toHaveBeenCalled();
            });

            // Ideally this function should go first, but don't want to write two different context mocks just to test reset. So we test
            // at the end.
            it("should reset the state", function() {
                expect(context.get()).not.toEqual({});
                context.reset();
                expect(context.get()).toEqual({});
            });

            describe("and when a subcontext is required", function() {
                var context = SUGAR.App.context.getContext({module: "my module", url: "this url"}, {collection: {name: "some collection"}}),
                    subcontext = SUGAR.App.context.getContext(context, {model: {name: "Some Model"}}),
                    state = context.get(),
                    state2 = subcontext.get();

                it("should generate sub-contexts from a parent context", function() {
                    expect(subcontext).toBeTruthy();
                });

                describe("the subcontext", function() {
                    it("should be inherit parent context properties except data properties", function() {
                        expect(state.module).toEqual(state2.module);
                        expect(state.url).toEqual(state2.url);
                        expect(state.model).not.toEqual(state2.model);
                        expect(state.collection).not.toEqual(state2.collection);
                    });

                    it("should set the parent to the parent context", function() {
                        expect(subcontext.parent).toEqual(context);
                    });

                    it("should set the children of the parent to the subcontext", function() {
                        var childContext;

                        expect(context.children).not.toBe([]);

                        _.each(context.children, function(child) {
                            if (child === subcontext) {
                                childContext = child;
                            }
                        });

                        expect(childContext).toBeTruthy();
                    });
                });
            });
        });
    });
});
