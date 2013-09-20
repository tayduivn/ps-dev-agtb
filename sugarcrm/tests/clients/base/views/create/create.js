describe("Create View", function() {
    var app,
        moduleName = 'Contacts',
        viewName = 'create',
        sinonSandbox, view, context,
        drawer;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('rowaction', 'field', 'base', 'detail');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'actiondropdown');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.addViewDefinition('record', {
            "panels":[
                {
                    "name":"panel_header",
                    "columns": 2,
                    "labelsOnTop": true,
                    "placeholders":true,
                    "header":true,
                    "fields":[
                        {
                            "name":"first_name",
                            "label":"",
                            "placeholder":"LBL_NAME",
                            "span": 6,
                            "labelSpan": 6
                        },
                        {
                            "name":"last_name",
                            "label":"",
                            "placeholder":"LBL_NAME",
                            "span": 6,
                            "labelSpan": 6
                        }
                    ]
                }, {
                    "name":"panel_body",
                    "columns": 2,
                    "labelsOnTop":true,
                    "placeholders":true,
                    "fields":[
                        {
                            name: "phone_work",
                            type: "phone",
                            label: "phone_work",
                            span: 6,
                            labelSpan: 6
                        },
                        {
                            name: "email1",
                            type: "email",
                            label: "email1",
                            span: 6,
                            labelSpan: 6
                        },
                        {
                            name: "full_name",
                            type: "name",
                            label: "full_name",
                            span: 6,
                            labelSpan: 6
                        }
                    ]
                }
            ]
        }, moduleName);
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.addViewDefinition(viewName, {
            "template":"record",
            "buttons": [
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
                    "showOn" : "select"
                }, {
                    "type":"actiondropdown",
                    "name":"main_dropdown",
                    "primary":true,
                    "buttons": [
                        {
                            "type":"rowaction",
                            "name":"save_button",
                            "label":"LBL_SAVE_BUTTON_LABEL"
                        }, {
                            "type":"rowaction",
                            "name":"save_view_button",
                            "label":"LBL_SAVE_AND_VIEW",
                            "showOn":"create"
                        }, {
                            "type":"rowaction",
                            "name":"save_create_button",
                            "label":"LBL_SAVE_AND_CREATE_ANOTHER",
                            "showOn":"create"
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
            close: function(){}
        };

        context = app.context.getContext();
        context.set({
            module: moduleName,
            create: true
        });
        context.prepare();

        view = SugarTest.createView("base", moduleName, viewName, null, context);
        view.enableDuplicateCheck = true;
        sinonSandbox.stub(view, 'addToLayoutComponents');
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.view.reset();
        sinonSandbox.restore();
        app.drawer = drawer;
    });

    describe('Initialize', function() {
        var current_user_id = '1234567890';
        var current_user_name = 'Johnny Appleseed';
        var save_user_id;
        var save_user_name;
        var fields;  // Must be Set by each Test
        beforeEach(function() {
            save_user_id = app.user.id;
            save_user_name = app.user.attributes.full_name;

            app.user.id = current_user_id;
            app.user.attributes.full_name = current_user_name;

            sinonSandbox.stub(app.metadata, 'getModule', function() {
                var meta = {
                    "fields": fields,
                    favoritesEnabled: true,
                    views: [],
                    layouts: [],
                    _hash: "bc6fc50d9d0d3064f5d522d9e15968fa"
                };
                return meta;
            });
        });

        afterEach(function() {
            app.user.id = save_user_id;
            app.user.attributes.full_name = save_user_name;
        });

        it("Should create a record view having a Assigned-To field initialized with the Current Signed In User", function() {
            fields = [
                {
                    "group": "assigned_user_name",
                    "id_name": "assigned_user_id",
                    "module": "Users",
                    "name": "assigned_user_id",
                    "rname": "user_name",
                    "table": "users",
                    "type": "relate",
                    "vname": "LBL_ASSIGNED_TO_ID"
                }
            ];

            var view = SugarTest.createView("base", moduleName, viewName, null, context);

            var user_id   = view.model.get('assigned_user_id');
            var full_name = view.model.get('assigned_user_name');

            expect(user_id).toEqual(current_user_id);
            expect(full_name).toEqual(current_user_name);

            expect(view.model._defaults.assigned_user_id).toBe(user_id);
            expect(view.model._defaults.assigned_user_name).toBe(full_name);
        });

        it("Should create a record view having a Assigned-To field initialized with the Assigned-to user of the original record if performing a copy.", function() {
            fields = [
                {
                    "group": "assigned_user_name",
                    "id_name": "assigned_user_id",
                    "module": "Users",
                    "name": "assigned_user_id",
                    "rname": "user_name",
                    "table": "users",
                    "type": "relate",
                    "vname": "LBL_ASSIGNED_TO_ID"
                }
            ];

            var copied_user_id = '98765',
                copied_user_name = 'John Doe',
                bean;
            var context = app.context.getContext();

            bean = app.data.createBean(moduleName, {
                "assigned_user_id" : copied_user_id,
                "assigned_user_name": copied_user_name
            });
            context.set({
                module: moduleName,
                isDuplicate: true,
                model: bean,
                create: true
            });
            context.prepare();

            var view = SugarTest.createView("base", moduleName, viewName, null, context);

            var user_id   = view.model.get('assigned_user_id');
            var full_name = view.model.get('assigned_user_name');

            expect(user_id).toEqual(copied_user_id);
            expect(full_name).toEqual(copied_user_name);

            expect(view.model._defaults.assigned_user_id).toBe(current_user_id);
            expect(view.model._defaults.assigned_user_name).toBe(current_user_name);
        });
        it("Should create a record view having a Assigned-To field - 'id_name' is assigned_user_id", function() {
            fields = [
                {
                    "group": "assigned_user_name",
                    "id_name": "assigned_user_id",
                    "module": "Users",
                    "name": "some_field",
                    "rname": "user_name",
                    "table": "users",
                    "type": "relate",
                    "vname": "LBL_ASSIGNED_TO_ID"
                }
            ];

            var view = SugarTest.createView("base", moduleName, viewName, null, context);

            var user_id   = view.model.get('assigned_user_id');
            var full_name = view.model.get('assigned_user_name');

            expect(user_id).toEqual(current_user_id);
            expect(full_name).toEqual(current_user_name);
        });

        it("Should create a record view having a Assigned-To field - 'name' is assigned_user_id", function() {
            fields = [
                {
                    "group": "assigned_user_name",
                    "id_name": "some_field",
                    "module": "Users",
                    "name": "assigned_user_id",
                    "rname": "user_name",
                    "table": "users",
                    "type": "relate",
                    "vname": "LBL_ASSIGNED_TO_ID"
                }
            ];

            var view = SugarTest.createView("base", moduleName, viewName, null, context);

            var user_id   = view.model.get('assigned_user_id');
            var full_name = view.model.get('assigned_user_name');

            expect(user_id).toEqual(current_user_id);
            expect(full_name).toEqual(current_user_name);
        });


        it("Should Not create a record view having an initialized Assigned-To field - Type is not Relate", function() {
            fields = [
                {
                    "group": "assigned_user_name",
                    "id_name": "assigned_user_id",
                    "module": "Users",
                    "name": "assigned_user_id",
                    "rname": "user_name",
                    "table": "users",
                    "type": "link",
                    "vname": "LBL_ASSIGNED_TO_ID"
                }
            ];

            var view = SugarTest.createView("base", moduleName, viewName, null, context);

            var user_id   = view.model.get('assigned_user_id');
            var full_name = view.model.get('assigned_user_name');

            expect(user_id).not.toEqual(current_user_id);
            expect(full_name).not.toEqual(current_user_name);
        });

        it("Should Not create a record view having an initialized Assigned-To field - neither id_name and name equal 'assigned_user_id' ", function() {
            fields = [
                {
                    "group": "assigned_user_name",
                    "id_name": "some_field",
                    "module": "Users",
                    "name": "some_field",
                    "rname": "user_name",
                    "table": "users",
                    "type": "relate",
                    "vname": "LBL_ASSIGNED_TO_ID"
                }
            ];

            var view = SugarTest.createView("base", moduleName, viewName, null, context);

            var user_id   = view.model.get('assigned_user_id');
            var full_name = view.model.get('assigned_user_name');

            expect(user_id).not.toEqual(current_user_id);
            expect(full_name).not.toEqual(current_user_name);
        });

    });

    describe('Render', function() {
        it("Should render 6 buttons and 5 fields", function() {
            sinonSandbox.stub(view, "_buildGridsFromPanelsMetadata", function(panels) {
                // The panel grid contains references to the actual fields found in panel.fields, so the fields must
                // be modified to include the field attributes that would be calculated during a normal render
                // operation and then added to the grid in the correct row and column.
                panels[0].grid = [
                    [panels[0].fields[0], panels[0].fields[1]]
                ];
                panels[1].grid = [
                    [panels[1].fields[0], panels[1].fields[1]],
                    [panels[1].fields[2]]
                ];
            });
            var fields = 0;

            view.render();

            _.each(view.fields, function(field) {
                if (!view.buttons[field.name]) {
                    fields++;
                }
            });

            expect(fields).toBe(5);
            expect(_.values(view.buttons).length).toBe(6);
        });
    });

    describe('Buttons', function() {
        it("Should hide the restore button when the form is empty", function() {
            view.render();

            expect(view.buttons[view.saveButtonName].isHidden).toBe(false);
            expect(view.buttons[view.cancelButtonName].isHidden).toBe(false);
            expect(view.buttons[view.saveAndCreateButtonName].isHidden).toBe(false);
            expect(view.buttons[view.saveAndViewButtonName].isHidden).toBe(false);
            expect(view.buttons[view.restoreButtonName].isHidden).toBe(true);
        });

        it("Should hide all buttons except save and cancel when duplicates are found.", function() {
            var flag = false,
                checkForDuplicateStub = sinon.stub(view, 'checkForDuplicate', function(success, error) {
                    var data = {
                        "id":"f360b873-b11c-4f25-0a3e-50cb8e7ad0c2",
                        "first_name":"Foo",
                        "last_name":"Bar",
                        "phone_work":"1234567890",
                        "email1":"foobar@test.com",
                        "full_name":"Mr Foo Bar"
                    },
                        model = app.data.createBean(moduleName, data),
                        collection = app.data.createBeanCollection(moduleName, model);

                    checkForDuplicateStub.restore();
                    success(collection);
                }),
                handleDuplicateFoundStub = sinon.stub(view, 'handleDuplicateFound', function(collection) {
                    handleDuplicateFoundStub.restore();
                    view.handleDuplicateFound(collection);
                    flag = true;
                });

            runs(function() {
                view.render();
                view.model.set({
                    first_name: 'First',
                    last_name: 'Last'
                });
                view.buttons[view.saveButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'handleDuplicateFound should have been called but timeout expired', 1000);

            runs(function() {
                expect(view.buttons[view.saveButtonName].isHidden).toBe(false);
                expect(view.buttons[view.saveButtonName].getFieldElement().text()).toBe('LBL_IGNORE_DUPLICATE_AND_SAVE');
                expect(view.buttons[view.cancelButtonName].isHidden).toBe(false);
                expect(view.buttons[view.saveAndCreateButtonName].isHidden).toBe(true);
                expect(view.buttons[view.saveAndViewButtonName].isHidden).toBe(true);
                expect(view.buttons[view.restoreButtonName].isHidden).toBe(true);
            });
        });

        it("Should show restore button, along with save and cancel, when a duplicate is selected to edit.", function() {
            var data = {
                "id":"f360b873-b11c-4f25-0a3e-50cb8e7ad0c2",
                "first_name":"Foo",
                "last_name":"Bar",
                "phone_work":"1234567890",
                "email1":"foobar@test.com",
                "full_name":"Mr Foo Bar"
            };

            view.render();
            view.model.set({
                first_name: 'First',
                last_name: 'Last'
            });
            view.context.trigger('list:dupecheck-list-select-edit:fire', app.data.createBean(moduleName, data));

            expect(view.buttons[view.saveButtonName].isHidden).toBe(false);
            expect(view.buttons[view.saveButtonName].getFieldElement().text()).toBe('LBL_SAVE_BUTTON_LABEL');
            expect(view.buttons[view.cancelButtonName].isHidden).toBe(false);
            expect(view.buttons[view.saveAndCreateButtonName].isHidden).toBe(true);
            expect(view.buttons[view.saveAndViewButtonName].isHidden).toBe(true);
            expect(view.buttons[view.restoreButtonName].isHidden).toBe(false);
        });

        it("Should set the model to selected duplicate values plus any create data for empty fields on selected duplicate", function() {
            var title = "CEO",
                selectedDuplicateAttributes = {
                    "id":"f360b873-b11c-4f25-0a3e-50cb8e7ad0c2",
                    "first_name":"Foo",
                    "last_name":"Bar",
                    "phone_work":"1234567890",
                    "email1":"foobar@test.com",
                    "full_name":"Mr Foo Bar",
                    "age":42,
                    "is_cool":false
                },
                expectedAttributes = _.extend({title:title}, selectedDuplicateAttributes);

            view.render();
            view.model.set({
                first_name: 'First',
                last_name: 'Last',
                title: title
            });
            view.context.trigger('list:dupecheck-list-select-edit:fire', app.data.createBean(moduleName, selectedDuplicateAttributes));
            expect(view.model.attributes).toEqual(expectedAttributes);
        });

        it("Should reset to the original form values when restore is clicked.", function() {
            var data = {
                "id":"f360b873-b11c-4f25-0a3e-50cb8e7ad0c2",
                "first_name":"Foo",
                "last_name":"Bar",
                "phone_work":"1234567890",
                "email1":"foobar@test.com",
                "full_name":"Mr Foo Bar"
            };

            view.render();
            view.model.set({
                first_name: 'First',
                last_name: 'Last'
            });
            view.context.trigger('list:dupecheck-list-select-edit:fire', app.data.createBean(moduleName, data));

            expect(view.model.get('first_name')).toBe('Foo');
            expect(view.model.get('last_name')).toBe('Bar');

            var render = sinonSandbox.stub(view, 'render');
            view.buttons[view.restoreButtonName].getFieldElement().click();

            expect(view.model.get('first_name')).toBe('First');
            expect(view.model.get('last_name')).toBe('Last');
            expect(view.model.isCopy()).toBe(true);
        });
    });

    describe('SaveModel', function() {
        it("Should retrieve custom save options and params options should be appended to request url", function() {
            var moduleName = "Contacts",
                bean,
                dm = app.data,
                ajaxSpy = sinon.spy($, 'ajax');

            bean = dm.createBean(moduleName, { id: "1234" });

            sinonSandbox.stub(app.file, 'checkFileFieldsAndProcessUpload', function(success) {
                success();
            });
            var getCustomSaveOptionsStub = sinonSandbox.stub(view, 'getCustomSaveOptions', function() {
                return {'params': {'param1': true, 'param2': false}};
            });

            view.render();
            view.model = bean;

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith("GET", /.*rest\/v10\/Contacts\/1234.*/,
                [200, { "Content-Type": "application/json"}, JSON.stringify({})]);

            var success = function(){};
            var failure = function(){};

            view.saveModel(success, failure);

            SugarTest.server.respond();
            expect(getCustomSaveOptionsStub.calledOnce).toBe(true);
            getCustomSaveOptionsStub.restore();

            expect(ajaxSpy.getCall(0).args[0].url).toContain('?param1=true&param2=false&viewed=1');
            ajaxSpy.restore();
        });

        it("Should not append options to url if custom options method not overridden", function() {
            var moduleName = "Contacts",
                bean,
                dm = app.data,
                ajaxSpy = sinonSandbox.spy($, 'ajax');

            bean = dm.createBean(moduleName, { id: "1234" });

            var checkFileStub = sinon.stub(app.file, 'checkFileFieldsAndProcessUpload', function(success) {
                    success();
                }),
                getCustomSaveOptionsStub = sinon.stub(view, 'getCustomSaveOptions');

            view.render();
            view.model = bean;

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith("GET", /.*rest\/v10\/Contacts\/1234.*/,
                [200, { "Content-Type": "application/json"}, JSON.stringify({})]);

            var success = function(){};
            var failure = function(){};

            view.saveModel(success, failure);

            SugarTest.server.respond();
            expect(getCustomSaveOptionsStub.calledOnce).toBe(true);
            checkFileStub.restore();
            getCustomSaveOptionsStub.restore();

            expect(ajaxSpy.getCall(0).args[0].url).toContain('?viewed=1');
        });

        it("Should build correct success message if model is returned from the API", function() {
            var moduleName = 'Contacts',
                labelSpy = sinonSandbox.spy(app.lang, 'get'),
                model = {
                    attributes: {
                        id: '123',
                        name: 'foo'
                    }
                },
                messageContext;

            view.moduleSingular = 'Contact';
            view.buildSuccessMessage(model);
            expect(labelSpy.calledWith('LBL_RECORD_SAVED_SUCCESS', moduleName)).toBeTruthy();
            messageContext = labelSpy.lastCall.args[2];
            expect(messageContext.id).toEqual(model.attributes.id);
            expect(messageContext.module).toEqual(moduleName);
            expect(messageContext.moduleSingularLower).toEqual(view.moduleSingular.toLowerCase());
            expect(messageContext.name).toEqual(model.attributes.name);
        });

        it("Should build generic message if model is not returned from the API", function() {
            var moduleName = 'Contacts',
                labelSpy = sinonSandbox.spy(app.lang, 'get');

            view.buildSuccessMessage();
            expect(labelSpy.calledWith('LBL_RECORD_SAVED', moduleName)).toBeTruthy();
        });
    });

    describe('Save', function() {

        beforeEach(function() {
            SugarTest.clock.restore();
        });

        it("Should save data when save button is clicked, form data are valid, and no duplicates are found.", function() {
            var flag = false,
                validateStub = sinonSandbox.stub(view, 'validateModelWaterfall', function(callback) {
                    callback(null);
                }),
                checkForDuplicateStub = sinonSandbox.stub(view, 'checkForDuplicate', function(success, error) {
                    success(app.data.createBeanCollection(moduleName));
                }),
                saveModelStub = sinonSandbox.stub(view, 'saveModel', function() {
                    flag = true;
                });

            view.render();

            runs(function() {
                view.buttons[view.saveButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'Save should have been called but timeout expired', 1000);

            runs(function() {
                expect(validateStub.calledOnce).toBe(true);
                expect(checkForDuplicateStub.calledOnce).toBe(true);
                expect(saveModelStub.calledOnce).toBe(true);
            });
        });

        it("Should close drawer once save is complete", function() {
            var flag = false,
                validateStub = sinonSandbox.stub(view, 'validateModelWaterfall', function(callback) {
                    callback(null);
                }),
                checkForDuplicateStub = sinonSandbox.stub(view, 'checkForDuplicate', function(success, error) {
                    success(app.data.createBeanCollection(moduleName));
                }),
                saveModelStub = sinonSandbox.stub(view, 'saveModel', function(success) {
                    success();
                }),
                drawerCloseStub = sinonSandbox.stub(app.drawer, 'close', function() {
                    flag = true;
                    return;
                });

            view.render();

            runs(function() {
                view.buttons[view.saveButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'close should have been called but timeout expired', 1000);

            runs(function() {
                expect(drawerCloseStub.calledOnce).toBe(true);
            });
        });

        it("Should not save data when save button is clicked but form data are invalid", function() {
            var flag = false,
                validateStub = sinonSandbox.stub(view, 'validateModelWaterfall', function(callback) {
                    flag = true;
                    callback(true);
                }),
                checkForDuplicateStub = sinonSandbox.stub(view, 'checkForDuplicate', function(success, error) {
                    flag = true;
                    success(app.data.createBeanCollection(moduleName));
                }),
                saveModelStub = sinonSandbox.stub(view, 'saveModel', function() {
                    return;
                });

            view.render();

            runs(function() {
                view.buttons[view.saveButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'validateModelWaterfall should have been called but timeout expired', 1000);

            runs(function() {
                expect(validateStub.calledOnce).toBeTruthy();
                expect(checkForDuplicateStub.called).toBeFalsy();
                expect(saveModelStub.called).toBeFalsy();
            });
        });

        it("Should not save data when save button is clicked but duplicates are found", function() {
            var flag = false,
                validateStub = sinonSandbox.stub(view, 'validateModelWaterfall', function(callback) {
                    callback(null);
                }),
                checkForDuplicateStub = sinonSandbox.stub(view, 'checkForDuplicate', function(success, error) {
                    flag = true;

                    var data = {
                        "id":"f360b873-b11c-4f25-0a3e-50cb8e7ad0c2",
                        "first_name":"Foo",
                        "last_name":"Bar",
                        "phone_work":"1234567890",
                        "email1":"foobar@test.com",
                        "full_name":"Mr Foo Bar"
                    },
                        model = app.data.createBean(moduleName, data),
                        collection = app.data.createBeanCollection(moduleName, model);

                    success(collection);
                }),
                saveModelStub = sinonSandbox.stub(view, 'saveModel', function() {
                    return;
                });

            view.render();

            runs(function() {
                view.buttons[view.saveButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'checkForDuplicate should have been called but timeout expired', 2000);

            runs(function() {
                expect(validateStub.calledOnce).toBe(true);
                expect(checkForDuplicateStub.calledOnce).toBe(true);
                expect(saveModelStub.called).toBe(false);
            });
        });
    });

    describe('Ignore Duplicate and Save', function() {
        it("Should save data and not run duplicate check when ignore duplicate and save button is clicked.", function() {
            var flag = false,
                validateStub = sinonSandbox.stub(view, 'validateModelWaterfall', function(callback) {
                    callback(null);
                }),
                checkForDuplicateStub = sinonSandbox.stub(view, 'checkForDuplicate', function(success, error) {
                    flag = true;

                    var data = {
                            "id":"f360b873-b11c-4f25-0a3e-50cb8e7ad0c2",
                            "first_name":"Foo",
                            "last_name":"Bar",
                            "phone_work":"1234567890",
                            "email1":"foobar@test.com",
                            "full_name":"Mr Foo Bar"
                        },
                        model = app.data.createBean(moduleName, data),
                        collection = app.data.createBeanCollection(moduleName, model);

                    success(collection);
                }),
                saveModelStub = sinonSandbox.stub(view, 'saveModel', function(success) {
                    success();
                }),
                drawerCloseStub = sinonSandbox.stub(app.drawer, 'close', function() {
                    flag = true;
                    return;
                });

            view.render();

            runs(function() {
                expect(view.skipDupeCheck()).toBe(false);
                view.buttons[view.saveButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'checkForDuplicate should have been called but timeout expired', 1000);

            runs(function() {
                flag = false;
                expect(view.skipDupeCheck()).toBe(true);
                view.buttons[view.saveButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'close should have been called but timeout expired', 1000);

            runs(function() {
                expect(validateStub.calledTwice).toBe(true);
                expect(checkForDuplicateStub.calledOnce).toBe(true);
                expect(saveModelStub.calledOnce).toBe(true);
                expect(drawerCloseStub.calledOnce).toBe(true);
            });
        });
    });

    describe('Save and Create Another', function() {
        it("Should save, clear out the form, but not close the drawer.", function() {
            var flag = false,
                validateStub = sinonSandbox.stub(view, 'validateModelWaterfall', function(callback) {
                    callback(null);
                }),

                checkForDuplicateStub = sinonSandbox.stub(view, 'checkForDuplicate', function(success, error) {
                    success(app.data.createBeanCollection(moduleName));
                }),
                saveModelStub = sinonSandbox.stub(view, 'saveModel', function(success) {
                    success();
                }),
                drawerCloseStub = sinonSandbox.stub(app.drawer, 'close', function() {
                    return;
                }),
                clearStub = sinonSandbox.stub(view.model, 'clear', function() {
                    flag = true;
                });

            view.render();
            view.buttons['main_dropdown'].renderDropdown();
            view.model.set({
                first_name: 'First',
                last_name: 'Last'
            });

            runs(function() {
                view.buttons[view.saveAndCreateButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'clear should have been called but timeout expired', 1000);

            runs(function() {
                expect(saveModelStub.calledOnce).toBe(true);
                expect(drawerCloseStub.called).toBe(false);
                expect(clearStub.calledOnce).toBe(true);
            });
        });
    });

    describe('Save and View', function() {
        it("Should save, close the modal, and navigate to the detail view.", function() {
            var flag = false,
                validateStub = sinonSandbox.stub(view, 'validateModelWaterfall', function(callback) {
                    callback(null);
                }),
                checkForDuplicateStub = sinonSandbox.stub(view, 'checkForDuplicate', function(success, error) {
                    success(app.data.createBeanCollection(moduleName));
                }),
                saveModelStub = sinonSandbox.stub(view, 'saveModel', function(success) {
                    success();
                }),
                navigateStub = sinonSandbox.stub(app, 'navigate', function() {
                    flag = true;
                });

            view.render();
            view.buttons['main_dropdown'].renderDropdown();

            runs(function() {
                view.buttons[view.saveAndViewButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'navigate should have been called but timeout expired', 1000);

            runs(function() {
                expect(saveModelStub.calledOnce).toBe(true);
                expect(navigateStub.calledOnce).toBe(true);
            });
        });
    });

    describe('Disable Duplicate Check', function() {
        it("Should save data and not run duplicate check when duplicate check is disabled", function() {
            var flag = false,
                validateStub = sinonSandbox.stub(view, 'validateModelWaterfall', function(callback) {
                    callback(null);
                }),
                checkForDuplicateStub = sinonSandbox.stub(view, 'checkForDuplicate'),
                saveModelStub = sinonSandbox.stub(view, 'saveModel', function(success) {
                    success();
                }),
                drawerCloseStub = sinonSandbox.stub(app.drawer, 'close', function() {
                    flag = true;
                });

            view.enableDuplicateCheck = false;
            view.render();

            runs(function() {
                view.buttons[view.saveButtonName].getFieldElement().click();
            });

            waitsFor(function() {
                return flag;
            }, 'Drawer should have been closed but timeout expired', 1000);

            runs(function() {
                expect(validateStub.calledOnce).toBe(true);
                expect(checkForDuplicateStub.called).toBe(false);
                expect(saveModelStub.calledOnce).toBe(true);
                expect(drawerCloseStub.calledOnce).toBe(true);
            });
        });
    });

    describe('renderDupeCheckList', function() {
        it('should set dupelisttype to dupecheck-list-edit', function() {
            view.renderDupeCheckList();
            expect(view.context.get('dupelisttype')).toEqual('dupecheck-list-edit');
        });

        it('should render dupecheck layout only if dupecheckList not already defined', function() {
            var createLayoutSpy = sinonSandbox.spy(app.view, 'createLayout');
            view.renderDupeCheckList();
            expect(createLayoutSpy).toHaveBeenCalledOnce();
            view.renderDupeCheckList();
            expect(createLayoutSpy).not.toHaveBeenCalledTwice();
        });
    });
});
