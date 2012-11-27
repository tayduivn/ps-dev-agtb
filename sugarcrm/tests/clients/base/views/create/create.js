describe("Create View", function() {
    var moduleName = 'Contacts',
        viewName = 'create',
        sinonSandbox, view, context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadViewHandlebarsTemplate('base', 'record');
        SugarTest.loadFieldHandlebarsTemplate('base', 'button', 'edit');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.addModuleViewDefinition(moduleName, viewName, {
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
        });
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        sinonSandbox = sinon.sandbox.create();

        context = SugarTest.app.context.getContext();
        context.set({
            module: moduleName,
            create: true
        });
        context.prepare();

        view = SugarTest.createView("base", moduleName, viewName, null, context);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinonSandbox.restore();
    });

    describe('Render', function() {
        it("Should render 5 buttons and 6 fields", function() {
            var fields = 0,
                buttons = 0;

            view.render();

            _.each(view.fields, function(field) {
                if (field.type === 'button') {
                    buttons++;
                } else {
                    fields++;
                }
            });

            expect(fields).toBe(6);
            expect(buttons).toBe(5);
        });
    });

    describe('Buttons', function() {
        it("Should disable the save button when the form is empty", function() {
            view.render();

            expect(view.$('[name=save_button]').hasClass('disabled')).toBe(true);
        });

        it("Should enable the save button when the model is valid", function() {
            view.render();
            view.model.set({
                first_name: 'foo',
                last_name: 'bar'
            });

            expect(view.$('[name=save_button]').hasClass('disabled')).toBe(false);
        });
    });
});