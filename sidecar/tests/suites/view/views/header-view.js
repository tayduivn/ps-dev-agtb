describe("headerView", function() {
    var app;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
    });

    it("should set current module", function() {
        var options = {
                context: {get: function() {
                    return 'cases';
                }},
                id: "1",
                template: function() {
                    return 'asdf';
                }
            },
            view = new SUGAR.App.view.views.HeaderView(options);
        view.setModuleInfo();
        expect(view.currentModule).toEqual('cases');
    });
    it("should set the current module list", function() {
        var result = fixtures.metadata.moduleList, options, view;
        delete result._hash;
        options = {
            context: {get: function() {
                return 'cases';
            }},
            id: "1",
            template: function() {
                return 'asdf';
            }
        };

        view = new SUGAR.App.view.views.HeaderView(options);
        view.setModuleInfo();
        expect(view.moduleList).toEqual(_.toArray(result));
    });

    it("should properly set the create task list dropdown", function() {
        var beanStub, hasAccessStub, getAppStringsStub,
            app, options, view, kbDocument;
        
        SugarTest.seedApp();
        app = SugarTest.app;

        hasAccessStub = sinon.stub(SUGAR.App.acl,"hasAccess",function() {
            return true;
        });

        getAppStringsStub = sinon.stub(app.lang, 'getAppListStrings', function() {
            return { Bugs: "Bugs", Cases: "Cases", KBDocuments: "KBDocuments", Leads: "Leads"};
        });

        // Set up a view and call our method under test: setCreateTasksList
        options = { context: {get: function() {}} };
        view = new SUGAR.App.view.views.HeaderView(options);
        view.setModuleInfo();
        view.setCreateTasksList();

        expect(view.createListLabels.length).toEqual(2);
        expect(hasAccessStub).toHaveBeenCalled();
        getAppStringsStub.restore();
        hasAccessStub.restore();
    });
});
