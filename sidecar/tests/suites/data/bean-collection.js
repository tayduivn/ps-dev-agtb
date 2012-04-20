describe("BeanCollection", function() {
    var metadata, app,
        dm = SUGAR.App.data;

    beforeEach(function() {
        app = SugarTest.app; 
        app.config.maxQueryResult = 2;
        metadata = SugarTest.loadFixture("metadata"); 
        dm.reset();
    });

    it("should get records for page +n from the current", function() {
        app.config.maxQueryResult = 1;

        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var beans = dm.createBeanCollection(moduleName);

        var contacts = SugarTest.loadFixture("contacts");

        contacts.next_offset = 1;
        contacts.result_count = 1;
        contacts.records.pop();

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\?maxresult=1/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);
        var syncSpy = sinon.spy(beans, "fetch");

        beans.fetch();
        SugarTest.server.respond();

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

        var contacts = SugarTest.loadFixture("contacts");

        contacts.next_offset = 1;
        contacts.result_count = 1;
        contacts.records.pop();

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\?maxresult=1/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);
        var syncSpy = sinon.spy(beans, "fetch");
        beans.fetch();
        SugarTest.server.respond();

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

        var contacts = SugarTest.loadFixture("contacts");
        var subSetContacts = contacts;
        subSetContacts.next_offset = 1;
        subSetContacts.result_count = 1;
        subSetContacts.records.pop();

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\?maxresult=1/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(subSetContacts)]);
        var syncSpy = sinon.spy(beans, "fetch");
        beans.fetch();

        SugarTest.server.respond();
        SugarTest.server.restore();
        var contacts = SugarTest.loadFixture("contacts");

        contacts.records.shift();
        var server = sinon.fakeServer.create();

        server.respondWith("GET", /.*\/rest\/v10\/Contacts\?offset=1&maxresult=1/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);

        beans.paginate({add: true});
        server.respond();

        expect(beans.models.length).toEqual(2);
    });

    it("should get records by order by", function() {
        app.config.maxQueryResult = 1;
        var ajaxSpy = sinon.spy(jQuery, 'ajax');
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var beans = dm.createBeanCollection(moduleName);

        var contacts = SugarTest.loadFixture("contacts");
        var subSetContacts = contacts;
        beans.orderBy = {
            field: "bob",
            direction: "asc"
        };

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\?maxresult=1&orderBy=bob%3Aasc/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(subSetContacts)]);
        beans.fetch();
        SugarTest.server.respond();
        expect(ajaxSpy.getCall(1).args[0].url).toMatch(/.*\/rest\/v10\/Contacts\?maxresult=1&orderBy=bob%3Aasc/);
        ajaxSpy.restore();
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
        var triggerFuncSpy = sinon.spy(function(data) {
            var x = 2;
            return x;
        });
        app.config.maxQueryResult = 1;
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var beans = dm.createBeanCollection(moduleName);

        var contacts = SugarTest.loadFixture("contacts");

        contacts.next_offset = 1;
        contacts.result_count = 1;
        contacts.records.pop();

        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("GET", /.*\/rest\/v10\/Contacts\?maxresult=1/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify(contacts)]);
        beans.on("app:collection:fetch", triggerFuncSpy, this);
        beans.fetch();
        SugarTest.server.respond();
        expect(triggerFuncSpy).toHaveBeenCalledOnce();
    });
});
