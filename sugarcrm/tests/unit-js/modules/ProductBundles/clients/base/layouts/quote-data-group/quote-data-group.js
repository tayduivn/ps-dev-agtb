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
describe('ProductBundles.Base.Layouts.QuoteDataGroup', function() {
    var app;
    var layout;
    var layoutModel;
    var layoutContext;
    var layoutGroupId;
    var initializeOptions;
    var parentContext;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        parentContext = app.context.getContext({module: 'Quotes'});
        parentContext.prepare(true);

        layoutModel = new Backbone.Model({
            id: layoutGroupId,
            default_group: false,
            product_bundle_items: new Backbone.Collection([
                {id: 'test1', _module: 'Products', position: 0},
                {id: 'test2', _module: 'Products', position: 1},
                {id: 'test3', _module: 'Products', position: 2}
            ])
        });
        layoutGroupId = layoutModel.cid;

        layoutContext = app.context.getContext();
        layoutContext.set({
            module: 'ProductBundles',
            model: layoutModel
        });

        layoutContext.parent = parentContext;

        sinon.collection.stub(app.metadata, 'getView', function() {
            return {
                panels: [{
                    fields: [
                        'field1', 'field2', 'field3', 'field4'
                    ]
                }]
            };
        });

        initializeOptions = {
            model: layoutModel
        };

        layout = SugarTest.createLayout('base', 'ProductBundles', 'quote-data-group', null,
            layoutContext, true, initializeOptions);

        sinon.collection.stub(layout, '_super', function() {});
    });

    afterEach(function() {
        sinon.collection.restore();
        layout.dispose();
        layout = null;
    });

    describe('initialize()', function() {
        var lastCall;

        it('should have className', function() {
            expect(layout.className).toBe('quote-data-group');
        });

        it('should have tagName', function() {
            expect(layout.tagName).toBe('tbody');
        });

        it('should set the groupId from the model ID', function() {
            expect(layout.groupId).toBe(layoutGroupId);
        });

        it('should set this.collection to be the product_bundle_items', function() {
            expect(layout.collection).toBe(layoutModel.get('product_bundle_items'));
        });

        it('should add the comparator function to this.collections', function() {
            expect(layout.collection.comparator).toBeDefined();
        });

        it('should call app.metadata.getView with first param Products module', function() {
            lastCall = app.metadata.getView.lastCall;
            expect(lastCall.args[0]).toBe('Products');
        });

        it('should call app.metadata.getView with second param quote-data-group-list', function() {
            lastCall = app.metadata.getView.lastCall;
            expect(lastCall.args[1]).toBe('quote-data-group-list');
        });

        it('should set listColSpan if metadata exists', function() {
            expect(layout.listColSpan).toBe(4);
        });
    });

    describe('bindDataChange()', function() {
        it('will subscribe the model to listen for a change on product_bundle_items and call render', function() {
            sinon.collection.stub(layout.model, 'on');
            layout.bindDataChange();
            expect(layout.model.on).toHaveBeenCalledWith('change:product_bundle_items', layout.render, layout);
        });
    });

    describe('_render()', function() {
        var $elAttrSpy;
        var $attrSpy;
        var $oldEl;

        beforeEach(function() {
            $elAttrSpy = sinon.collection.spy();
            $attrSpy = sinon.collection.spy();

            $oldEl = layout.$el;
            layout.$el = {
                attr: $elAttrSpy
            };

            sinon.collection.stub(layout, '$', function() {
                return {
                    attr: $attrSpy
                };
            });

            layout._render();
        });

        afterEach(function() {
            $elAttrSpy = null;
            $attrSpy = null;
            delete layout.$el.attr;
            layout.$el = $oldEl;
            $oldEl = null;
        });

        it('should call super _render', function() {
            expect(layout._super).toHaveBeenCalledWith('_render');
        });

        it('should call $el.attr and set the data-group-id to the groupId', function() {
            expect($elAttrSpy).toHaveBeenCalledWith('data-group-id', layoutGroupId);
        });

        it('should call this.$ to get the table rows', function() {
            expect(layout.$).toHaveBeenCalledWith('tr.quote-data-group-list');
        });

        it('should call $.attr and set the data-group-id to the groupId', function() {
            expect($attrSpy).toHaveBeenCalledWith('data-group-id', layoutGroupId);
        });
    });

    describe('addRowModel()', function() {
        var rowModel;
        var rowModel2;
        beforeEach(function() {
            layout.quoteDataGroupList = {
                toggledModels: {}
            };

            rowModel = new Backbone.Model({
                id: 'rowModelId1',
                position: 0
            });
            rowModel.cid = 'rowModelId1';
            rowModel2 = new Backbone.Model({
                id: 'rowModelId2',
                position: 0
            });
            rowModel2.cid = 'rowModelId2';
            sinon.collection.spy(layout.collection, 'add');

            layout.collection.reset();
        });

        afterEach(function() {
            rowModel = null;
        });

        it('should add the model to the collection', function() {
            layout.addRowModel(rowModel, false);

            expect(layout.collection.length).toBe(1);
        });

        it('should add model to list component toggledModels if in edit', function() {
            layout.addRowModel(rowModel, true);

            expect(layout.quoteDataGroupList.toggledModels.rowModelId1).toEqual(rowModel);
        });

        it('should not add model to list component toggledModels if not in edit', function() {
            layout.addRowModel(rowModel, false);

            expect(layout.quoteDataGroupList.toggledModels.rowModelId1).toBeUndefined();
        });

        it('should add row model at the position value on the model', function() {
            layout.addRowModel(rowModel, false);
            rowModel.set('position', 1);
            layout.addRowModel(rowModel2, false);

            expect(layout.collection.models[0].get('id')).toBe('rowModelId2');
            expect(layout.collection.models[1].get('id')).toBe('rowModelId1');
        });
    });

    describe('removeRowModel()', function() {
        var rowModel;

        beforeEach(function() {
            rowModel = new Backbone.Model({
                id: 'rowModelId1'
            });
            layout.quoteDataGroupList = {
                toggledModels: {
                    rowModelId1: rowModel
                }
            };
            layout.collection.reset(rowModel);
        });

        afterEach(function() {
            rowModel = null;
        });

        it('should remove the model from the collection', function() {
            layout.removeRowModel(rowModel, false);

            expect(layout.collection.length).toBe(0);
        });

        it('should remove model from list toggledModels if in edit', function() {
            layout.removeRowModel(rowModel, true);

            expect(layout.quoteDataGroupList.toggledModels.rowModelId1).toBeUndefined();
        });
    });

    describe('addComponent()', function() {
        var components;

        beforeEach(function() {
            components = [{
                name: 'quote-data-group-list'
            }, {
                name: 'other-component'
            }];
        });

        afterEach(function() {
            components = null;
        });

        it('should set quoteDataGroupList during addComponent', function() {
            _.each(components, function(comp) {
                layout.addComponent(comp, {});
            }, this);

            expect(layout.quoteDataGroupList).toEqual(components[0]);
        });
    });

    describe('removeComponent()', function() {
        var components;

        beforeEach(function() {
            components = [{
                name: 'quote-data-group-list'
            }, {
                name: 'other-component'
            }];
        });

        afterEach(function() {
            components = null;
        });

        it('should set quoteDataGroupList during addComponent', function() {
            _.each(components, function(comp) {
                layout.removeComponent(comp, {});
            }, this);

            expect(layout.quoteDataGroupList).toBeNull();
        });
    });

    describe('_dispose()', function() {
        var quoteDataGroupList;

        beforeEach(function() {
            quoteDataGroupList = {
                name: 'quote-data-group-list'
            };
            layout.quoteDataGroupList = quoteDataGroupList;
        });

        afterEach(function() {
            quoteDataGroupList = null;
        });

        it('should set quoteDataGroupList during addComponent', function() {
            layout._dispose();

            expect(layout.quoteDataGroupList).toBeNull();
        });
    });
});
