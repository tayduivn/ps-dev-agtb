describe("DataManager", function() {

    var metadata,
        app = SUGAR.App,
        dm = SUGAR.App.dataManager,
        server;

    beforeEach(function() {
        dm.reset();
        metadata = SugarTest.loadJson("metadata");
    });

    afterEach(function() {
        if (server && server.restore) server.restore();
    });

    it("should be able to create an instance of primary bean and collection", function() {
        dm.declareModels(metadata);

        _.each(_.keys(metadata), function(moduleName) {
            expect(dm.createBean(moduleName, {})).toBeDefined();
            expect(dm.createBeanCollection(moduleName)).toBeDefined();
        });

    });

    it("should be able to create an instance of default bean and collection", function() {
        var moduleName = "Contacts",
            beanType = "Contact";

        dm.declareModel(moduleName, metadata[moduleName]);

        var bean = dm.createBean(moduleName, { someAttr: "Some attr value"});
        expect(bean.module).toEqual(moduleName);
        expect(bean.beanType).toEqual(beanType);
        expect(bean.fields).toEqual(metadata[moduleName].beans[beanType].vardefs.fields);
        expect(bean.get("someAttr")).toEqual("Some attr value");

        var collection = dm.createBeanCollection(moduleName);
        expect(collection.module).toEqual(moduleName);
        expect(collection.beanType).toEqual(beanType);
        expect(collection.model).toBeDefined();

    });

    it("should be able to create an instance of non-default bean and collection", function() {
        var moduleName = "Teams",
            beanType = "TeamSet";

        dm.declareModel(moduleName, metadata[moduleName]);

        var bean = dm.createBean(moduleName, { someAttr: "Some attr value"}, beanType);
        expect(bean.module).toEqual(moduleName);
        expect(bean.beanType).toEqual(beanType);
        expect(bean.fields).toEqual(metadata[moduleName].beans[beanType].vardefs.fields);
        expect(bean.get("someAttr")).toEqual("Some attr value");

        var collection = dm.createBeanCollection(moduleName, undefined, beanType);
        expect(collection.module).toEqual(moduleName);
        expect(collection.beanType).toEqual(beanType);
        expect(collection.model).toBeDefined();

    });

    it("should be able to fetch a bean by ID", function() {
        var moduleName = "Teams",
            beanType = "TeamSet";

        dm.declareModel(moduleName, metadata[moduleName]);

        var mock = sinon.mock(Backbone);
        mock.expects("sync").once().withArgs("read");

        var bean = dm.fetchBean(moduleName, "xyz", null, beanType);

        expect(bean.id).toEqual("xyz");
        expect(bean.module).toEqual(moduleName);
        expect(bean.beanType).toEqual(beanType);
        mock.verify();
    });

    it("should be able to fetch beans", function() {
        var moduleName = "Teams",
            beanType = "TeamSet";
        dm.declareModel(moduleName, metadata[moduleName]);

        var mock = sinon.mock(Backbone);
        mock.expects("sync").once().withArgs("read");

        var collection = dm.fetchBeans(moduleName, null, beanType);

        expect(collection.module).toEqual(moduleName);
        expect(collection.beanType).toEqual(beanType);
        expect(collection.model).toBeDefined();
        mock.verify();
    });

    it("should be able to sync (read) a bean", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata[moduleName]);
        var bean = dm.createBean(moduleName, { id: "1234" });

        var contact = SugarTest.loadJson("contact");

        server = sinon.fakeServer.create();

        server.respondWith("GET", "/rest/v10/Contacts/1234",
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contact)]);

        bean.fetch();
        server.respond();

        expect(bean.get("primary_address_city")).toEqual("Cupertino");
    });

    it("should be able to sync (create) a bean", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata[moduleName]);
        var contact = dm.createBean(moduleName, { first_name: "Clara", last_name: "Tsetkin" });

        server = sinon.fakeServer.create();

        server.respondWith("POST", "/rest/v10/Contacts",
            [200, {  "Content-Type": "application/json"},
                JSON.stringify({ id: "xyz" })]);

        contact.save();
        server.respond();

        expect(contact.id).toEqual("xyz");
    });

    it("should be able to sync (update) a bean", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata[moduleName]);
        var contact = dm.createBean(moduleName, { id: "xyz", first_name: "Clara", last_name: "Tsetkin", dateModified: "1" });

        server = sinon.fakeServer.create();

        server.respondWith("PUT", "/rest/v10/Contacts/xyz",
            [200, {  "Content-Type": "application/json"},
                JSON.stringify({ dateModified: "2" })]);

        contact.save();
        server.respond();

        expect(contact.get("dateModified")).toEqual("2");
    });

    it("should be able to sync (delete) a bean", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata[moduleName]);
        var contact = dm.createBean(moduleName, { id: "xyz" });

        server = sinon.fakeServer.create();

        server.respondWith("DELETE", "/rest/v10/Contacts/xyz",
            [200, {  "Content-Type": "application/json"}, ""]);

        contact.destroy();
        server.respond();
    });

    it("should be able to sync (read) beans", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata[moduleName]);
        var beans = dm.createBeanCollection(moduleName);

        var contacts = SugarTest.loadJson("contacts");

        server = sinon.fakeServer.create();

        server.respondWith("GET", "/rest/v10/Contacts",
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);

        beans.fetch();
        server.respond();

        expect(beans.length).toEqual(2);
        expect(beans.at(0).get("name")).toEqual("Vladimir Vladimirov");
        expect(beans.at(1).get("name")).toEqual("Petr Petrov");

    });


    it("should use getters and setters for sugar fields", function() {
        var moduleName = "Contacts";
        //Declare the SugarField text type handler
        var handler = {
            get : function(model, field) {
                return model.attributes[field];
            },
            set : function(model, field, value) {
                return value;
            }
        };
        var getSpy = sinon.spy(handler, "get");
        var setSpy = sinon.spy(handler, "set");
        var stubGFH = sinon.stub(app.sugarFieldManager, "getFieldHandler");
        stubGFH.withArgs("varchar").returns(handler);

        dm.declareModel(moduleName, metadata[moduleName]);

        var bean = dm.createBean(moduleName, { first_name: "test", last_name: "more"});
        bean.set("first_name", "bar");
        expect(bean.get("first_name")).toBe("bar");
        expect(getSpy.called).toBeTruthy();
        expect(setSpy.called).toBeTruthy();

        stubGFH.restore();
    });



});