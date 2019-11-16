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
describe('ConsoleConfiguration.Layout.ConfigDrawer', function() {
    var app;
    var layout;
    var context;
    var options;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        context = app.context.getContext();
        context.set({
            model: new Backbone.Model(),
            collection: app.data.createBeanCollection('ConsoleConfiguration')
        });
        context.prepare();
        options = {
            context: context
        };

        sinon.collection.stub(app.metadata, 'getModule', function() {
            return {
                priority: {
                    audited: true,
                    name: 'priority',
                    type: 'enum',
                    vname: 'LBL_PRIORITY'
                },
                fields: {
                    name: {
                        type: 'name'
                    }
                },
                isBwcEnabled: false
            };
        });
        SugarTest.loadComponent('base', 'layout', 'config-drawer');
        layout = SugarTest.createLayout('base', 'ConsoleConfiguration', 'config-drawer', {}, context, true);
        sinon.collection.stub(layout, 'checkAccess', function() {
            return true;
        });
    });

    afterEach(function() {
        sinon.collection.restore();
        layout = null;
        context = null;
        options = null;
    });

    describe('initialize', function() {
        beforeEach(function() {
            sinon.collection.stub(layout, 'setAllowedModules', function() {});
            sinon.collection.stub(layout, '_super');
            sinon.collection.stub(layout, '_parseConsoleContext');
        });

        it('should call the _super method with initialize', function() {
            layout.initialize(options);
            expect(layout._super).toHaveBeenCalledWith('initialize', [options]);
        });

        it('should call setAllowedModules', function() {
            layout.initialize(options);
            expect(layout.setAllowedModules).toHaveBeenCalled();
        });

        it('should call _parseConsoleContext if no console ID exists on the context', function() {
            sinon.collection.stub(layout.context, 'get').withArgs('consoleId').returns(undefined)
            layout.initialize(options);
            expect(layout._parseConsoleContext).toHaveBeenCalled();
        });

        it('should not call _parseConsoleContext if a console ID already exists on the context', function() {
            sinon.collection.stub(layout.context, 'get').withArgs('consoleId').returns('12345')
            layout.initialize(options);
            expect(layout._parseConsoleContext).not.toHaveBeenCalled();
        });
    });

    describe('bindDataChange', function() {
        beforeEach(function() {
            sinon.collection.stub(layout, 'addModelToCollection', function() {});
            sinon.collection.stub(layout, 'removeModelFromCollection', function() {});
            sinon.collection.stub(layout.context, 'on', function() {});
            layout.bindDataChange();
        });

        it('should call layout.context.on with consoleconfiguration:config:model:add', function() {
            expect(layout.context.on).toHaveBeenCalledWith('consoleconfiguration:config:model:add');
        });

        it('should call layout.context.on with consoleconfiguration:config:model:remove', function() {
            expect(layout.context.on).toHaveBeenCalledWith('consoleconfiguration:config:model:remove');
        });
    });

    describe('loadData', function() {
        beforeEach(function() {
            sinon.collection.stub(layout, 'addModelToCollection', function() {});
            sinon.collection.stub(layout, 'setActiveTabIndex', function() {});
            sinon.collection.stub(app.metadata, 'getModuleNames', function() {
                return ['Accounts', 'Opportunities', 'Cases'];
            });
            sinon.collection.spy(JSON, 'parse');

            // Stub the calls to get context and model information
            sinon.collection.stub(layout.context, 'get').withArgs('consoleId').returns('12345');
            sinon.collection.stub(layout.model, 'get')
                .withArgs('enabled_modules').returns({
                '12345': ['Accounts', 'Opportunities']
            })
                .withArgs('order_by_primary').returns({
                '12345': {
                    'Accounts': 'next_renewal_date',
                    'Opportunities': 'date_closed'
                }
            })
                .withArgs('order_by_secondary').returns({
                '12345': {
                    'Accounts': '',
                    'Opportunities': ''
                }
            })
                .withArgs('filter_def').returns({
                '12345': {
                    'Accounts': [{'$owner': ''}],
                    'Opportunities': [{'$owner': ''}]
                }
            });
        });

        it('should call layout.model.get with various arguments', function() {
            layout.loadData();
            expect(layout.model.get).toHaveBeenCalledWith('order_by_primary');
            expect(layout.model.get).toHaveBeenCalledWith('order_by_secondary');
            expect(layout.model.get).toHaveBeenCalledWith('filter_def');
        });

        it('should call layout.addModelToCollection with moduleName and data', function() {
            layout.loadData();
            expect(layout.addModelToCollection).toHaveBeenCalledWith('Accounts', {
                enabled: true,
                enabled_module: 'Accounts',
                order_by_primary: 'next_renewal_date',
                order_by_secondary: '',
                filter_def: [{'$owner': ''}]
            });
        });

        it('should return the list of available and supported module names', function() {
            expect(layout.getAvailableModules()).toEqual(['Accounts', 'Opportunities']);
        });

        it('should call layout.setActiveTabIndex with 0', function() {
            layout.loadData();
            expect(layout.setActiveTabIndex).toHaveBeenCalledWith(0);
        });
    });

    describe('_checkModuleAccess', function() {
        beforeEach(function() {
            sandbox.stub(app.user, 'getAcls', function() {
                return {
                    'ConsoleConfiguration': {
                        admin: true
                    }
                };
            });
            sinon.collection.spy(app.user, 'get');
            layout._checkModuleAccess();
        });

        it('should call app.user.getAcls method', function() {
            expect(app.user.getAcls).toHaveBeenCalled();
        });

        it('should call the app.user.get method with', function() {
            expect(app.user.get).toHaveBeenCalledWith('type');
        });
    });

    describe('setAllowedModules', function() {
        beforeEach(function() {
            layout.supportedModules = ['Accounts'];
            sinon.collection.stub(app.lang, 'getAppListStrings', function() {return {Accounts: 'Accounts'};});
            sinon.collection.stub(layout.context, 'set', function() {});
            layout.setAllowedModules();
        });

        it('should call the app.metadata.getModule method', function() {
            expect(app.metadata.getModule).toHaveBeenCalledWith('Accounts');
        });

        it('should call layout.context.on method with allowedModules and modules', function() {
            expect(layout.context.set).toHaveBeenCalledWith('allowedModules', {Accounts: 'Accounts'});
        });
    });

    describe('setActiveIndex', function() {
        var index;
        beforeEach(function() {
            sinon.collection.stub(layout.context, 'set', function() {});
        });

        describe('when collection.length is < 1 and index is undefined', function() {
            it('should not call layotu.context.set', function() {
                index = undefined;
                layout.collection = {
                    length: 0,
                    off: function() {}
                };
                layout.setActiveTabIndex(index);

                expect(layout.context.set).not.toHaveBeenCalled();
            });
        });

        describe('when collection.length is >= 1 or index is undefined', function() {
            it('should not call layout.context.set at length 1', function() {
                index = undefined;
                layout.collection = {
                    length: 1,
                    off: function() {}
                };
                layout.setActiveTabIndex(index);

                expect(layout.context.set).toHaveBeenCalledWith('activeTabIndex', 0);
            });

            it('should not call layout.context.set at index defined', function() {
                index = 2;
                layout.collection = {
                    length: 0,
                    off: function() {}
                };
                layout.setActiveTabIndex(index);

                expect(layout.context.set).toHaveBeenCalledWith('activeTabIndex', 2);
            });
        });
    });

    describe('removeModelFromCollection', function() {
        var module;
        var removeStub;
        beforeEach(function() {
            removeStub = sinon.collection.stub();
            sinon.collection.stub(layout, 'setActiveTabIndex', function() {});
        });

        afterEach(function() {
            removeStub = null;
        });

        describe('when module is not an enabled_module', function() {
            it('should not call layout.collection.remove', function() {
                module = 'Potato';
                layout.collection = {
                    models: [
                        {
                            get: function() {
                                return 'Accounts';
                            }
                        }
                    ],
                    off: function() {},
                    remove: removeStub
                };
                layout.removeModelFromCollection(module);
                expect(layout.collection.remove).not.toHaveBeenCalled();
            });
        });

        describe('when module is enabled and model is not empty', function() {
            beforeEach(function() {
                module = 'Accounts';
                layout.collection = {
                    models: [
                        {
                            get: function() {
                                return 'Accounts';
                            }
                        }
                    ],
                    off: function() {},
                    remove: removeStub
                };
                layout.removeModelFromCollection(module);
            });

            it('should call layout.collection.remove', function() {
                expect(layout.collection.remove).toHaveBeenCalledWith(layout.collection.models[0]);
            });

            it('should call layout.setActiveTabIndex', function() {
                expect(layout.setActiveTabIndex).toHaveBeenCalled();
            });
        });
    });

    describe('addModelToCollection', function() {
        var module;
        var data;
        var addStub;
        beforeEach(function() {
            module = 'Accounts';
            data = {
                enabled: true,
                enabled_module: 'Accounts',
                order_by_primary: 'next_renewal_date',
                order_by_secondary: '',
                filter_def: [{'$owner': ''}]
            };

            addStub = sinon.collection.stub();
            sinon.collection.stub(layout.context, 'get', function() {
                return {Accounts: 'Accounts'};
            });
            sinon.collection.stub(layout, 'setActiveTabIndex', function() {});
            sinon.collection.stub(layout, 'getModuleFields', function() {});
            sinon.collection.stub(layout, 'addValidationTasks', function() {});
        });

        it('should call setActiveTabIndex method', function() {
            layout.collection = {
                models: [
                    {
                        get: function() {
                            return 'Accounts';
                        }
                    }
                ],
                off: function() {},
                add: addStub
            };
            layout.addModelToCollection(module, data);

            expect(layout.setActiveTabIndex).toHaveBeenCalled();
        });

        describe('when existingBean is not empty', function() {
            it('should not call layout.addValidationTasks method', function() {
                layout.collection = {
                    models: [
                        {
                            get: function() {
                                return 'Accounts';
                            }
                        }
                    ],
                    off: function() {},
                    add: addStub
                };
                layout.addModelToCollection(module, data);

                expect(layout.addValidationTasks).not.toHaveBeenCalled();
            });
        });

        describe('when existingBean is empty', function() {
            beforeEach(function() {
                layout.collection = {
                    models: [
                        {
                            get: function() {
                                return 'Opportunities';
                            }
                        }
                    ],
                    off: function() {},
                    add: addStub
                };
                layout.addModelToCollection(module, data);
            });

            it('should call layout.collection.add with bean', function() {
                expect(layout.collection.add).toHaveBeenCalled();
            });

            it('should call layout.getModuleFields with bean', function() {
                expect(layout.getModuleFields).toHaveBeenCalled();
            });

            it('should not layout.addValidationTasks method', function() {
                expect(layout.addValidationTasks).toHaveBeenCalled();
            });
        });
    });

    describe('getModuleFields', function() {
        var bean;
        beforeEach(function() {
            bean = app.data.createBean(layout.module, {
                enabled: true,
                enabled_module: 'Accounts',
                order_by_primary: 'next_renewal_date',
                order_by_secondary: '',
                filter_def: [{'$owner': ''}]
            });

            sinon.collection.stub(bean, 'set', function() {});
            sinon.collection.stub(bean, 'get', function() {
                return 'Accounts';
            });

            sinon.collection.stub(app.metadata, 'getView', function() {
                return {
                    panels: [{
                        fields: [
                            {
                                default: true,
                                enabled: true,
                                label: 'LBL_LIST_PRIORITY',
                                name: 'priority',
                                type: 'enum'
                            }
                        ]
                    }]
                };
            });
            sinon.collection.stub(app.lang, 'get', function() {return 'LBL_PRIORITY';});
        });

        it('should call bean.get method', function() {
            layout.getModuleFields(bean);
            expect(bean.get).toHaveBeenCalledWith('enabled_module');
        });

        it('should call app.metadata.getView', function() {
            layout.getModuleFields(bean);
            expect(app.metadata.getView).toHaveBeenCalledWith('Accounts', 'multi-line-list');
        });

        it('should call app.metadata.getModule', function() {
            layout.getModuleFields(bean);
            expect(app.metadata.getModule).toHaveBeenCalledWith('Accounts', 'fields');
        });

        it('should call bean.set with tabContent and content', function() {
            layout.getModuleFields(bean);
            expect(bean.set).toHaveBeenCalledWith('tabContent', {
                dropdownFields: {
                    priority: 'LBL_PRIORITY'
                },
                fields: {
                    priority: 'LBL_PRIORITY'
                }
            });
        });
    });

    describe('addValidationTasks', function() {
        var bean;

        describe('when bean is not undefined', function() {
            beforeEach(function() {
                bean = app.data.createBean(layout.module, {
                    enabled: true,
                    enabled_module: 'Accounts',
                    order_by_primary: 'next_renewal_date',
                    order_by_secondary: '',
                    filter_def: [{'$owner': ''}]
                });

                sinon.collection.stub(bean, 'addValidationTask', function() {});
                sinon.collection.stub(layout, '_validatePrimaryOrderBy', function() {});
            });

            it('should call bean.addValidationTask method', function() {
                layout.addValidationTasks(bean);
                expect(bean.addValidationTask).toHaveBeenCalledWith('check_order_by_primary');
            });
        });

        describe('when bean is undefined', function() {
            var model;
            beforeEach(function() {
                bean = undefined;
                layout.collection = {
                    models: [
                        {
                            addValidationTask: sinon.collection.stub()
                        }
                    ],
                    off: function() {}
                };

                model = layout.collection.models[0];
                sinon.collection.stub(layout, '_validatePrimaryOrderBy', function() {});
            });

            it('should call bean.addValidationTask method', function() {
                layout.addValidationTasks(bean);
                expect(model.addValidationTask).toHaveBeenCalledWith('check_order_by_primary');
            });
        });
    });

    describe('_validatePrimaryOrderBy', function() {
        var fields;
        var errors;
        var callback;
        beforeEach(function() {
            fields = {
                name: 'primary_order_by',
                type: 'enum',
                vname: 'LBL_CONSOLE_SORT_ORDER_PRIMARY'
            };

            errors = {
                order_by_primary: {
                    required: false
                }
            };

            callback = sinon.collection.stub();
        });

        describe('when order_by_primary is empty', function() {
            it('should set order_by_primary required as true', function() {
                layout.get = function() {return;};
                layout._validatePrimaryOrderBy(fields, errors, callback);
                expect(errors.order_by_primary.required).toBe(true);
            });
        });
    });

    describe('_dispose', function() {
        beforeEach(function() {
            sinon.collection.stub(layout.context, 'off', function() {});
            sinon.collection.stub(layout, '_super');
            layout._dispose();
        });

        it('should call layout.context.off method', function() {
            expect(layout.context.off).toHaveBeenCalledWith('consoleconfiguration:config:model:add');
            expect(layout.context.off).toHaveBeenCalledWith('consoleconfiguration:config:model:remove');
        });

        it('should call layout._super method', function() {
            expect(layout._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
