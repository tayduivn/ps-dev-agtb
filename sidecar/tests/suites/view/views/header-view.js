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
        var beanStub, hasAccessStub, langGetSpy, getAppStringsStub,
            app, options, view, kbDocument;
        
        SugarTest.seedApp();
        app = SugarTest.app;

        langGetSpy = sinon.spy(app.lang, 'get');
        hasAccessStub = sinon.stub(SUGAR.App.acl,"hasAccess",function() {
            return true;
        });

        beanStub = sinon.stub(SUGAR.App.data,"createBean",function() {
            return {a:'b'};
        });

        getAppStringsStub = sinon.stub(app.lang, 'getAppStrings', function() {
            return 'Create Article';
        });

        // Set up a view and call our method under test: setCreateTasksList
        options = { context: {get: function() {}} };
        view = new SUGAR.App.view.views.HeaderView(options);
        view.setCreateTasksList();

        expect(langGetSpy).toHaveBeenCalled();
        expect(langGetSpy.args[0][0]).toEqual('LBL_CREATE_BUG');
        expect(langGetSpy.args[0][1]).toEqual('Emails');
        expect(langGetSpy.args[1][0]).toEqual('LBL_CREATE_CASE');
        expect(langGetSpy.args[2][0]).toEqual('LBL_CREATE_LEAD');
        expect(getAppStringsStub.args[0][0]).toEqual('LBL_CREATE_KB_DOCUMENT');
        expect(getAppStringsStub.callCount).toEqual(1);
        expect(beanStub.callCount).toEqual(4);
        expect(hasAccessStub.callCount).toEqual(4);
        expect(view.createListLabels.length).toEqual(4);
        expect(view.createListLabels[2]).toEqual('Create Article');

        getAppStringsStub.restore();
        hasAccessStub.restore();
        beanStub.restore();
        langGetSpy.restore();
    });
});
