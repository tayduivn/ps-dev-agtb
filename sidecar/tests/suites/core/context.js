describe("Application context manager", function() {
    var app, context;

    // TODO: This test suite MUST BE refactored

    beforeEach(function() {
        app = SugarTest.app;
        app.metadata.set(fixtures.metadata);
        app.data.declareModels(fixtures.metadata);
    });

    it("should return a new context object", function() {
        var context = app.context.getContext({}, {});
        expect(context).toBeTruthy();
    });

    it("should prepare its model and collection properties for standard modules", function() {
        var context = SUGAR.App.context.getContext({module:'Contacts'});
        expect(context.state.model).toBeUndefined();
        expect(context.state.collection).toBeUndefined();
        context.prepareData();
        expect((context.state.model instanceof Backbone.Model)).toBeTruthy();
        expect((context.state.collection instanceof Backbone.Collection)).toBeTruthy();
    });

    describe("Context Object", function() {
        describe("when requesting state", function() {

            beforeEach(function() {
                context = app.context.getContext({
                    prop1: "Prop1",
                    prop2: "Prop2",
                    prop3: "Prop3"
                });
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
            var getFieldsSpy = sinon.spy(function() {
                    return [1, 2];
                }),
                renderSpy = sinon.spy();

            beforeEach(function() {
                context = app.context.getContext();
            });

            it("should load the context for layout path", function() {
                var params = {
                    id: 123,
                    layout: {
                        getFields: getFieldsSpy,
                        render: renderSpy
                    },
                    module: 'Cases'
                };

                context.init(params);
                context.prepareData();
                context.loadData();

                expect(getFieldsSpy).toHaveBeenCalled();
            });

            it("should load the context for create path", function() {
                var stub = sinon.spy(),
                    params = {
                        create: stub,
                        module: 'Cases'
                    };

                context.init(params);
                context.loadData();
                expect(context.get().module).toEqual('Cases');
            });

            it("should always load bean for context", function() {
                var params = {
                    module: "Cases",
                    layout: {
                        getFields: getFieldsSpy,
                        render: renderSpy
                    }
                };
                context.init(params);
                context.prepareData();
                expect(context.state.collection).toBeDefined();
                expect(context.state.model).toBeDefined();
            });
            it("should trigger context:focus when focus called", function() {
                var onFocusSpy = sinon.spy(),
                    context = SUGAR.App.context.getContext();

                context.bind("context:focus", onFocusSpy);
                context.init({});
                context.focus(onFocusSpy);
                expect(onFocusSpy).toHaveBeenCalled();
            });
        });

        describe("when a new state is required", function() {
            var stub, context,
                obj = {
                    url: "someurl",
                    module: "test_module"
                },
                data = {
                    model: {name: "sample"},
                    collection: {name: "sample collection"}
                };

            stub = sinon.spy();
            context = SUGAR.App.context.getContext(); // We don't initialize first because we need to attach an event handler first for test.
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

            describe("and when a child context is required", function() {
                SugarTest.seedMetadata();
                var context = SUGAR.App.context.getContext({module: "Contacts",
                        model: {
                            fields: {
                                accounts: {
                                    relationship: "contacts_accounts"
                                }
                            },
                            relationships: {
                                "contacts_accounts": {
                                    lhs_module:"Accounts",
                                    rhs_module:"Contacts"
                                }
                            },
                            getRelatedCollection: function() {
                                return new Backbone.Collection({module:"Accounts"})
                            }}}),
                    childModuleContextDef = {
                        module: "Accounts"
                    },
                    childRelatedContextDef = {
                        link: "accounts"
                    },
                    subcontext = context.getChildContext(childModuleContextDef),
                    subrelatedContext = context.getChildContext(childRelatedContextDef),
                    state = context.get(),
                    state2 = subcontext.get(),
                    state3 = subrelatedContext.get();

                it("should generate  child contexts", function() {
                    expect(context.children.length).toEqual(2);
                    expect(state2).toBeTruthy();
                    expect(state3).toBeTruthy();
                });

                describe("the child context", function() {
                    it("should be of a different module", function() {
                        expect(state.module).not.toEqual(state2.module);
                    });

                    it("should set the parent to the parent context", function() {
                        expect(subcontext.parent).toEqual(context);
                    });
                });
            });
        });
    });
});
