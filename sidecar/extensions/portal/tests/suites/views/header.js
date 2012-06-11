describe("Header View", function() {
    var app, HeaderView;

    beforeEach(function() {
        var controller;
        //SugarTest.app.config.env = "dev"; // so I can see app.data ;=)
        controller = SugarTest.loadFile('../../../../../sugarcrm/clients/base/views/header', 'header', 'js', function(d){ return d;});
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        HeaderView = app.view.declareComponent('view', 'Header', null, controller);
    });

    it("should set current module", function() {
        var view, context, options;
        context = app.context.getContext();
        context.get = function() { return 'cases'; };
        options = {
                context: context,
                id: "1",
                template: function() {
                    return 'asdf';
                },
                layout: null
            };
        view = new HeaderView(options);
        view.setModuleInfo();
        expect(view.module).toEqual('cases');
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
        view = new HeaderView(options);
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
        view = new HeaderView(options);
        view.setModuleInfo();
        view.setCreateTasksList();

        expect(view.createListLabels.length).toEqual(2);
        expect(hasAccessStub).toHaveBeenCalled();
        getAppStringsStub.restore();
        hasAccessStub.restore();
    });
});
