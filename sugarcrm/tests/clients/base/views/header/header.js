describe("Header View", function() {

    var app, view,
        modStrings;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        app.user.set('module_list', fixtures.metadata.module_list);
        var context = app.context.getContext();
        view = SugarTest.createView("base","Cases", "header", null, context);
        modStrings = sinon.stub(SugarTest.app.metadata, 'getStrings', function() {
            return {
                Accounts: {'LBL_MODULE_NAME': 'Accounts'},
                Bugs: {'LBL_MODULE_NAME': 'Bugs'},
                Calendar: {'LBL_MODULE_NAME': 'Calendar'},
                Calls: {'LBL_MODULE_NAME': 'Calls'},
                Campaigns: {'LBL_MODULE_NAME': 'Campaigns'},
                Cases: {'LBL_MODULE_NAME': 'Cases'},
                Contacts: {'LBL_MODULE_NAME': 'Contacts'},
                Forecasts: {'LBL_MODULE_NAME': 'Forecasts'},
                Home: {'LBL_MODULE_NAME': 'Home'},
                Opportunities: {'LBL_MODULE_NAME': 'Opportunities'},
                Prospects: {'LBL_MODULE_NAME': 'Prospects'},
                Reports: {'LBL_MODULE_NAME': 'Reports'},
                Tasks: {'LBL_MODULE_NAME': 'Tasks'}
            }
        });
    });
    
    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
        app.user.clear();
        modStrings.restore();
    });

    it("should set current module", function() {
        view.setModuleInfo();
        expect(view.module).toEqual('Cases');
    });

    it("should set the current module list", function() {
        view.setModuleInfo();
        expect(_.values(view.module_list)).toEqual(_.toArray(_.intersection(app.config.displayModules, fixtures.metadata.module_list)));
    });

    it("should properly set the create task list dropdown", function() {
        var hasAccessStub = sinon.stub(SUGAR.App.acl,"hasAccess",function() {
                return true;
            });

        // setCreateTasksList is our system under test
        view.setModuleInfo();
        view.setCreateTasksList();

        expect(view.createListLabels.length).toEqual(_.values(view.module_list).length);
        expect(hasAccessStub).toHaveBeenCalled();
        hasAccessStub.restore();
    });
});
