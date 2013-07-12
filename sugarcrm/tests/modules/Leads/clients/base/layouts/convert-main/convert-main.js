describe("Leads.Base.Layout.ConvertMain", function() {
    var app, layout, createLayout, hasAccessStub, mockAccess;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addLayoutDefinition('convert-main', {
            modules: [
                {
                    module: 'Foo',
                    required: true
                },
                {
                    module: 'Bar',
                    required: true
                },
                {
                    module: 'Baz',
                    required: false,
                    dependentModules: {
                        'Foo': {
                            'fieldMapping': {
                                'foo_id': 'id'
                            }
                        }
                    }
                }
            ]
        });
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        mockAccess = {
            Foo: true,
            Bar: true,
            Baz: true
        };
        hasAccessStub = sinon.stub(app.acl, 'hasAccess', function(access, module) {
            return (_.isUndefined(mockAccess[module])) ? true : mockAccess[module];
        });
    });

    afterEach(function() {
        hasAccessStub.restore();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        SugarTest.testMetadata.dispose();
    });

    createLayout = function() {
        return SugarTest.createLayout('base', 'Leads', 'convert-main', null, null, true);
    };

    describe("ACL Access Checks", function() {
        it("should have all three convert panel components created if user has access to all", function() {
            layout = createLayout();
            expect(_.keys(layout.convertPanels)).toEqual(['Foo', 'Bar', 'Baz']);
            expect(layout.noAccessRequiredModules).toEqual([]);
        });

        it("should have two convert panel components created if missing access to one optional module", function() {
            mockAccess.Baz = false;
            layout = createLayout();
            expect(_.keys(layout.convertPanels)).toEqual(['Foo', 'Bar']);
            expect(layout.noAccessRequiredModules).toEqual([]); //Baz is not required
        });

        it("should have one convert panel components created if missing access to one optional and one required module", function() {
            mockAccess.Foo = false;
            mockAccess.Baz = false;
            layout = createLayout();
            expect(_.keys(layout.convertPanels)).toEqual(['Bar']);
            expect(layout.noAccessRequiredModules).toEqual(['Foo']); //Baz is not required
        });

        it("should display an error, prevent render, and close the drawer if user is missing access to a required module", function() {
            var drawerCloseStub, renderStub,
                alertShowStub = sinon.stub(app.alert, 'show');

            app.drawer = app.drawer || {};
            app.drawer.close = app.drawer.close || function() {};
            drawerCloseStub = sinon.stub(app.drawer, 'close');

            mockAccess.Foo = false;
            layout = createLayout();
            renderStub = sinon.stub(layout, '_render');
            layout.render();

            expect(alertShowStub.calledWith('convert_access_denied')).toBeTruthy();
            expect(drawerCloseStub.called).toBeTruthy();
            expect(renderStub.called).toBeFalsy();

            alertShowStub.restore();
            drawerCloseStub.restore();
            renderStub.restore();
        });
    });

    describe("After Initialize", function() {
        var contextTriggerStub;

        beforeEach(function() {
            layout = createLayout();
            contextTriggerStub = sinon.stub(layout.context, 'trigger');
        });

        afterEach(function() {
            contextTriggerStub.restore();
        });

        it("should pull out the dependencies based on the module metadata", function() {
            expect(layout.dependentModules['Foo']).toBeUndefined();
            expect(layout.dependentModules['Bar']).toBeUndefined();
            expect(layout.dependentModules['Baz']).not.toBeUndefined();
        });

        it("should retrieve the lead data from api and push model to the context for panel to use", function() {
            var mockModel = new Backbone.Model({id:'123'}),
                fetchStub = sinon.stub(mockModel, 'fetch', function(options) {
                    options.success(mockModel);
                });
            layout.context.set('leadsModel', mockModel);
            layout.render();
            expect(contextTriggerStub.lastCall.args).toEqual(['lead:convert:populate', mockModel]);
            fetchStub.restore();
        });

        it("should ignore hidden/shown events that are propagated to the panel body not directly on it", function () {
            var mockPropagatedEvent = {
                target: '<tooltip></tooltip>',
                currentTarget: '<panelBody></panelBody>'
            };
            layout.handlePanelCollapseEvent(mockPropagatedEvent);
            expect(contextTriggerStub.callCount).toEqual(0);
        });

        it("should pass along hidden/shown events to the context if event is fired directly on the panel body", function () {
            var mockTargetHtml = '<div data-module="Foo"></div>';
            var mockEvent = {
                type: 'shown',
                target: mockTargetHtml,
                currentTarget: mockTargetHtml
            };
            layout.handlePanelCollapseEvent(mockEvent);
            expect(contextTriggerStub.lastCall.args).toEqual(['lead:convert:Foo:shown']);
        });

        it("should add/remove model from associated model array when panel is complete/reset", function () {
            var mockModel = {id:'123'};
            expect(layout.associatedModels['Foo']).toBeUndefined();
            layout.handlePanelComplete('Foo', mockModel);
            expect(layout.associatedModels['Foo']).toEqual(mockModel);
            layout.handlePanelReset('Foo');
            expect(layout.associatedModels['Foo']).toBeUndefined();
        });

        it("should enable dependent panels when dependencies are met", function () {
            layout.associatedModels['Foo'] = {id:'123'};
            layout.checkDependentModules();
            expect(contextTriggerStub.lastCall.args).toEqual(['lead:convert:Baz:enable', true]);
        });

        it("should disable dependent panels when dependencies are not met", function () {
            delete layout.associatedModels['Foo'];
            layout.checkDependentModules();
            expect(contextTriggerStub.lastCall.args).toEqual(['lead:convert:Baz:enable', false]);
        });

        it("should enable save button when all required modules have been complete", function () {
            layout.associatedModels['Foo'] = {id:'123'};
            layout.associatedModels['Bar'] = {id:'456'};
            layout.checkRequired();
            expect(contextTriggerStub.lastCall.args).toEqual(['lead:convert-save:toggle', true]);
        });

        it("should enable save button when all required modules have been complete", function () {
            delete layout.associatedModels['Foo'];
            layout.associatedModels['Bar'] = {id:'456'};
            layout.checkRequired();
            expect(contextTriggerStub.lastCall.args).toEqual(['lead:convert-save:toggle', false]);
        });

        describe("Convert Save", function () {
            var ajaxSpy, convertCompleteStub, leadConvertPattern, mockLeadConvertResponse;

            beforeEach(function () {
                ajaxSpy = sinon.spy($, 'ajax')
                convertCompleteStub = sinon.stub(layout, 'convertComplete');

                SugarTest.seedFakeServer();
                leadConvertPattern = /.*rest\/v10\/Leads\/lead123\/convert.*/;
                mockLeadConvertResponse = [200, { "Content-Type": "application/json"}, JSON.stringify({})];

                layout.context.set('leadsModel', new Backbone.Model({id:'lead123'}));
            });

            afterEach(function () {
                ajaxSpy.restore();
                convertCompleteStub.restore();
            });

            it("should call lead convert api with associated models and call to upload files", function () {
                var uploadFilesStub = sinon.stub(layout, 'uploadAssociatedRecordFiles');

                layout.associatedModels = {
                    Foo: {id:123},
                    Bar: {id:456},
                    Baz: {id:789}
                };
                SugarTest.server.respondWith("POST", leadConvertPattern, mockLeadConvertResponse);
                layout.handleSave();
                SugarTest.server.respond();
                expect(ajaxSpy.lastCall.args[0].data).toEqual('{"modules":{"Foo":{"id":123},"Bar":{"id":456},"Baz":{"id":789}}}');
                expect(uploadFilesStub.callCount).toEqual(1);
                uploadFilesStub.restore();
            });

            it("should disable the save button while saving and re-enable if there is an error", function () {
                mockLeadConvertResponse[0] = 500;
                SugarTest.server.respondWith("POST", leadConvertPattern, mockLeadConvertResponse);
                layout.handleSave();
                SugarTest.server.respond();
                expect(contextTriggerStub.calledWith('lead:convert-save:toggle', false)).toBe(true);
                expect(contextTriggerStub.calledWith('lead:convert-save:toggle', true)).toBe(true);
                expect(convertCompleteStub.calledWith('error')).toBe(true);
            });
        });

        describe("Upload Associated Record Files", function () {
            var convertCompleteStub, checkAndProcessUploadStub, checkAndProcessUploadCallbacks, mockLeadConvertResponse;

            beforeEach(function () {
                convertCompleteStub = sinon.stub(layout, 'convertComplete');

                checkAndProcessUploadCallbacks = {};
                checkAndProcessUploadStub = sinon.stub(app.file, 'checkFileFieldsAndProcessUpload', function(view, options) {
                    checkAndProcessUploadCallbacks = options;
                });

                mockLeadConvertResponse = {
                    modules: [
                        {_module: 'Foo', id: '123'},
                        {_module: 'Bar', id: '456'},
                        {_module: 'Baz', id: '789'}
                    ]
                };
            });

            afterEach(function () {
                convertCompleteStub.restore();
                checkAndProcessUploadStub.restore();
            });

            it("should check for upload files on each module where we are creating a record (no id passed)", function() {
                layout.associatedModels = {
                    Foo: new Backbone.Model({name:'foo'}),
                    Bar: new Backbone.Model({name:'bar'}),
                    Baz: new Backbone.Model({id:'789'})
                };
                layout.uploadAssociatedRecordFiles(mockLeadConvertResponse);
                expect(checkAndProcessUploadStub.callCount).toEqual(2);
            });

            it("should throw a convert success if all calls succeed", function() {
                layout.associatedModels = {
                    Foo: new Backbone.Model({name:'foo'}),
                    Bar: new Backbone.Model({name:'bar'}),
                    Baz: new Backbone.Model({id:'789'})
                };
                layout.uploadAssociatedRecordFiles(mockLeadConvertResponse);
                checkAndProcessUploadCallbacks.success();
                checkAndProcessUploadCallbacks.success();
                expect(convertCompleteStub.calledWith('success')).toBe(true);
            });

            it("should throw a convert warning if any calls fail", function() {
                layout.associatedModels = {
                    Foo: new Backbone.Model({name:'foo'}),
                    Bar: new Backbone.Model({name:'bar'}),
                    Baz: new Backbone.Model({id:'789'})
                };
                layout.uploadAssociatedRecordFiles(mockLeadConvertResponse);
                checkAndProcessUploadCallbacks.success();
                checkAndProcessUploadCallbacks.error();
                expect(convertCompleteStub.calledWith('warning')).toBe(true);
            });
        });
    });
});
