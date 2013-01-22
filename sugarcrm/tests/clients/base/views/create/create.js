describe("Create View", function() {
    var moduleName = 'Contacts',
        viewName = 'create',
        sinonSandbox, view, context;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'edit');
        SugarTest.loadComponent('base', 'view', 'editable');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.addViewDefinition('record', {
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
                        "full_name"
                    ]
                }
            ]
        }, moduleName);
        SugarTest.loadComponent('base', 'view', viewName);
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
                    "css_class":"hide btn-invisible btn-link",
                    "showOn" : "edit"
                }, {
                    "name":"save_create_button",
                    "type":"button",
                    "label":"LBL_SAVE_AND_CREATE_ANOTHER",
                    "css_class":"hide btn-invisible btn-link",
                    "showOn" : "save"
                }, {
                    "name":"save_view_button",
                    "type":"button",
                    "label":"LBL_SAVE_AND_VIEW",
                    "css_class":"hide btn-invisible btn-link",
                    "showOn" : "save"
                }, {
                    "name":"save_button",
                    "type":"button",
                    "label":"LBL_SAVE_BUTTON_LABEL",
                    "css_class":"disabled"
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

        view = SugarTest.createView("base", moduleName, viewName, null, context);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        sinonSandbox.restore();
    });

    describe('Render', function() {
        it("Should render 5 buttons and 5 fields", function() {
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

            expect(fields).toBe(5);
            expect(buttons).toBe(5);
        });
    });

    describe('Buttons', function() {
        it("Should disable the save button when the form is empty", function() {
            view.render();
            expect(view.buttons[view.saveButtonName].isDisabled()).toBe(true);
        });

        it("Should enable the save button when the model is valid", function() {
            view.render();
            view.model.set({
                first_name: 'foo',
                last_name: 'bar'
            });

            expect(view.buttons[view.saveButtonName].isDisabled()).toBe(false);
        });
    });
});