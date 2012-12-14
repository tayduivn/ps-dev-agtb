describe("Drawer Layout", function() {
    var moduleName = 'Contacts',
        layoutName = 'drawer',
        viewName = 'create',
        sinonSandbox, layout, context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'edit');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'layout', 'modal');
        SugarTest.testMetadata.addViewDefinition(viewName, {
            "type":"record",
            "buttons":[
                {
                    "name":"cancel_button",
                    "type":"button",
                    "label":"LBL_CANCEL_BUTTON_LABEL",
                    "css_class":"btn-invisible btn-link"
                }, {
                    "name":"restore_button",
                    "type":"button",
                    "label":"LBL_RESTORE",
                    "css_class":"hide btn-invisible btn-link"
                }, {
                    "name":"save_create_button",
                    "type":"button",
                    "label":"LBL_SAVE_AND_CREATE_ANOTHER",
                    "css_class":"hide btn-invisible btn-link"
                }, {
                    "name":"save_view_button",
                    "type":"button",
                    "label":"LBL_SAVE_AND_VIEW",
                    "css_class":"hide btn-invisible btn-link"
                }, {
                    "name":"save_button",
                    "type":"button",
                    "label":"LBL_SAVE_BUTTON_LABEL",
                    "css_class":"disabled"
                }, {
                    "name":"sidebar_toggle",
                    "type":"sidebartoggle"
                }
            ],
            "panels":[
                {
                    "name":"panel_header",
                    "placeholders":true,
                    "header":true,
                    "labels":false,
                    "fields":[
                        {
                            "name":"first_name",
                            "label":"",
                            "placeholder":"LBL_NAME"
                        },
                        {
                            "name":"last_name",
                            "label":"",
                            "placeholder":"LBL_NAME"
                        }
                    ]
                }, {
                    "name":"panel_body",
                    "columns":2,
                    "labels":false,
                    "labelsOnTop":true,
                    "placeholders":true,
                    "fields":[
                        "phone_work",
                        "email1",
                        "phone_office",
                        "full_name"
                    ]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        sinonSandbox = sinon.sandbox.create();

        context = SugarTest.app.context.getContext();
        context.set({
            module: moduleName,
            create: true
        });
        context.prepare();

        layout = SugarTest.createLayout('base', moduleName, layoutName, {
            "type": "drawer",
            "components": [
                {"view":"create"}
            ]
        }, context);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinonSandbox.restore();
    });

    describe('Initialization', function() {
        it("Should not initialize any components", function() {
            expect(layout._components.length).toBe(0);
        });
    });

    describe('Show', function() {
        it("Should render create view", function() {
            layout.render();
            layout.show({});

            expect(layout._components.length).toBe(1);
            expect(layout._components[0].name).toBe('create');
        });

        it("Should call _showDrawer", function() {
            var showDrawerSpy = sinonSandbox.spy(layout, '_showDrawer');

            layout.render();
            layout.show({});

            expect(showDrawerSpy.calledOnce).toBe(true);
        });

        it("Should show the drawer", function() {
            layout.render();
            layout.show({});

            expect(layout.$el.css('display')).not.toBe('none');
        });
    });

    describe('Hide', function() {
        it("Should call _showDrawer", function() {
            var hideDrawerSpy = sinonSandbox.spy(layout, '_hideDrawer');

            layout.render();
            layout.show({});
            layout.hide();

            expect(hideDrawerSpy.calledOnce).toBe(true);
        });

        it("Should hide the drawer", function() {
            layout.render();
            layout.show({});
            layout.hide();

            expect(layout.$el.css('display')).toBe('none');
        });
    });
});