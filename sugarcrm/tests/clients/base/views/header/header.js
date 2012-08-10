describe("Header View", function() {

    var app, view;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        var context = app.context.getContext();
        view = SugarTest.createView("base","Cases", "header", null, context);
        view.model = new Backbone.Model();
    });
    
    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view.model = null;
        view = null;
    });

    it("should set current module", function() {
        view.setModuleInfo();
        expect(view.module).toEqual('Cases');
    });

    it("should set the current module list", function() {
        var originalModuleList, 
            result = fixtures.metadata.moduleList;

        // Temporarily reset the display modules to our fixture's module list.
        originalModuleList = app.config.displayModules;
        app.config.displayModules = _.toArray(result);
        delete result._hash;
        view.setModuleInfo();
        expect(view.moduleList).toEqual(_.toArray(result));
        app.config.displayModules = originalModuleList;
    });

    it("should properly set the create task list dropdown", function() {
        var beanStub, 
            hasAccessStub = sinon.stub(SUGAR.App.acl,"hasAccess",function() {
                return true;
            });

        // setCreateTasksList is our system under test
        view.setModuleInfo();
        view.setCreateTasksList();

        expect(view.createListLabels.length).toEqual(2);
        expect(hasAccessStub).toHaveBeenCalled();
        hasAccessStub.restore();
    });
});
