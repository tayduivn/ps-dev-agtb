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
describe('Opportunities.Base.Plugins.Cascade', function() {
    var app;
    var plugin;
    var moduleName = 'Opportunitites';
    var model;

    beforeEach(function() {
        app = SUGAR.App;
        SugarTest.loadFile(
            '../modules/Opportunities/clients/base/plugins',
            'Cascade',
            'js',
            function(data) {
                app.events.off('app:init');
                eval(data);
                app.events.trigger('app:init');
            }
        );
        plugin = app.plugins.plugins.field.Cascade;
        model = app.data.createBean(moduleName, {
            id: '123test',
            name: 'Lorem ipsum dolor sit amet'
        });
    });

    afterEach(function() {
        sinon.collection.restore();
        plugin = null;
        model = null;
    });

    describe('onAttach', function() {
        using(
            'different opps+rlis configs', [
                {
                    oppsConfig: 'RevenueLineItems',
                    rlisEnabled: true,
                    callCount: 1
                }, {
                    oppsConfig: 'OpportunitiesOnly',
                    rlisEnabled: false,
                    callCount: 0
                }
            ],
            function(values) {
            it('should set field properties appropriately', function() {
                sinon.collection.stub(app.metadata, 'getModule', function() {
                    return {opps_view_by: values.oppsConfig};
                });
                sinon.collection.stub(_, 'wrap');
                var field = {
                    options: {
                        def: {
                            name: 'testField'
                        },
                        view: {
                            action: 'edit'
                        },
                        model: {
                            on: sinon.stub()
                        }
                    },
                    on: sinon.stub()
                };
                plugin.onAttach(field, plugin);
                expect(field.rlisEnabled).toEqual(values.rlisEnabled);
                expect(field.on.callCount).toBe(values.callCount);
                expect(field.options.model.on.callCount).toBe(values.callCount);
                expect(_.wrap.callCount).toBe(values.callCount);
            });
        });
    });

    describe('handleModeChange', function() {
        var setDisabled;
        beforeEach(function() {
            setDisabled = sinon.stub();
            plugin.field = {setDisabled: setDisabled, $el: true};
            sinon.collection.stub(plugin, 'handleReadOnly');
        });
        afterEach(function() {
            setDisabled = null;
        });
        it('should bind edit listeners in edit mode', function() {
            plugin.handleModeChange('edit');
            expect(setDisabled).not.toHaveBeenCalled();
            expect(plugin.handleReadOnly).toHaveBeenCalled();
        });

        it('should enable field in non-edit mode', function() {
            plugin.handleModeChange('detail');
            expect(setDisabled).toHaveBeenCalledWith(false, {trigger: true});
            expect(plugin.handleReadOnly).not.toHaveBeenCalled();
        });
    });

    describe('resetModelValue', function() {
        it('should set model value to synced value, and clear cascade field', function() {
            plugin.baseFieldName = 'sales_stage';
            plugin.model = model;
            sinon.collection.stub(model, 'getSynced', function() {
                return 'beforeValue';
            });
            model.set = sinon.stub();

            plugin.resetModelValue();
            expect(model.set.callCount).toBe(2);
            expect(model.getSynced).toHaveBeenCalledWith('sales_stage');
            expect(model.set).toHaveBeenCalledWith('sales_stage_cascade', '');
            expect(model.set).toHaveBeenCalledWith('sales_stage', 'beforeValue');
        });
    });

    describe('handleReadOnly', function() {
        var setDisabled;
        beforeEach(function() {
            setDisabled = sinon.stub();
            plugin.field = {setDisabled: setDisabled, $el: true};
            plugin.model = SugarTest.app.data.createBean(moduleName);
            sinon.collection.stub(plugin, 'bindEditActions');
        });
        afterEach(function() {
            setDisabled = null;
        });
        it('when disabled field is an array call setDisabled', function() {
            plugin.options = {
                def: {
                    disable_field: ['test1','test2'],
                }
            };
            sinon.collection.stub(plugin.model, 'get')
                .withArgs('test1').returns('4')
                .withArgs('test2').returns('4');

            plugin.handleReadOnly();
            expect(plugin.field.setDisabled).toHaveBeenCalledWith(true, {trigger: true});
            expect(plugin.bindEditActions).toHaveBeenCalled();
        });
        it('when disabled field is an array call setDisabled', function() {
            plugin.options = {
                def: {
                    disable_field: ['test1','test2'],
                }
            };
            sinon.collection.stub(plugin.model, 'get')
                .withArgs('test1').returns('4')
                .withArgs('test2').returns('2');

            plugin.handleReadOnly();
            expect(plugin.field.setDisabled).toHaveBeenCalledWith(false, {trigger: true});
        });
        it('when disabled field is a string call setDisabled', function() {
            plugin.options = {
                def: {
                    disable_field: 'test',
                }
            };
            sinon.collection.stub(plugin.model, 'get')
                .withArgs('test').returns('4');

            plugin.handleReadOnly();
            expect(plugin.field.setDisabled).toHaveBeenCalledWith(false, {trigger: true});
            expect(plugin.bindEditActions).toHaveBeenCalled();
        });
        it('when disabled field is a string call setDisabled', function() {
            plugin.options = {
                def: {
                    disable_field: 'test',
                }
            };
            sinon.collection.stub(plugin.model, 'get')
                .withArgs('test').returns('0');

            plugin.handleReadOnly();
            expect(plugin.field.setDisabled).toHaveBeenCalledWith(true, {trigger: true});
            expect(plugin.bindEditActions).toHaveBeenCalled();
        });
        it('when disabled field is a not defined do not call setDisabled', function() {
            plugin.options = {
                def: {
                    disable_field: null,
                }
            };

            plugin.handleReadOnly();
            expect(plugin.field.setDisabled).not.toHaveBeenCalled();
            expect(plugin.bindEditActions).toHaveBeenCalled();
        });
    });
});
