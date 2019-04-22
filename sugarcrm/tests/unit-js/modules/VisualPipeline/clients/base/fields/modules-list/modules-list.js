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

describe('VisualPipeline.Base.Fields.ModulesListField', function() {
    var app;
    var sandbox;
    var context;
    var model;
    var moduleName;
    var field;
    var options;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        moduleName = 'Opportunities';
        model = app.data.createBean(moduleName, {
            id: '123test',
            name: 'Lórem ipsum dolor sit àmêt, ut úsu ómnés tatión imperdiet.'
        });

        context = new app.Context();
        context.set({model: model});

        field = SugarTest.createField('base', 'modules-list', 'modules-list',
            'detail', {}, 'VisualPipeline', model, context, true);
        sinon.collection.stub(field, '_super', function() {});
    });

    afterEach(function() {
        sandbox.restore();
        sinon.collection.restore();
        app = null;
        context = null;
        model = null;
        field = null;
        moduleName = null;
        options = null;
    });

    describe('initialize', function() {
        it('should call field._super method with initialize', function() {
            options = {
                def: {
                    name: 'test'
                }
            };
            field.initialize(options);

            expect(field._super).toHaveBeenCalledWith('initialize', [options]);
        });

        describe('when options.def.name is enabled_modules', function() {
            beforeEach(function() {
                options = {
                    def: {
                        name: 'enabled_modules'
                    }
                };
                sinon.collection.stub(field.context, 'get', function() {
                    return {
                        test: 'test'
                    };
                });
                field.initialize(options);
            });

            it('should call field.context.get method with allowedModules', function() {

                expect(field.context.get).toHaveBeenCalledWith('allowedModules');
            });

            it('should assign allowed modules to field.items', function() {

                expect(field.items).toEqual({test: 'test'});
            });
        });

        describe('when options.def.name is tile_body_fields', function() {
            beforeEach(function() {
                options = {
                    def: {
                        name: 'tile_body_fields'
                    }
                };
                sinon.collection.stub(field.model, 'get', function() {
                    return {
                        fields: {
                            testField: 'testField'
                        }
                    };
                });
                field.initialize(options);
            });

            it('should call field.context.get method with allowedModules', function() {

                expect(field.model.get).toHaveBeenCalledWith('tabContent');
            });

            it('should assign allowed modules to field.items', function() {

                expect(field.items).toEqual({
                    testField: 'testField'
                });
            });
        });
    });

    describe('_render', function() {
        beforeEach(function() {
            sinon.collection.stub(field, 'attachEvents');
        });

        it('should call field._super method wtih _render', function() {
            field._render();

            expect(field._super).toHaveBeenCalledWith('_render');
        });

        describe('when field.name is enabled_modules', function() {
            it('should call field.attachEvents', function() {
                field.name = 'enabled_modules';
                field._render();

                expect(field.attachEvents).toHaveBeenCalled();
            });
        });

        describe('when field.name is not enabled_modules', function() {
            it('should not call field.attachEvents', function() {
                field.name = 'not_enabled_modules';
                field._render();

                expect(field.attachEvents).not.toHaveBeenCalled();
            });
        });

    });

    describe('attachEvents', function() {
        beforeEach(function() {
            sinon.collection.stub(field, '_handleRemoveItemFromCollection');
            sinon.collection.stub(field, '_handleAddItemToCollection');
            sinon.collection.stub(field.$el, 'on', function() {});
            field.fieldTag = 'test.tag';
            field.attachEvents();
        });

        it('should call $el.on with select2-removed', function() {

            expect(field.$el.on).toHaveBeenCalledWith('select2-removed', field.handleRemoveItemHandler);
        });

        it('should call field.$() with select2-selecting', function() {

            expect(field.$el.on).toHaveBeenCalledWith('select2-selecting', field.handleAddItemHandler);
        });
    });

    describe('_handleRemoveItemFromCollection', function() {
        var evt;
        beforeEach(function() {
            evt = {
                preventDefault: $.noop,
            };

            sinon.collection.stub(field.context, 'trigger', function() {});
        });

        describe('when evt.val is empty', function() {
            it('should not call field.context.trigger', function() {
                evt.val = {};
                field._handleRemoveItemFromCollection(evt);

                expect(field.context.trigger).not.toHaveBeenCalled();
            });
        });

        describe('when evt.val is not empty', function() {
            it('should not call field.context.trigger', function() {
                evt.val = {test: 'test'};
                field._handleRemoveItemFromCollection(evt);

                expect(field.context.trigger).toHaveBeenCalledWith('pipeline:config:model:remove', {test: 'test'});
            });
        });
    });

    describe('_handleAddItemToCollection', function() {
        var evt;
        beforeEach(function() {
            evt = {
                preventDefault: $.noop,
            };

            sinon.collection.stub(field.context, 'trigger', function() {});
        });

        describe('when evt.val is empty', function() {
            it('should not call field.context.trigger', function() {
                evt.val = {};
                field._handleAddItemToCollection(evt);

                expect(field.context.trigger).not.toHaveBeenCalled();
            });
        });

        describe('when evt.val is not empty', function() {
            it('should not call field.context.trigger', function() {
                evt.val = {test: 'test'};
                field._handleAddItemToCollection(evt);

                expect(field.context.trigger).toHaveBeenCalledWith('pipeline:config:model:add', {test: 'test'});
            });
        });
    });

    describe('_dispose', function() {
        beforeEach(function() {
            sinon.collection.stub(field.$el, 'off', function() {});

            field._dispose();
        });

        it('should call view.$el.off method with scroll', function() {

            expect(field.$el.off).toHaveBeenCalledWith('select2-removed');
            expect(field.$el.off).toHaveBeenCalledWith('select2-selecting');
        });

        it('should call view._super with _dispose', function() {

            expect(field._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
