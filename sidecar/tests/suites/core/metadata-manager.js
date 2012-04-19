describe('Metadata Manager', function () {
    var app;

    beforeEach(function () {
        app = SugarTest.app; 
        SugarTest.seedFakeServer();
        app.template.load(fixtures.metadata.viewTemplates);
        app.metadata.set(fixtures.metadata);
    });

    it('should get view definitions', function () {
        expect(app.metadata.getView("Contacts")).toBe(fixtures.metadata.modules.Contacts.views);
    });

    it('should get moduleList', function () {
        var result = fixtures.metadata.moduleList;
        delete result._hash;

        expect(app.metadata.getModuleList()).toBe(result);
    });

    it('should get all modules', function () {
        expect(app.metadata.getModules()['Cases']).toBeDefined(); 
        expect(app.metadata.getModules()['BOGUS']).not.toBeDefined(); 
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

    it('should get a varchar sugarfield', function () {
        expect(app.metadata.getField('varchar')).toBe(fixtures.metadata.sugarFields.text);
    });

    it('should get a specific sugarfield', function () {
        expect(app.metadata.getField('phone')).toBe(fixtures.metadata.sugarFields.phone);
    });

    it('should get a undefined sugarfield as text', function () {
        expect(app.metadata.getField('doesntexist')).toBe(fixtures.metadata.sugarFields.text);
    });

    it('should patch view metadata', function () {
        app.metadata.set(fixtures.metadata);
        var field = app.metadata.getView("Contacts", "detail").panels[0].fields[3];
        expect(_.isObject(field)).toBeTruthy();
        expect(field.name).toEqual("phone_home");
        expect(field.type).toEqual("text");
        expect(field.label).toEqual("LBL_PHONE_HOME");
    });

    it ('should sync metadata', function (){
        SugarTest.storage = {};
        SugarTest.server.respondWith("GET", "/rest/v10/metadata?typeFilter=&moduleFilter=",
                        [200, {  "Content-Type":"application/json"},
                            JSON.stringify(fixtures.metadata)]);

        app.metadata.sync();
        SugarTest.server.respond();

        expect(SugarTest.storage["test:portal:md:modules"]).toEqual("Cases,Contacts,Home");
        expect(SugarTest.storage["test:portal:md:m:Cases"]).toBeDefined();
        expect(SugarTest.storage["test:portal:md:m:Contacts"]).toBeDefined();
        expect(SugarTest.storage["test:portal:md:m:Home"]).toBeDefined();
        expect(SugarTest.storage["test:portal:md:f:integer"]).toBeDefined();
        expect(SugarTest.storage["test:portal:md:f:password"]).toBeDefined();

    });

});
