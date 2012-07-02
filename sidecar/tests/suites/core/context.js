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

        context.prepare();

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

        context.prepare();

        expect(context.attributes.model).toBeDefined();
        expect(context.attributes.model.id).toEqual("123");
    });

    it("should prepare data for a create path", function() {
        var context = app.context.getContext({
            create: true,
            module: 'Cases'
        });

        context.prepare();

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

        // Prevent outgoing http request
        var stub = sinon.stub(jQuery, 'ajax');
        context.loadData();
        stub.restore();

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

        // Prevent outgoing http request
        var stub = sinon.stub(jQuery, 'ajax');
        context.loadData();
        stub.restore();

        expect(context.get('collection').orderBy).toBeDefined();
        expect(context.get('collection').orderBy.field).toEqual('fooby');
        expect(context.get('collection').orderBy.direction).toEqual('updownallaround');
    });

    it("should prepare data for a link path", function() {
        var context = app.context.getContext({
            link: "accounts",
            parentModelId: 'xyz',
            parentModule: "Contacts"
        });

        context.prepare();

        expect(context.get("parentModel")).toBeDefined();
        expect(context.get("parentModel").module).toEqual("Contacts");
        expect(context.get("parentModel").id).toEqual("xyz");

        expect(context.get("collection")).toBeDefined();
        expect(context.get("collection").module).toEqual("Accounts");
        expect(context.get("collection").link).toBeDefined();
        expect(context.get("collection").link.name).toEqual("accounts");
        expect(context.get("collection").link.bean).toEqual(context.get("parentModel"));
    });

    it("should prepare data for a link path with pre-filled parent model", function() {
        var context = app.context.getContext({
            link: "accounts",
            parentModel: app.data.createBean("Contacts", { id: "xyz "})
        });

        context.prepare();

        expect(context.get("collection")).toBeDefined();
        expect(context.get("collection").module).toEqual("Accounts");
        expect(context.get("collection").link).toBeDefined();
        expect(context.get("collection").link.name).toEqual("accounts");
        expect(context.get("collection").link.bean).toEqual(context.get("parentModel"));
    });

    it("should prepare data for a related record path", function() {
        var context = app.context.getContext({
            link: "accounts",
            parentModelId: 'xyz',
            parentModule: "Contacts",
            modelId: 'asd'
        });

        context.prepare();

        expect(context.get("parentModel")).toBeDefined();
        expect(context.get("parentModel").module).toEqual("Contacts");
        expect(context.get("parentModel").id).toEqual("xyz");

        expect(context.get("model")).toBeDefined();
        expect(context.get("model").module).toEqual("Accounts");
        expect(context.get("model").id).toEqual("asd");
        expect(context.get("model").link).toBeDefined();
        expect(context.get("model").link.name).toEqual("accounts");
        expect(context.get("model").link.bean).toEqual(context.get("parentModel"));
        expect(context.get("model").link.isNew).toBeTruthy();
    });

    it("should prepare data for a create related record path", function() {
        var context = app.context.getContext({
            link: "accounts",
            parentModelId: 'xyz',
            parentModule: "Contacts",
            create: true
        });

        context.prepare();

        expect(context.get("parentModel")).toBeDefined();
        expect(context.get("parentModel").module).toEqual("Contacts");
        expect(context.get("parentModel").id).toEqual("xyz");

        expect(context.get("model")).toBeDefined();
        expect(context.get("model").module).toEqual("Accounts");
        expect(context.get("model").isNew()).toBeTruthy();
        expect(context.get("model").link).toBeDefined();
        expect(context.get("model").link.name).toEqual("accounts");
        expect(context.get("model").link.bean).toEqual(context.get("parentModel"));
        expect(context.get("model").link.isNew).toBeTruthy();
    });

    describe("Child context", function() {

        it("should create and prepare child contexts from a parent model", function() {
            var model = app.data.createBean("Contacts", { id: "xyz"});
            var context = app.context.getContext({
                module: "Contacts",
                model: model
            });

            var subcontext = context.getChildContext({ module: "Accounts" });

            expect(context.children.length).toEqual(1);
            expect(subcontext.parent).toEqual(context);
            expect(subcontext.get("module")).toEqual("Accounts");

            var subcontext2 = context.getChildContext({ module: "Accounts" });
            expect(subcontext).toEqual(subcontext2);

            expect(context.children.length).toEqual(1);

            subcontext.prepare();
            expect(subcontext.get("model")).toBeDefined();
            expect(subcontext.get("module")).toEqual("Accounts");

            context.clear();
            expect(context.children.length).toEqual(0);
            expect(context.parent).toBeNull();
        });

        it("should create and prepare child contexts from a link name", function() {
            var model = app.data.createBean("Contacts", { id: "xyz"});
            var context = app.context.getContext({
                module: "Contacts",
                model: model
            });

            var subrelatedContext = context.getChildContext({ link: "accounts" });

            expect(context.children.length).toEqual(1);

            expect(subrelatedContext.parent).toEqual(context);
            expect(subrelatedContext.get("link")).toEqual("accounts");
            expect(subrelatedContext.get("parentModel")).toEqual(model);

            var subrelatedContext2 = context.getChildContext({ link: "accounts" });
            expect(subrelatedContext).toEqual(subrelatedContext2);

            expect(context.children.length).toEqual(1);

            subrelatedContext.prepare();

            expect(subrelatedContext.get("model")).toBeDefined();
            expect(subrelatedContext.get("model").module).toEqual("Accounts");
            expect(subrelatedContext.get("parentModule")).toEqual("Contacts");
            expect(subrelatedContext.get("module")).toEqual("Accounts");

            context.clear();
            expect(context.children.length).toEqual(0);

            subrelatedContext.clear();
            expect(context.parent).toBeNull();
        });
    });

});
