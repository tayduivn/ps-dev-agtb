describe("DataManager", function() {

    var metadata, app,
        dm = SUGAR.App.data;

    beforeEach(function() {
        app = SugarTest.app;
        metadata = SugarTest.loadFixture("metadata");
        app.config.maxQueryResult = 2;
        dm.reset();
    });

    it("should be able to create an empty instance of bean and collection", function() {
        dm.declareModels(metadata);

        _.each(_.keys(metadata.modules), function(moduleName) {
            expect(dm.createBean(moduleName)).toBeDefined();
            expect(dm.createBeanCollection(moduleName)).toBeDefined();
        });

    });

    it("should be able to create an instance of bean and collection", function() {
        var moduleName = "Contacts";

        dm.declareModel(moduleName, metadata.modules[moduleName]);

        var bean = dm.createBean(moduleName, { someAttr: "Some attr value"});
        expect(bean.module).toEqual(moduleName);
        expect(bean.fields).toEqual(metadata.modules[moduleName].fields);
        expect(bean.get("someAttr")).toEqual("Some attr value");

        var collection = dm.createBeanCollection(moduleName);
        expect(collection.module).toEqual(moduleName);
        expect(collection.model).toBeDefined();

    });

    it("should be able to fetch a bean by ID", function() {
        var moduleName = "Teams";

        dm.declareModel(moduleName, metadata.modules[moduleName]);

        var mock = sinon.mock(Backbone);
        mock.expects("sync").once().withArgs("read");

        var bean = dm.createBean(moduleName, {id: "xyz"});
        bean.fetch();

        expect(bean.id).toEqual("xyz");
        expect(bean.module).toEqual(moduleName);
        mock.verify();
    });

    it("should be able to fetch beans", function() {
        var moduleName = "Teams";
        dm.declareModel(moduleName, metadata.modules[moduleName]);

        var mock = sinon.mock(Backbone);
        mock.expects("sync").once().withArgs("read");

        var collection = dm.createBeanCollection(moduleName, null);
        collection.fetch();

        expect(collection.module).toEqual(moduleName);
        expect(collection.model).toBeDefined();
        mock.verify();
    });

    it("should be able to sync (read) a bean", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var bean = dm.createBean(moduleName, { id: "1234" });

        var contact = SugarTest.loadFixture("contact");

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\/1234.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contact)]);

        bean.fetch();
        SugarTest.server.respond();

        expect(bean.get("primary_address_city")).toEqual("Cupertino");
    });

    it("should be able to sync (create) a bean", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var contact = dm.createBean(moduleName, { first_name: "Clara", last_name: "Tsetkin" });

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("POST", /.*\/rest\/v10\/Contacts.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify({ id: "xyz" })]);

        contact.save();
        SugarTest.server.respond();

        expect(contact.id).toEqual("xyz");
    });

    it("should be able to sync (update) a bean", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var contact = dm.createBean(moduleName, { id: "xyz", first_name: "Clara", last_name: "Tsetkin", dateModified: "1" });

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("PUT", /.*\/rest\/v10\/Contacts\/xyz.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify({ dateModified: "2" })]);

        contact.save();
        SugarTest.server.respond();

        expect(contact.get("dateModified")).toEqual("2");
    });

    it("should be able to sync (delete) a bean", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var contact = dm.createBean(moduleName, { id: "xyz" });

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("DELETE", /.*\/rest\/v10\/Contacts\/xyz.*/,
            [200, {  "Content-Type": "application/json"}, ""]);

        contact.destroy();
        SugarTest.server.respond();
    });

    it("should be able to sync (read) beans", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var beans = dm.createBeanCollection(moduleName);

        var contacts = SugarTest.loadFixture("contacts");

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts[?]{1}maxresult=2.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);

        beans.fetch();
        SugarTest.server.respond();

        expect(beans.length).toEqual(2);
        expect(beans.at(0).get("name")).toEqual("Vladimir Vladimirov");
        expect(beans.at(1).get("name")).toEqual("Petr Petrov");
        expect(beans.at(1).module).toEqual("Contacts");
        expect(beans.at(1).fields).toBeDefined();
    });

    it("should be able to handle sync errors", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var bean = dm.createBean(moduleName);
        var spy = sinon.spy();

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith([422, {}, ""]);
        bean.save(null, {error: spy});
        SugarTest.server.respond();

        expect(spy.called).toBeTruthy();
    });

    it("should add result count and next offset to a collection if in server response", function(){
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var beans = dm.createBeanCollection(moduleName);

        var contacts = SugarTest.loadFixture("contacts");

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts[?]{1}maxresult=2.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);

        beans.fetch();
        SugarTest.server.respond();

        expect(beans.offset).toEqual(2);
    });

});
