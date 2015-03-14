//FILE SUGARCRM flav=ent ONLY
describe("PortalCreateView", function() {
    var app,
        moduleName = 'Contacts',
        viewName = 'create',
        sinonSandbox, view, context,
        drawer;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('portal', 'view', viewName);
        SugarTest.testMetadata.addViewDefinition(viewName, {
            "template": "record",
            "buttons": [
                {
                    "name": "cancel_button",
                    "type": "button",
                    "label": "LBL_CANCEL_BUTTON_LABEL",
                    "css_class": "btn-invisible btn-link"
                },
                {
                    "name": "restore_button",
                    "type": "button",
                    "label": "LBL_RESTORE",
                    "css_class": "hide btn-invisible btn-link",
                    "showOn": "select"
                },
                {
                    "type": "actiondropdown",
                    "name": "main_dropdown",
                    "primary": true,
                    "buttons": [
                        {
                            "type": "rowaction",
                            "name": "save_button",
                            "label": "LBL_SAVE_BUTTON_LABEL"
                        },
                        {
                            "type": "rowaction",
                            "name": "save_create_button",
                            "label": "LBL_SAVE_AND_CREATE_ANOTHER",
                            "showOn": "create"
                        }
                    ]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        app.data.declareModels();

        sinonSandbox = sinon.sandbox.create();

        drawer = app.drawer;
        app.drawer = {
            close: function() {
            }
        };

        context = app.context.getContext();
        context.set({
            module: moduleName,
            create: true
        });
        context.prepare();

        view = SugarTest.createView("portal", moduleName, viewName, null, context);
        sinonSandbox.stub(view, 'addToLayoutComponents');
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.view.reset();
        sinonSandbox.restore();
        app.drawer = drawer;
    });

    describe('renderDupeCheckList', function() {
        it('should not set dupelisttype to dupecheck-list-edit', function() {
            view.renderDupeCheckList();
            expect(view.context.get('dupelisttype')).toBeUndefined();
        });
    });
});
