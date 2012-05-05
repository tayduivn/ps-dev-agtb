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
        expect(app.metadata.getView("Contacts", "edit")).toBe(meta.modules.Contacts.views.edit.meta);
    });

    it('should get layout definitions', function () {
        expect(app.metadata.getLayout("Contacts")).toBe(meta.modules.Contacts.layouts);
    });

    it('should get a specific layout', function () {
        expect(app.metadata.getLayout("Contacts", "detail")).toBe(meta.modules.Contacts.layouts.detail.meta);
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
        var field = app.metadata.getView("Contacts", "detail").panels[0].fields[3];
        expect(_.isObject(field)).toBeTruthy();
        expect(field.name).toEqual("phone_home");
        expect(field.type).toEqual("text");
        expect(field.label).toEqual("LBL_PHONE_HOME");
    });

    it("should delegate to view-manager if has a custom view controller", function () {
        sinon.spy(app.view, "declareComponent");
        app.metadata.set({modules: { Home: fixtures.metadata.modules.Home }});
        expect(app.view.declareComponent.getCall(0).args[0]).toEqual("view");
        expect(app.view.declareComponent.getCall(0).args[1]).toEqual("login");
        expect(app.view.declareComponent.getCall(0).args[2]).toEqual("Home");
        expect(app.view.declareComponent.getCall(0).args[3]).toMatch(/^\{customCallback.*/);
        app.view.declareComponent.restore();
    });

    it("should delegate to view-manager if has custom layout controller", function () {
        sinon.spy(app.view, "declareComponent");
        app.metadata.set({modules: { Contacts: fixtures.metadata.modules.Contacts}});
        expect(app.view.declareComponent.getCall(0).args[0]).toEqual("layout");
        expect(app.view.declareComponent.getCall(0).args[1]).toEqual("detailplus");
        expect(app.view.declareComponent.getCall(0).args[2]).toEqual("Contacts");
        expect(app.view.declareComponent.getCall(0).args[3]).toMatch(/^\{customLayoutCallback.*/);
        app.view.declareComponent.restore();
    });

    it("should delegate to template.compile if meta set with custom view template", function() {
        sinon.spy(app.template, "compile");
        app.metadata.set({
            modules: { 
                Taxonomy: {
                    views: { 
                        tree: { 
                            template: "My Lil Template"
                        }
                    }
                }
            }
        });
        expect(app.template.compile.getCall(0).args[0]).toEqual('My Lil Template');
        expect(app.template.compile.getCall(0).args[1]).toEqual("tree.taxonomy");
        app.template.compile.restore();
    });

    it("should delegate to template.compile if meta set with custom layout template", function() {
        sinon.spy(app.template, "compile");
        app.metadata.set({
            modules: { 
                Taxonomy: {
                    layouts: { 
                        tree: { 
                            template: "My Lil Template"
                        }
                    }
                }
            }
        });
        expect(app.template.compile.getCall(0).args[0]).toEqual('My Lil Template');
        expect(app.template.compile.getCall(0).args[1]).toEqual("tree.taxonomy");
        app.template.compile.restore();
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
