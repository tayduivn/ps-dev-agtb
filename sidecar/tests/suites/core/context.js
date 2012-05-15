describe("Context", function() {
    var app;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
    });

    it("should return a new context object", function() {
        var context = app.context.getContext({});
        expect(context.attributes).toEqual({});
    });

    it("should return properties", function() {
        var context = app.context.getContext({
            prop1: "Prop1",
            prop2: "Prop2",
            prop3: "Prop3"
        });
        expect(context.get("prop1")).toEqual("Prop1");
        expect(context.attributes).toEqual({prop1: "Prop1", prop2: "Prop2", prop3: "Prop3"});
    });

    it("should prepare data for a module path", function() {
        var context = app.context.getContext({module:'Contacts'});
        expect(context.attributes.model).toBeUndefined();
        expect(context.attributes.collection).toBeUndefined();

        context.prepareData();

        expect((context.attributes.model instanceof Backbone.Model)).toBeTruthy();
        expect((context.attributes.collection instanceof Backbone.Collection)).toBeTruthy();

        expect(context.attributes.model.module).toEqual("Contacts");
        expect(context.attributes.collection.module).toEqual("Contacts");
    });

    it("should prepare data for a record path", function() {
        var context = app.context.getContext({
            modelId: '123',
            module: 'Cases'
        });

        context.prepareData();

        expect(context.attributes.model).toBeDefined();
        expect(context.attributes.model.id).toEqual("123");
    });

    it("should prepare data for a create path", function() {
        var context = app.context.getContext({
            create: true,
            module: 'Cases'
        });

        context.prepareData();

        expect(context.get("module")).toEqual('Cases');
        expect(context.get("model") instanceof app.Bean).toBeTruthy();
        expect(context.get("model").isNew()).toBeTruthy();
    });

    it("should load data for a module path", function() {
        var collection = app.data.createBeanCollection("Cases");
        var context = app.context.getContext({
            collection: collection,
            module: 'Cases'
        });

        var mock = sinon.mock(collection).expects("fetch").once();
        context.loadData();

        mock.verify();
    });

    it("should load data for a record path", function() {
        var model = app.data.createBean("Cases", { id: "xyz" });
        var context = app.context.getContext({
            model: model,
            module: 'Cases',
            modelId: 'xyz'
        });

        var mock = sinon.mock(model).expects("fetch").once();
        context.loadData();

        mock.verify();
    });

    it("should set the order by on collection if defined in config", function() {
        var collection = app.data.createBeanCollection("Cases");

        var context = app.context.getContext({
            module: 'Cases',
            collection: collection
        });
        app.config.orderByDefaults = {
            'Cases': {
                field: 'case_number',
                direction: 'asc'
            }
        };

        context.loadData();

        expect(context.get('collection').orderBy).toBeDefined();
        expect(context.get('collection').orderBy.field).toEqual('case_number');
        expect(context.get('collection').orderBy.direction).toEqual('asc');
    });

    it("should maintain order by if already set on collection event if defined in config", function() {
        var collection = app.data.createBeanCollection("Cases"), params;
        collection.orderBy = {
                field: 'fooby',
                direction: 'updownallaround'
            };

        var context = app.context.getContext({
            module: 'Cases',
            collection: collection
        });

        app.config.orderByDefaults = {
            'Cases': {
                field: 'case_number',
                direction: 'asc'
            }
        };

        context.loadData();

        expect(context.get('collection').orderBy).toBeDefined();
        expect(context.get('collection').orderBy.field).toEqual('fooby');
        expect(context.get('collection').orderBy.direction).toEqual('updownallaround');
    });

    describe("Child context", function() {

        it("should create child contexts and prepare data", function() {
            var model = app.data.createBean("Contacts", { id: "xyz"});
            var context = app.context.getContext({
                module: "Contacts",
                model: model
            });

            var subcontext = context.getChildContext({ module: "Accounts" });
            var subrelatedContext = context.getChildContext({ link: "accounts" });

            expect(context.children.length).toEqual(2);
            expect(subcontext.parent).toEqual(context);
            expect(subcontext.attributes..module).toEqual("Accounts");

            expect(subrelatedContext.parent).toEqual(context);
            expect(subrelatedContext.attributes.link).toEqual("Accounts");
            expect(subrelatedContext.attributes..parentModel).toEqual(model);

            subcontext.prepareData();
            expect(subcontext.attributes.model).toBeDefined();

            subrelatedContext.prepareRelatedData();
            expect(subcontext.attributes.model).toBeDefined();
            expect(subcontext.attributes.model.module).toEqual("Accounts");

            context.clear();
            expect(context.children.length).toEqual(0);

            subrelatedContext.clear();
            expect(context.parent).toBeNull();
        });

    });

});
