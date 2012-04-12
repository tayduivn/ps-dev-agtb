describe('Metadata Manager', function () {
    var app = SUGAR.App;
    var server;
    //Preload the templates
    app.template.load(fixtures.metadata.viewTemplates);

    beforeEach(function () {
        //Load the metadata
        app.metadata.set(fixtures.metadata);
    });

    afterEach(function () {
        if (server && server.restore) server.restore();
    });

    it('should get view definitions', function () {
        expect(app.metadata.getView("Contacts")).toBe(fixtures.metadata.modules.Contacts.views);
    });

    it('should get moduleList', function () {
        var result = fixtures.metadata.moduleList;
        delete result._hash;

        expect(app.metadata.getModuleList()).toBe(result);
    });

    it('should get definition for a specific view', function () {
        expect(app.metadata.getView("Contacts", "edit")).toBe(fixtures.metadata.modules.Contacts.views.edit);
    });

    it('should get layout definitions', function () {
        expect(app.metadata.getLayout("Contacts")).toBe(fixtures.metadata.modules.Contacts.layouts);
    });

    it('should get a specific layout', function () {
        expect(app.metadata.getLayout("Contacts", "detail")).toBe(fixtures.metadata.modules.Contacts.layouts.detail);
    });

    it('should get a specific sugarfield', function () {
        expect(app.metadata.getField({
                type:'varchar',
                view:'edit'
        })).toBe(fixtures.metadata.sugarFields.text.views.edit);
    });

    it('should get a specific sugarfield defaulted to default if the view does not exist', function () {
        expect(app.metadata.getField({
                type:'varchar',
                view:'thisViewDoesntExist'
        })).toBe(fixtures.metadata.sugarFields.text.views["default"]);
    });

    it ('should sync metadata', function (){
        SugarTest.storage = {};
        server = sinon.fakeServer.create();
        server.respondWith("GET", "/rest/v10/metadata?typeFilter=&moduleFilter=",
                        [200, {  "Content-Type":"application/json"},
                            JSON.stringify(fixtures.metadata)]);

        app.metadata.sync();
        server.respond();

        expect(SugarTest.storage["test:portal:md:modules"]).toEqual("Cases,Contacts,Home");
        expect(SugarTest.storage["test:portal:md:m:Cases"]).toBeDefined();
        expect(SugarTest.storage["test:portal:md:m:Contacts"]).toBeDefined();
        expect(SugarTest.storage["test:portal:md:m:Home"]).toBeDefined();
        expect(SugarTest.storage["test:portal:md:f:integer"]).toBeDefined();
        expect(SugarTest.storage["test:portal:md:f:password"]).toBeDefined();

    });

});