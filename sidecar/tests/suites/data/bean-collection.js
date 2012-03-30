describe("BeanCollection", function() {
    var metadata,
        app = SUGAR.App,
        dm = SUGAR.App.dataManager,
        server;

    beforeEach(function() {

        app.config.maxQueryResult = 2;
        dm.reset();
        metadata = fixtures.metadata;
    });

    afterEach(function() {
        if (server && server.restore) server.restore();
    });

    it("should get records for page +n from the current", function() {
        app.config.maxQueryResult = 1;

        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var beans = dm.createBeanCollection(moduleName);

        var contacts = SugarTest.loadJson("contacts");

        contacts.next_offset = 1;
        contacts.result_count = 1;
        contacts.records.pop();

        server = sinon.fakeServer.create();
        server.respondWith("GET", "/rest/v10/Contacts?maxresult=1",
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);
        var syncSpy = sinon.spy(beans, "fetch");
        beans.fetch();
        server.respond();

        beans.paginate();
        expect(syncSpy).toHaveBeenCalledTwice();
        expect(syncSpy.getCall(1).args[0].offset).toEqual(1);
        syncSpy.restore();
    });
    it("should get records for page -n from the current", function() {
        app.config.maxQueryResult = 1;

        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var beans = dm.createBeanCollection(moduleName);

        var contacts = SugarTest.loadJson("contacts");

        contacts.next_offset = 1;
        contacts.result_count = 1;
        contacts.records.pop();

        server = sinon.fakeServer.create();
        server.respondWith("GET", "/rest/v10/Contacts?maxresult=1",
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);
        var syncSpy = sinon.spy(beans, "fetch");
        beans.fetch();
        server.respond();

        beans.paginate();
        expect(syncSpy).toHaveBeenCalledTwice();
        expect(syncSpy.getCall(1).args[0].offset).toEqual(1);
        var options = {page: -1};
        beans.paginate(options);
        expect(syncSpy.getCall(2).args[0].offset).toEqual(-1);

        syncSpy.restore();
    });
    it("should append records for page +n", function() {
        app.config.maxQueryResult = 1;

        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var beans = dm.createBeanCollection(moduleName);

        var contacts = SugarTest.loadJson("contacts");
        var subSetContacts = contacts;
        subSetContacts.next_offset = 1;
        subSetContacts.result_count = 1;
        subSetContacts.records.pop();

        server = sinon.fakeServer.create();
        server.respondWith("GET", "/rest/v10/Contacts?maxresult=1",
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(subSetContacts)]);
        var syncSpy = sinon.spy(beans, "fetch");
        beans.fetch();

        server.respond();
        server.restore();
        var contacts = SugarTest.loadJson("contacts");

        contacts.records.shift();
        server = sinon.fakeServer.create();

        server.respondWith("GET", "/rest/v10/Contacts?offset=1&maxresult=1",
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);

        beans.paginate({add: true});
        server.respond();

        expect(beans.models.length).toEqual(2);
    });

    it("should get the current page number", function() {
        app.config.maxQueryResult = 1;

        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var beans = dm.createBeanCollection(moduleName);

        beans.offset = 3;
        app.config.maxQueryResult = 2;

        var p = beans.getPageNumber();
        expect(p).toEqual(2);
    });

    it("should trigger app:collection:fetch on fetch", function() {
        var triggerFuncSpy = sinon.spy(function(data){
                    var x = 2;
                    return x;
                });
        app.config.maxQueryResult = 1;
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var beans = dm.createBeanCollection(moduleName);

        var contacts = SugarTest.loadJson("contacts");

        contacts.next_offset = 1;
        contacts.result_count = 1;
        contacts.records.pop();

        server = sinon.fakeServer.create();
        server.respondWith("GET", "/rest/v10/Contacts?maxresult=1",
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);
        beans.on("app:collection:fetch", triggerFuncSpy, this);
        beans.fetch();
        server.respond();
        server.restore();
        expect(triggerFuncSpy).toHaveBeenCalledOnce();
    });
});