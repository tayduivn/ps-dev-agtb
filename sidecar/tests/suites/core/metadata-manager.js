describe('Metadata Manager', function() {
    var app, meta = fixtures.metadata;

    beforeEach(function() {
        app = SugarTest.app;
        app.metadata.set(meta);
    });

    it('should get metadata hash', function() {
        expect(app.metadata.getHash()).toEqual("2q34aasdfwrasdfse");
    });

    it('should get view definitions', function() {
        expect(app.metadata.getView("Contacts")).toBe(meta.modules.Contacts.views);
    });

    it('should get moduleList', function() {
        var result = meta.moduleList;
        delete result._hash;

        expect(app.metadata.getModuleList()).toBe(result);
    });

    it('should get all modules', function() {
        expect(app.metadata.getModules()['Cases']).toBeDefined();
        expect(app.metadata.getModules()['BOGUS']).not.toBeDefined();
    });

    it('should get definition for a specific view', function() {
        expect(app.metadata.getView("Contacts", "edit")).toEqual(meta.modules.Contacts.views.edit.meta);
    });

    it('should get base view definitions', function() {
        expect(app.metadata.getView("Test", "list")).toEqual(meta.views.list);
    });

    it('should get layout definitions', function() {
        expect(app.metadata.getLayout("Contacts")).toEqual(meta.modules.Contacts.layouts);
    });

    it('should get default layout defs', function() {
        expect(app.metadata.getLayout("Test", "list")).toEqual(meta.layouts.list.meta);
    });

    it('should get a specific layout', function() {
        expect(app.metadata.getLayout("Contacts", "detail")).toBe(meta.modules.Contacts.layouts.detail.meta);
    });

    it('should get a varchar sugarfield', function() {
        expect(app.metadata.getField('varchar')).toBe(meta.fields.text);
    });

    it('should get a specific sugarfield', function() {
        expect(app.metadata.getField('phone')).toBe(meta.fields.phone);
    });

    it('should get a undefined sugarfield as text', function() {
        expect(app.metadata.getField('doesntexist')).toBe(meta.fields.text);
    });

    it('should get strings', function() {
        expect(app.metadata.getStrings("modStrings")).toBe(meta.modStrings);
        expect(app.metadata.getStrings("appStrings")).toBe(meta.appStrings);
        expect(app.metadata.getStrings("appListStrings")).toBe(meta.appListStrings);
    });

    it('should patch field displayParams metadata', function() {
        var field = app.metadata.getView("Contacts", "edit").panels[0].fields[2];
        expect(_.isObject(field)).toBeTruthy();
        expect(field.name).toEqual("phone_home");
        expect(field.type).toEqual("text");
        expect(field.label).toEqual("Phone");
        expect(field.required).toBeTruthy();
    });

    it('should patch view metadata', function() {
        var field = app.metadata.getView("Contacts", "detail").panels[0].fields[3];
        expect(_.isObject(field)).toBeTruthy();
        expect(field.name).toEqual("phone_home");
        expect(field.type).toEqual("text");
    });

    it("should delegate to view-manager if has a custom view controller", function() {
        sinon.spy(app.view, "declareComponent");
        app.metadata.set({modules: { Home: fixtures.metadata.modules.Home }});
        expect(app.view.declareComponent.getCall(0).args[0]).toEqual("view");
        expect(app.view.declareComponent.getCall(0).args[1]).toEqual("login");
        expect(app.view.declareComponent.getCall(0).args[2]).toEqual("Home");
        expect(app.view.declareComponent.getCall(0).args[3]).toMatch(/^\{customCallback.*/);
        app.view.declareComponent.restore();
    });

    it("should delegate to view-manager if has custom layout controller", function() {
        sinon.spy(app.view, "declareComponent");
        app.metadata.set({modules: { Contacts: fixtures.metadata.modules.Contacts}});
        expect(app.view.declareComponent.getCall(0).args[0]).toEqual("layout");
        expect(app.view.declareComponent.getCall(0).args[1]).toEqual("detailplus");
        expect(app.view.declareComponent.getCall(0).args[2]).toEqual("Contacts");
        expect(app.view.declareComponent.getCall(0).args[3]).toMatch(/^\{customLayoutCallback.*/);
        app.view.declareComponent.restore();
    });

    it("should delegate to template.compile if meta set with custom view template", function() {
        sinon.spy(app.template, "setView");
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
        expect(app.template.setView.getCall(0).args[0]).toEqual("tree");
        expect(app.template.setView.getCall(0).args[1]).toEqual("Taxonomy");
        expect(app.template.setView.getCall(0).args[2]).toEqual('My Lil Template');
        expect(Handlebars.templates["tree.Taxonomy"]).toBeDefined();
        app.template.setView.restore();
    });

    describe('when syncing metdata', function() {
        beforeEach(function() {
            app.cache.cutAll(true);
            SugarTest.seedFakeServer();
        });

        it('should sync metadata', function() {
            // Verify hash doesn't exist
            expect(SugarTest.storage["test:portal:md:_hash"]).toBeUndefined();

            SugarTest.server.respondWith("GET", /.*\/rest\/v10\/metadata\?typeFilter=&moduleFilter=.*/,
                [200, {"Content-Type": "application/json"}, JSON.stringify(meta)]);

            app.metadata.sync();
            SugarTest.server.respond();

            expect(SugarTest.storage["test:portal:md:modules"]).toEqual("Cases,Contacts,Accounts,Home");
            expect(SugarTest.storage["test:portal:md:m:Cases"]).toBeDefined();
            expect(SugarTest.storage["test:portal:md:m:Contacts"]).toBeDefined();
            expect(SugarTest.storage["test:portal:md:m:Home"]).toBeDefined();
            expect(SugarTest.storage["test:portal:md:r:contacts_accounts"]).toBeDefined();
            expect(SugarTest.storage["test:portal:md:f:integer"]).toBeDefined();
            expect(SugarTest.storage["test:portal:md:f:password"]).toBeDefined();
            expect(SugarTest.storage["test:portal:templates"]).toBeDefined();
            expect(SugarTest.storage["test:portal:md:lang:modStrings"]).toBeDefined();
            expect(SugarTest.storage["test:portal:md:lang:appStrings"]).toBeDefined();
            expect(SugarTest.storage["test:portal:md:lang:appListStrings"]).toBeDefined();
            expect(SugarTest.storage["test:portal:md:acl"]).toBeDefined();
            expect(SugarTest.storage["test:portal:md:moduleList"]).toBeDefined();
        });

        it('should not take any action when server returns 304', function() {
            SugarTest.server.respondWith("GET", /.*\/rest\/v10\/metadata\?typeFilter=&moduleFilter=.*/,
                [304, {"Content-Type": "application/json"}, JSON.stringify(meta)]);

            app.metadata.sync();
            SugarTest.server.respond();

            expect(SugarTest.storage["test:portal:md:modules"]).not.toEqual("Cases,Contacts,Accounts,Home");
            expect(SugarTest.storage["test:portal:md:m:Cases"]).toBeUndefined();
            expect(SugarTest.storage["test:portal:md:m:Contacts"]).toBeUndefined();
            expect(SugarTest.storage["test:portal:md:m:Home"]).toBeUndefined();
            expect(SugarTest.storage["test:portal:md:f:integer"]).toBeUndefined();
            expect(SugarTest.storage["test:portal:md:f:password"]).toBeUndefined();
        });

    });
});
