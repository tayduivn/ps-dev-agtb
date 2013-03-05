describe("ConvertLeadLayout", function() {
    var app, leadModel;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('headerpane', 'view', 'base');
        SugarTest.loadHandlebarsTemplate('convert-panel', 'view', 'base', null, 'Leads');

        SugarTest.loadComponent('base', 'layout', 'convert-main', 'Leads');

        SugarTest.loadComponent('base', 'view', 'convert-panel', 'Leads');
        SugarTest.loadComponent('base', 'view', 'convert-results', 'Leads');
        SugarTest.loadComponent('base', 'view', 'alert');

        SugarTest.addComponent('base', 'layout', 'dupecheck', createMockDupeView());
        SugarTest.addComponent('base', 'view', 'create', createMockRecordView());
        SugarTest.testMetadata.set();


        SugarTest.testMetadata.addViewDefinition('create', {
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
        }, 'Contacts');

        SugarTest.testMetadata.addViewDefinition('create', {
            "panels":[
                {
                    "name":"panel_header",
                    "placeholders":true,
                    "header":true,
                    "labels":false,
                    "fields":[
                            "name",
                            "email"
                    ]
                }, {
                    "name":"panel_body",
                    "columns":2,
                    "labels":false,
                    "labelsOnTop":true,
                    "placeholders":true,
                    "fields":[
                        'account_type',
                        'industry',
                        'annual_revenue'
                    ]
                }
            ]
        }, 'Accounts');

        SugarTest.testMetadata.addViewDefinition('create', {
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
        }, 'Opportunities');

        //Injecting the dupecheck property into the modules
        var modules = app.metadata.getModules();
         _.each(modules, function(module){
            module.dupCheckEnabled = true;
        });

        app.metadata.set(modules);

    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        SugarTest.testMetadata.dispose();
    });

    var initializeLayout = function() {
        var meta = {
            'modules': [
                {
                    'module': 'Contacts',
                    'required': true,
                    'fieldMapping': {
                        'first_name': 'first_name',
                        'last_name': 'last_name'
                    }
                },
                {
                    'module': 'Accounts',
                    'duplicateCheckOnStart': true,
                    'required': true,
                    'fieldMapping': {
                        'name': 'account_name'
                    }
                },
                {
                    'module':'Opportunities',
                    'duplicateCheckOnStart':false,
                    'required':false,
                    'fieldMapping':{
                        'name':'opportunity_name'
                    },
                    "dependentModules":{
                        "Accounts":{
                            "fieldMapping":{
                                "id":"account_id"
                            }
                        },
                        "Contacts":{}
                    }
                }
            ]
        };
        var layout = SugarTest.createLayout('base', 'Leads', 'convert-main', meta, null, true);
        leadModel = new Backbone.Model();
        layout.context.set("leadsModel", leadModel);

        return layout;
    };

    describe('Initialize', function() {
        var layout;

        beforeEach(function() {
            layout = initializeLayout();
        });

        afterEach(function() {
            delete layout;
        });

        it("should have 3 components on the layout", function() {
            expect(layout._components.length).toEqual(3);
        })

        it("components on the layout should be convert-panels with module metadata", function() {
            var i = 0;

            _.each(layout._components, function(component) {
                expect(component.name).toEqual('convert-panel');
                expect(component.meta.module).toEqual(layout.meta.modules[i++].module);
            });
        });
    });

    describe('Render', function() {
        var layout;

        beforeEach(function() {
            layout = initializeLayout();
        });

        afterEach(function() {
            mockDupesToFind = 2;
            delete layout;
        });

        it("components on the layout each have a duplicate view and create/record view", function() {
            layout.render();

            expect(layout._components.length).toEqual(9);
            var panels = _.first(layout._components, 3);
            _.each(panels, function(component) {
                expect(component.duplicateView).toBeDefined();
                expect(component.recordView).toBeDefined();
            });
        });

        it("first component is active, other two are not", function() {
            layout.render();
            expect(layout._components[0].$('.header').hasClass('active')).toBeTruthy();
            expect(layout._components[1].$('.header').hasClass('active')).toBeFalsy();
            expect(layout._components[2].$('.header').hasClass('active')).toBeFalsy();
        });

        it("first two components enabled, last is not because of dependency", function() {
            layout.render();
            expect(layout._components[0].$('.header').hasClass('enabled')).toBeTruthy();
            expect(layout._components[1].$('.header').hasClass('enabled')).toBeTruthy();

            expect(layout._components[2].$('.header').hasClass('enabled')).toBeFalsy();
            expect(layout._components[2].$('.header').hasClass('disabled')).toBeTruthy();
        });

        it("finish button is disabled", function() {
            var finishButtonEnabled = true;
            var stub = sinon.stub(layout, 'toggleFinishButton', function(enable) {
                finishButtonEnabled = enable;
            });
            layout.render();
            expect(finishButtonEnabled).toBeFalsy();
            stub.restore();
        });

        it("create views are prepopulated with lead data", function() {
            var last_name = 'mylastname',
                account_name = 'myaccname',
                opportunity_name = 'myoppname'

            leadModel.set('last_name', last_name);
            leadModel.set('account_name', account_name);
            leadModel.set('opportunity_name', opportunity_name);
            layout.render();
            expect(layout._components[0].recordView.model.get("last_name")).toEqual(last_name);
            expect(layout._components[1].recordView.model.get("name")).toEqual(account_name);
            expect(layout._components[2].recordView.model.get("name")).toEqual(opportunity_name);
        });

        it("correct subviews are active", function() {
            layout.render();

            //Contact should have record view active (dupe check not defined, defaults to false)
            expect(layout._components[0].currentState.activeView).toEqual(layout._components[0].RECORD_VIEW);
            //Account should have duplicate view active (dupe check set to true)
            expect(layout._components[1].currentState.activeView).toEqual(layout._components[1].DUPLICATE_VIEW);
            //Opportunity should have record view active (dupe check set to false)
            expect(layout._components[2].currentState.activeView).toEqual(layout._components[2].RECORD_VIEW);
        });

        it("dupe view is skipped if no dupes found", function() {
            mockDupesToFind = 0;
            layout.render();
            expect(layout._components[1].currentState.activeView).toEqual(layout._components[1].RECORD_VIEW);
        });
    });

    describe('Switching Panels', function() {
        var layout, $contactHeader, $accountHeader, $opportunityHeader;

        beforeEach(function() {
            layout = initializeLayout();
            layout.render();
            $contactHeader = layout._components[0].$('.header');
            $accountHeader = layout._components[1].$('.header');
            $opportunityHeader = layout._components[2].$('.header');
        });

        afterEach(function() {
            mockValidationResult = true;
            delete layout;
        });

        it("clicking on the opportunity panel header does nothing (disabled until first two are complete)", function() {
            expect($opportunityHeader.hasClass('disabled')).toBeTruthy(); //disabled before
            $opportunityHeader.click()
            expect($opportunityHeader.hasClass('disabled')).toBeTruthy(); //disabled after
        });

        it("clicking on the account panel header with success validation on contact panel moves activate status to account panel", function() {
            expect($accountHeader.hasClass('active')).toBeFalsy(); //not active before
            $accountHeader.click()
            expect($contactHeader.hasClass('active')).toBeFalsy(); //not active after
            expect($accountHeader.hasClass('active')).toBeTruthy(); //active after
        });

        it("clicking on the account panel header with validation error on contact panel keeps active status on contact panel", function() {
            mockValidationResult = false;
            expect($accountHeader.hasClass('active')).toBeFalsy(); //not active before
            $accountHeader.click()
            expect($contactHeader.hasClass('active')).toBeTruthy(); //still active after
            expect($accountHeader.hasClass('active')).toBeFalsy(); //not active after
        });

        it("completing contact panel and account panel ready for validation activates opportunity panel", function() {
            $accountHeader.click(); //complete contact panel by navigating to account
            $accountHeader.find('.show-record').click(); //switching to record mode puts panel in dirty state, ready for validation
            expect($opportunityHeader.hasClass('enabled')).toBeTruthy(); //now opportunity is enabled
        });

        it("completing required panels enables finish button", function() {
            var finishButtonEnabled = false;
            var stub = sinon.stub(layout, 'toggleFinishButton', function(enable) {
                finishButtonEnabled = enable;
            });
            $accountHeader.click(); //complete contact panel by navigating to account
            $accountHeader.find('.show-record').click(); //switching to record mode puts panel in dirty state, ready for validation
            $opportunityHeader.click(); //complete account panel by navigating to opportunity
            expect(finishButtonEnabled).toBeTruthy();
            stub.restore();
        });
    });

    describe('Switching SubViews', function() {
        var layout;

        beforeEach(function() {
            layout = initializeLayout();
            layout.render();
        });

        afterEach(function() {
            delete layout;
        });

        it("clicking on ignore duplicates switches to create/record view and back", function() {
            layout._components[1].$('.header').click(); //go to account panel
            expect(layout._components[1].currentState.activeView).toEqual(layout._components[1].DUPLICATE_VIEW);
            layout._components[1].$('.show-record').click();
            expect(layout._components[1].currentState.activeView).toEqual(layout._components[1].RECORD_VIEW);
            layout._components[1].$('.show-duplicate').click();
            expect(layout._components[1].currentState.activeView).toEqual(layout._components[1].DUPLICATE_VIEW);
        });
    });

    describe('Finishing Convert Lead', function() {
        var layout,
            showAlertStub,
            last_name = 'mylastname',
            account_name = 'myaccname',
            opportunity_name = 'myoppname',
            actualConvertModel,
            apiCallStub;

        beforeEach(function() {
            actualConvertModel = {};
            layout = initializeLayout();
            leadModel.set('last_name', last_name);
            leadModel.set('account_name', account_name);
            leadModel.set('opportunity_name', opportunity_name);
            layout.render();
            showAlertStub = sinon.stub(SugarTest.app.alert, 'show', $.noop());

            apiCallStub = sinon.stub(app.api, 'call');
        });

        afterEach(function() {
            apiCallStub.restore();
            showAlertStub.restore();
            delete layout;
        });


        it("clicking on finish after completing all panels bundles up models from each panel and calls the API", function() {
            var expectedConvertModel = '{"modules":{"Contacts":{"last_name":"'+last_name+'"},"Accounts":{"name":"'+account_name+'"},"Opportunities":{"name":"'+opportunity_name+'"}}}';

            layout._components[1].$('.header').click(); //click Account to complete Contact
            layout._components[1].$('.header').find('.show-record').click();
            layout._components[2].$('.header').click(); //click Opportunity to complete Account
            layout.initiateFinish(); //click finish to complete Opportunity


            expect(apiCallStub.lastCall.args[0]).toEqual('create');
            expect(apiCallStub.lastCall.args[1]).toMatch(/.*\/Leads\/convert/);

            actualConvertModel = apiCallStub.lastCall.args[2];

            expect(JSON.stringify(actualConvertModel)).toEqual(expectedConvertModel);

        });

        it("clicking on finish when optional panels have not been completed should not pass the optional model to API", function() {
            var expectedConvertModel = '{"modules":{"Contacts":{"last_name":"'+last_name+'"},"Accounts":{"name":"'+account_name+'"}}}';

            layout._components[1].$('.header').click(); //click Account to complete Contact
            layout._components[1].$('.header').find('.show-record').click();
            layout.initiateFinish(); //click finish to complete Account

            expect(apiCallStub.lastCall.args[0]).toEqual('create');
            expect(apiCallStub.lastCall.args[1]).toMatch(/.*\/Leads\/convert/);

            actualConvertModel = apiCallStub.lastCall.args[2];
            expect(JSON.stringify(actualConvertModel)).toEqual(expectedConvertModel);

        });
    });

    describe("uploadAssociatedRecordFiles", function () {
        var layout, convertCompleteStub, uploadFileFieldStub, getPanelStub, isUploadSuccess, uploadSuccessCount, uploadErrorCount;

        beforeEach(function () {
            layout = initializeLayout();
            layout.meta.modules = [
                {module:'Foo'},
                {module:'Bar'}
            ];
            convertCompleteStub = sinon.stub(layout, 'convertComplete');
            uploadSuccessCount = 0;
            uploadErrorCount = 0;
            uploadFileFieldStub = sinon.stub(app.file, 'checkFileFieldsAndProcessUpload', function (view, callbacks, options, showAlert) {
                if (isUploadSuccess) {
                    uploadSuccessCount++;
                    callbacks.success();
                } else {
                    uploadErrorCount++;
                    callbacks.error();
                }
            });
            getPanelStub = sinon.stub(layout, '_getPanelByModuleName', function (moduleName) {
                var view = new Backbone.View();
                view.getAssociatedModel = function() {
                    return new Backbone.Model();
                };
                view.recordView = new Backbone.View();
                return view;
            });
        });

        afterEach(function () {
            convertCompleteStub.restore();
            uploadFileFieldStub.restore();
            getPanelStub.restore();
            delete layout;
        });

        it("should successfully call upload twice and convertSuccess once", function () {
            var convertResults = {
                modules: [
                    {_module: 'Foo', id: '123'},
                    {_module: 'Bar', id: '456'}
                ]
            };
            isUploadSuccess = true;
            layout.uploadAssociatedRecordFiles(convertResults);
            expect(uploadSuccessCount).toBe(2);
            expect(uploadErrorCount).toBe(0);
            expect(convertCompleteStub.callCount).toBe(1);
            expect(convertCompleteStub.lastCall.args[0]).toEqual('success');
        });

        it("should successfully call upload once if only one module data was returned and convertSuccess once", function () {
            var convertResults = {
                modules: [
                    {_module: 'Bar', id: '456'}
                ]
            };
            isUploadSuccess = true;
            layout.uploadAssociatedRecordFiles(convertResults);
            expect(uploadSuccessCount).toBe(1);
            expect(uploadErrorCount).toBe(0);
            expect(convertCompleteStub.callCount).toBe(1);
            expect(convertCompleteStub.lastCall.args[0]).toEqual('success');
        });

        it("should only call convertSuccess if no convert results returned", function () {
            var convertResults = {
                modules: []
            };
            isUploadSuccess = true;
            layout.uploadAssociatedRecordFiles(convertResults);
            expect(uploadSuccessCount).toBe(0);
            expect(uploadErrorCount).toBe(0);
            expect(convertCompleteStub.callCount).toBe(1);
            expect(convertCompleteStub.lastCall.args[0]).toEqual('success');
        });

        it("should call convertWarning if there was an error during upload", function () {
            var convertResults = {
                modules: [
                    {_module: 'Foo', id: '123'},
                    {_module: 'Bar', id: '456'}
                ]
            };
            isUploadSuccess = false;
            layout.uploadAssociatedRecordFiles(convertResults);
            expect(uploadSuccessCount).toBe(0);
            expect(uploadErrorCount).toBe(2);
            expect(convertCompleteStub.callCount).toBe(1);
            expect(convertCompleteStub.lastCall.args[0]).toEqual('warning');
        });
    });

    var mockDupesToFind = 2;
    var mockDupes = [
        {'id': '123', 'name': 'abc'},
        {'id': '456', 'name': 'def'}
    ];

    var createMockDupeView = function() {
        return {
            'initialize': function(options) {
                var self = this;
                app.view.Layout.prototype.initialize.call(this, options);
                this.context.on("dupecheck:fetch:fire", function(){
                    var mockDupesFound = [];
                    for (i = 0; i < mockDupesToFind; i++) {
                        mockDupesFound.push(mockDupes[i]);
                    }
                    self.collection.reset(mockDupesFound);
                });
            }
        };
    };

    var mockValidationResult = true;
    var createMockRecordView = function() {
        return {
            'render': function() {
                _.extend(this.model,
                    {
                        'isValid': function() {
                            return mockValidationResult;
                        }
                    }
                );
            }
        };
    };
});
