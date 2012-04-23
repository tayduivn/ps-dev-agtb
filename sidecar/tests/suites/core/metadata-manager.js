describe('Metadata Manager', function () {
    var app, meta = fixtures.metadata;

    beforeEach(function () {
        app = SugarTest.app; 
        app.template.load(meta.viewTemplates);
        app.metadata.set(meta);
    });

    it('should get view definitions', function () {
        expect(app.metadata.getView("Contacts")).toBe(meta.modules.Contacts.views);
    });

    it('should get moduleList', function () {
        var result = meta.moduleList;
        delete result._hash;

        expect(app.metadata.getModuleList()).toBe(result);
    });

    it('should get all modules', function () {
        expect(app.metadata.getModules()['Cases']).toBeDefined(); 
        expect(app.metadata.getModules()['BOGUS']).not.toBeDefined(); 
    });

    it('should get definition for a specific view', function () {
        expect(app.metadata.getView("Contacts", "edit")).toBe(meta.modules.Contacts.views.edit);
    });

    it('should get layout definitions', function () {
        expect(app.metadata.getLayout("Contacts")).toBe(meta.modules.Contacts.layouts);
    });

    it('should get a specific layout', function () {
        expect(app.metadata.getLayout("Contacts", "detail")).toBe(meta.modules.Contacts.layouts.detail);
    });

    it('should get a varchar sugarfield', function () {
        expect(app.metadata.getField('varchar')).toBe(meta.sugarFields.text);
    });

    it('should get a specific sugarfield', function () {
        expect(app.metadata.getField('phone')).toBe(meta.sugarFields.phone);
    });

    it('should get a undefined sugarfield as text', function () {
        expect(app.metadata.getField('doesntexist')).toBe(meta.sugarFields.text);
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

        var server = sinon.fakeServer.create();
        server.respondWith("GET", /.*\/rest\/v10\/metadata\?typeFilter=&moduleFilter=.*/,
                        [200, {  "Content-Type":"application/json"},
                            JSON.stringify(meta)]);

        app.metadata.sync();
        server.respond();
        server.restore();

        expect(SugarTest.storage["test:portal:md:modules"]).toEqual("Cases,Contacts,Home");
        expect(SugarTest.storage["test:portal:md:m:Cases"]).toBeDefined();
        expect(SugarTest.storage["test:portal:md:m:Contacts"]).toBeDefined();
        expect(SugarTest.storage["test:portal:md:m:Home"]).toBeDefined();
        expect(SugarTest.storage["test:portal:md:f:integer"]).toBeDefined();
        expect(SugarTest.storage["test:portal:md:f:password"]).toBeDefined();

    });

});
