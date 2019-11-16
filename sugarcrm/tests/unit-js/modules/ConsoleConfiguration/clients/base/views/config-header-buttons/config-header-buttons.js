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
describe('ConsoleConfiguration.View.ConfigHeaderButtons', function() {
    var app;
    var view;
    var context;
    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();

        view = SugarTest.createView('base', 'ConsoleConfiguration', 'config-header-buttons', null, null, true);
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
            beforeEach(function() {
                view._viewAlerts = [];
                sinon.collection.stub(app.alert, 'show', function() {});
                view.showInvalidModel();
            });

            it('should not call app.logger.error method', function() {

                expect(app.logger.error).not.toHaveBeenCalled();
            });

            it('should push invalid-data into view._viewAlerts', function() {

                expect(view._viewAlerts).toEqual(['invalid-data']);
            });

            it('should call app.alert.show mwthod', function() {

                expect(app.alert.show).toHaveBeenCalledWith('invalid-data', {
                    level: 'error',
                    messages: 'ERR_RESOLVE_ERRORS'
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

            it('should not call app.router.navigate', function() {
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

        beforeEach(function() {
            model = new Backbone.Model('ConsoleConfiguration');
            view.collection = {
                models: [model],
                off: function() {}
            };

            // Stub the calls to "this.context.get". Pretend that no settings
            // currently exist for this fake console ID in the config table
            getStub = sinon.collection.stub();
            getStub.withArgs('enabled_modules').returns({'1234-5678': ['Accounts']})
                .withArgs('order_by_primary').returns({})
                .withArgs('order_by_secondary').returns({})
                .withArgs('filter_def').returns({});
            setStub = sinon.collection.stub();
            sinon.collection.stub(view.context, 'get').withArgs('consoleId').returns('1234-5678')
                .withArgs('model').returns({
                    model: model,
                    get: getStub,
                    set: setStub
                });

            contextModel = view.context.get('model');

            // Stub the calls that read the current field values of the view
            // for each module tab
            sinon.collection.stub(model, 'get')
                .withArgs('enabled_module').returns('Accounts')
                .withArgs('order_by_primary').returns('next_renewal_date')
                .withArgs('order_by_secondary').returns('')
                .withArgs('filter_def').returns({'$owner': ''});
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
            expect(model.get).toHaveBeenCalledWith('order_by_primary');
            expect(model.get).toHaveBeenCalledWith('order_by_secondary');
            expect(model.get).toHaveBeenCalledWith('filter_def');
        });

        it('should call the view.context.get.set method with the new values', function() {
            view._setupSaveConfig();

            expect(contextModel.set).toHaveBeenCalledWith({
                is_setup: true,
                enabled_modules: {'1234-5678': ['Accounts']},
                order_by_primary: {'1234-5678': {'Accounts': 'next_renewal_date'}},
                order_by_secondary: {'1234-5678': {'Accounts': ''}},
                filter_def: {'1234-5678': {'Accounts': {'$owner': ''}}}
            }, {silent: true});
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
            model = new Backbone.Model('ConsoleConfiguration');
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
