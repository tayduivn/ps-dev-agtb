// FILE SUGARCRM flav=ent ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
describe('VisualPipeline.View.ConfigHeaderButtons', function() {
    var app;
    var view;
    var context;
    var layout;
    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();

        view = SugarTest.createView('base', 'VisualPipeline', 'config-header-buttons', null, null, true);
        layout = SugarTest.createLayout('base', 'VisualPipeline', 'config-drawer-content', null, null);
        view.layout = layout;
        app.routing.start();
    });

    afterEach(function() {
        sinon.collection.restore();
        app.router.stop();
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        var result;
        var options;
        var showInvalidModelStub;
        beforeEach(function() {
            options = {
                context: context,
            };

            sinon.collection.stub(view, '_super', function() {});
            showInvalidModelStub = sinon.collection.stub();
            view.initialize(options);
        });

        afterEach(function() {
            result = null;
        });

        it('should call view._super with initialize and options', function() {

            expect(view._super).toHaveBeenCalledWith('initialize', [options]);
        });

        it('should initialize view._viewAlerts as an empty array', function() {

            expect(view._viewAlerts).toEqual([]);
        });
    });

    describe('showInvalidModel', function() {
        describe('when view is defined', function() {
            var errorMessage;

            beforeEach(function() {
                view._viewAlerts = [];
                view.validatedModels = [
                    {isValid: false, moduleName: 'Invalid Module'},
                    {isValid: true, moduleName: 'Valid Module'}
                ];
                errorMessage = 'Error message';
                sinon.collection.stub(app.lang, 'get').returns(errorMessage);
                sinon.collection.stub(app.alert, 'show', function() {});
                view.showInvalidModel();
            });

            it('should not call app.logger.error method', function() {

                expect(app.logger.error).not.toHaveBeenCalled();
            });

            it('should push invalid-data into view._viewAlerts', function() {

                expect(view._viewAlerts).toEqual(['invalid-data']);
            });

            it('should call app.alert.show method', function() {

                expect(app.alert.show).toHaveBeenCalledWith('invalid-data', {
                    level: 'error',
                    messages: errorMessage + '<li>Invalid Module</li>'
                });
            });
        });
    });

    describe('cancelConfig', function() {
        beforeEach(function() {
            sinon.collection.stub(view.context, 'get', function() {});
            sinon.collection.spy(app.router, 'navigate');
        });

        describe('when triggerBefore is true', function() {
            beforeEach(function() {
                sinon.collection.stub(view, 'triggerBefore', function() {return true;});
            });

            it('should call app.router.navigate', function() {
                app.drawer = {
                    close: $.noop,
                    count: function() {
                        return 1;
                    }
                };
                sinon.collection.spy(app.drawer, 'close');
                view.cancelConfig();

                expect(app.router.navigate).toHaveBeenCalledWith('#Administration', {trigger: true});
                delete app.drawer;
            });

            describe('when app.drawer.count is defined', function() {
                it('should call app.drawer.close method', function() {
                    app.drawer = {
                        close: $.noop,
                        count: function() {
                            return 1;
                        }
                    };
                    sinon.collection.spy(app.drawer, 'close');
                    view.cancelConfig();

                    expect(app.drawer.close).toHaveBeenCalledWith(view.context, view.context.get());
                    delete app.drawer;
                });
            });

            describe('when app.drawer.count is not defined', function() {
                it('should not call app.drawer.close method', function() {
                    app.drawer = {
                        close: $.noop,
                        count: function() {
                            return undefined;
                        }
                    };
                    sinon.collection.spy(app.drawer, 'close');
                    view.cancelConfig();

                    expect(app.drawer.close).not.toHaveBeenCalled();
                    delete app.drawer;
                });
            });
        });

        describe('when triggerBefore is false', function() {
            it('should not call app.router.navigate', function() {
                sinon.collection.stub(view, 'triggerBefore', function() {return false;});
                app.drawer = {
                    close: $.noop,
                    count: function() {
                        return 1;
                    }
                };
                sinon.collection.spy(app.drawer, 'close');
                view.cancelConfig();

                expect(app.router.navigate).not.toHaveBeenCalled();
                delete app.drawer;
            });
        });
    });

    describe('_setupSaveConfig', function() {
        var model;
        var contextModel;
        var getStub;
        var setStub;
        var availableColumns;
        beforeEach(function() {
            model = new Backbone.Model('VisualPipeline');
            view.collection = {
                models: [model],
                off: function() {}
            };
            getStub = sinon.collection.stub().withArgs('enabled_modules').returns(['Cases']);
            setStub = sinon.collection.stub();
            sinon.collection.stub(view.context, 'get', function() {
                return {
                    model: model,
                    get: getStub,
                    set: setStub
                };
            });
            sinon.collection.stub(view, 'getAvailableColumnNames').withArgs(['Cases'])
                .returns(['test']);
            contextModel = view.context.get('model');

            sinon.collection.stub(model, 'get')
                .withArgs('enabled_module').returns(['Cases'])
                .withArgs('table_header').returns('status')
                .withArgs('tile_header').returns('name')
                .withArgs('tile_body_fields').returns(['account_name', 'priority'])
                .withArgs('records_per_column').returns(10)
                .withArgs('hidden_values').returns(['test']);
        });

        it('should call view.context.get with model', function() {
            view._setupSaveConfig();

            expect(view.context.get).toHaveBeenCalledWith('model');
        });

        it('should call view.context.get.get with enabled_modules', function() {
            view._setupSaveConfig();

            expect(contextModel.get).toHaveBeenCalledWith('enabled_modules');
        });

        it('should call model.get with method', function() {
            view._setupSaveConfig();

            expect(model.get).toHaveBeenCalledWith('enabled_module');
            expect(model.get).toHaveBeenCalledWith('table_header');
            expect(model.get).toHaveBeenCalledWith('tile_header');
            expect(model.get).toHaveBeenCalledWith('tile_body_fields');
            expect(model.get).toHaveBeenCalledWith('records_per_column');
            expect(model.get).toHaveBeenCalledWith('hidden_values');
            expect(view.getAvailableColumnNames).toHaveBeenCalledWith(['Cases']);
        });

        it('should call view.context.get.set method', function() {
            view._setupSaveConfig();

            expect(contextModel.set).toHaveBeenCalledWith({
                is_setup: true,
                enabled_modules: ['Cases'],
                table_header: {Cases: 'status'},
                tile_header: {Cases: 'name'},
                tile_body_fields: {Cases: ['account_name', 'priority']},
                records_per_column: {Cases: 10},
                hidden_values: {Cases: ['test']},
                available_columns: {Cases: ['test']}
            }, {silent: true});
        });
    });

    describe('getAvailableColumnNames', function() {
        var mockHtml;
        var availableColumnNames;
        var model;
        beforeEach(function() {
            mockHtml = '<div class="main-pane span8">' +
                            '<div class="accordion record-panel">' +
                                '<div class="config-visual-pipeline-group accordion-group">' +
                                    '<div class="accordion-headerpane"></div>' +
                                    '<div class="accordion-inner">' +
                                        '<div id="tabs" class="ui-tabs ui-corner-all ui-widget">' +
                                            '<div id="Cases">' +
                                                '<div class="row-fluid">' +
                                                    '<div class="span6 record-cell">' +
                                                        '<ul id="pipeline-sortable-1">' +
                                                            '<li data-headervalue="Assigned">' +
                                                                '<span>Assigned</span></li>' +
                                                            '<li data-headervalue="New">' +
                                                                '<span>New</span></li>' +
                                                        '</ul>>' +
                                                    '</div>' +
                                                '</div>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>';

            view.layout.$el = $(mockHtml);

            model = new Backbone.Model('VisualPipeline');
            sinon.collection.stub(model, 'get').withArgs('enabled_module').returns('Cases')
                .withArgs('table_header').returns('status');

            view.collection = {
                models: [model],
                off: function() {}
            };

            sinon.collection.stub(view.model, 'get').withArgs('table_header').returns({
                'Cases': 'status',
                'Opportunities': 'sales_stage',
                'Tasks': 'status',
                'Leads': 'status'
            });

            sinon.collection.stub(app.metadata, 'getModule')
                .withArgs('Cases', 'fields')
                .returns({
                    status: {
                        name: 'status',
                        options: 'case_status_dom'
                    }
                });
            sinon.collection.stub(app.lang, 'getAppListStrings').returns(
                {
                    'New': 'New',
                    'Assigned': 'Assigned',
                    'Duplicate': 'Duplicate',
                    'Lost': 'Lost'
                }
            );
        });

        it('should call app.metadata.getModule with an array and fields', function() {
            view.getAvailableColumnNames('Cases');

            expect(app.metadata.getModule).toHaveBeenCalledWith('Cases', 'fields');
        });

        it('should return availableColumnNames', function() {
            availableColumnNames = view.getAvailableColumnNames('Cases');

            expect(availableColumnNames).toEqual({status: {'Assigned': 'Assigned', 'New': 'New'}});
        });
    });

    describe('_saveConfig', function() {
        var setDisabledStub;
        beforeEach(function() {
            setDisabledStub = sinon.collection.stub();
            sinon.collection.stub(view, 'validateCollection', function() {});
            sinon.collection.stub(view, 'getField', function() {
                return {
                    setDisabled: setDisabledStub
                };
            });
            view._saveConfig();
        });

        it('should set view.validatedModels to []', function() {

            expect(view.validatedModels).toEqual([]);
        });

        it('should call view.getField with save_button', function() {

            expect(view.getField).toHaveBeenCalledWith('save_button');
        });

        it('should call view.getField.setDisabled with false', function() {

            expect(view.getField('save_button').setDisabled).toHaveBeenCalledWith(true);
        });
    });

    describe('validateCollection', function() {
        var model;
        beforeEach(function() {
            model = new Backbone.Model('VisualPipeline');
            sinon.collection.stub(view, 'getFields', function() {
                return {
                    name: {
                        type: name
                    }
                };
            });

            view.collection = {
                models: [model],
                off: function() {}
            };
            sinon.collection.stub(model, 'doValidate', function() {});
            sinon.collection.stub(app.acl, 'hasAccessToModel', function() {});

            view.validateCollection();
        });

        it('should call view.getFields method', function() {

            expect(view.getFields).toHaveBeenCalledWith(view.module, view.model);
        });

        it('should call app.acl.hasAccessToModel method', function() {

            expect(app.acl.hasAccessToModel).toHaveBeenCalledWith('edit', view.model, 'name');
        });

        it('should call model.doValidate method', function() {

            expect(model.doValidate).toHaveBeenCalled('name');
        });
    });
});
