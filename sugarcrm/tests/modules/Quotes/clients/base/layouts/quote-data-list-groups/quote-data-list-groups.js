describe('Quotes.Base.Layouts.QuoteDataListGroups', function() {
    var app;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        sinon.collection.stub(app.metadata, 'getView', function() {
            return {
                panels: [{
                    fields: [
                        'field1', 'field2', 'field3', 'field4'
                    ]
                }]
            };
        });

        sinon.collection.stub(app.metadata, 'getLayout', function() {
            return {
                name: 'ProductBundlesQuoteDataGroupMetadata'
            };
        });
        layout = SugarTest.createLayout('base', 'Quotes', 'quote-data-list-groups', null, null, true);
        sinon.collection.stub(layout, '_super', function() {});
    });

    afterEach(function() {
        sinon.collection.restore();
        layout.dispose();
        layout = null;
    });

    describe('initialize()', function() {
        it('should have className', function() {
            expect(layout.className).toBe('table dataTable quote-data-list-table');
        });

        it('should have tagName', function() {
            expect(layout.tagName).toBe('table');
        });

        it('should initialize groupIds to an empty array', function() {
            expect(layout.groupIds).toEqual([]);
        });

        it('should initialize quoteDataGroupMeta to ProductBundlesQuoteDataGroupLayout metadata', function() {
            expect(layout.quoteDataGroupMeta).toEqual({
                name: 'ProductBundlesQuoteDataGroupMetadata'
            });
        });
    });

    describe('bindDataChange()', function() {
        beforeEach(function() {
            sinon.collection.spy(layout.context, 'on');
            layout.bindDataChange();
        });

        it('quotes:group:create', function() {
            expect(layout.context.on).toHaveBeenCalledWith('quotes:group:create');
        });

        it('quotes:group:delete', function() {
            expect(layout.context.on).toHaveBeenCalledWith('quotes:group:delete');

        });
    });

    describe('_onProductBundleChange()', function() {
        var quoteData;
        var bundle1;
        var bundle2;
        var bundle3;
        beforeEach(function() {
            bundle1 = new Backbone.Model({
                id: 'testId1',
                name: 'testName1'
            });
            bundle2 = new Backbone.Model({
                id: 'testId2',
                name: 'testName2'
            });
            bundle3 = new Backbone.Model({
                id: 'testId3',
                name: 'testName3'
            });
            quoteData = new Backbone.Collection([
                bundle1,
                bundle2
            ]);

            sinon.collection.spy(layout, '_addQuoteGroupToLayout');
            sinon.collection.spy(layout, 'render');
            layout.model.set('bundles', quoteData);
        });

        it('should set this.groupIds with quoteData record IDs', function() {
            expect(layout.groupIds).toEqual(['testId1', 'testId2']);
        });

        it('should call _addQuoteGroupToLayout with new bundle 1 data', function() {
            expect(layout._addQuoteGroupToLayout).toHaveBeenCalledWith(bundle1);
        });

        it('should call _addQuoteGroupToLayout with new bundle 2 data', function() {
            expect(layout._addQuoteGroupToLayout).toHaveBeenCalledWith(bundle2);
        });

        it('should call _addQuoteGroupToLayout with only bundle 3 data', function() {
            // unset the spy to reset the called with list
            layout._addQuoteGroupToLayout.restore();
            sinon.collection.spy(layout, '_addQuoteGroupToLayout');

            quoteData = new Backbone.Collection([
                bundle1,
                bundle2,
                bundle3
            ]);

            layout.model.set('bundles', quoteData);
            expect(layout._addQuoteGroupToLayout).not.toHaveBeenCalledWith(bundle1);
            expect(layout._addQuoteGroupToLayout).not.toHaveBeenCalledWith(bundle2);
            expect(layout._addQuoteGroupToLayout).toHaveBeenCalledWith(bundle3);
        });

        it('should call render on quote_data change', function() {
            expect(layout.render).toHaveBeenCalled();
        });
    });

    describe('_addQuoteGroupToLayout()', function() {
        var initComponentsSpy;
        beforeEach(function() {
            initComponentsSpy = sinon.collection.spy();
            sinon.collection.spy(layout.context, 'getChildContext');
            sinon.collection.stub(app.view, 'createLayout', function() {
                return {
                    initComponents: initComponentsSpy
                };
            });
            sinon.collection.stub(layout, 'addComponent', function() {});

            layout._addQuoteGroupToLayout({});
        });

        it('should call layout.context.getChildContext', function() {
            expect(layout.context.getChildContext).toHaveBeenCalled({module: 'ProductBundles'});
        });

        it('should call app.view.createLayout', function() {
            expect(app.view.createLayout).toHaveBeenCalled();
        });

        it('should call group layout initComponents', function() {
            expect(initComponentsSpy).toHaveBeenCalled();
        });

        it('should call layout.addComponent', function() {
            expect(layout.addComponent).toHaveBeenCalled();
        });
    });

    describe('_onCreateQuoteGroup()', function() {
        var callArgs;
        var bundles;
        beforeEach(function() {
            bundles = new Backbone.Collection([{
                id: 'testId1',
                position: 0
            }, {
                id: 'testId2',
                position: 1
            }]);
            layout.model.set('id', 'testQuoteLayoutId');
            layout.model.set('bundles', bundles);

            sinon.collection.stub(app.alert, 'show', function() {});
            sinon.collection.stub(app.api, 'relationships', function() {});
            layout._onCreateQuoteGroup();
            callArgs = app.api.relationships.firstCall;
        });

        afterEach(function() {
            callArgs = null;
        });

        it('should call app.alert.show', function() {
            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should call app.api.relationships', function() {
            expect(app.api.relationships).toHaveBeenCalled();
        });

        it('should call app.api.relationships with method create', function() {
            expect(callArgs.args[0]).toBe('create');
        });

        it('should call app.api.relationships with module Quotes', function() {
            expect(callArgs.args[1]).toBe('Quotes');
        });

        it('should call app.api.relationships with proper link payload Quote ID', function() {
            expect(callArgs.args[2].id).toBe('testQuoteLayoutId');
        });

        it('should call app.api.relationships with proper link payload link name', function() {
            expect(callArgs.args[2].link).toBe('product_bundles');
        });

        it('should call app.api.relationships with proper link payload position', function() {
            expect(callArgs.args[2].related.position).toBe(2);
        });

        it('should call app.api.relationships with payload position of 0 when no bundles exist', function() {
            layout.model.set('bundles', new Backbone.Collection());
            layout._onCreateQuoteGroup();

            callArgs = app.api.relationships.lastCall;

            expect(callArgs.args[2].related.position).toBe(0);
        });
    });

    describe('_onCreateQuoteGroupSuccess()', function() {
        var newBundleData;
        var bundles;
        beforeEach(function() {
            bundles = new Backbone.Collection([{
                id: 'testId1',
                position: 0
            }, {
                id: 'testId2',
                position: 1
            }]);
            layout.model.set('bundles', bundles);
            newBundleData = {
                related_record: {
                    id: 'testId3',
                    position: 2,
                    product_bundle_items: []
                }
            };
            sinon.collection.stub(app.alert, 'dismiss', function() {});
            sinon.collection.stub(app.alert, 'show', function() {});
            sinon.collection.stub(layout, '_addQuoteGroupToLayout', function() {});
            sinon.collection.stub(layout, 'render', function() {});
            sinon.collection.spy(bundles, 'add');

            layout._onCreateQuoteGroupSuccess(newBundleData);
        });

        afterEach(function() {
            newBundleData = null;
        });

        it('should call app.alert.dismiss to get rid of old alert', function() {
            expect(app.alert.dismiss).toHaveBeenCalledWith('adding_bundle_alert');
        });

        it('should call app.alert.show to display new alert', function() {
            expect(app.alert.show).toHaveBeenCalledWith('added_bundle_alert');
        });

        it('should add the newBundleData to records', function() {
            expect(layout.model.get('bundles').at(2).get('id')).toBe('testId3');
        });

        it('should call add on bundles collection', function() {
            expect(bundles.add).toHaveBeenCalledWith(newBundleData.related_record);
        });
    });

    describe('_onDeleteQuoteGroupConfirm()', function() {
        var callArgs;
        var groupId;
        var groupName;
        var groupToDelete;

        beforeEach(function() {
            groupId = 'testId1';
            groupName = 'testName1';
            groupToDelete = new Backbone.Model({
                id: groupId,
                name: groupName
            });
            layout.model.set('id', 'testQuoteLayoutId');
            layout.model.set('bundles', new Backbone.Collection(groupToDelete));
            sinon.collection.stub(app.alert, 'dismiss', function() {});
            sinon.collection.stub(app.alert, 'show', function() {});
            sinon.collection.stub(app.api, 'records', function() {});

            layout._onDeleteQuoteGroupConfirm(groupId, groupName, groupToDelete);
            callArgs = app.api.records.firstCall;
        });

        afterEach(function() {
            callArgs = null;
        });

        it('should call app.alert.show', function() {
            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should call app.api.records', function() {
            expect(app.api.records).toHaveBeenCalled();
        });

        it('should call app.api.records with method delete', function() {
            expect(callArgs.args[0]).toBe('delete');
        });

        it('should call app.api.records with module Quotes', function() {
            expect(callArgs.args[1]).toBe('ProductBundles');
        });

        it('should call app.api.records with proper link payload Quote ID', function() {
            expect(callArgs.args[2].id).toBe(groupId);
        });
    });

    describe('_onDeleteQuoteGroupSuccess()', function() {
        var groupId;
        var groupToDelete;
        var disposeStub;

        beforeEach(function() {
            disposeStub = sinon.collection.stub();
            groupId = 'testId1';
            groupToDelete = {
                model: new Backbone.Model({
                    id: groupId
                }),
                dispose: disposeStub
            };
            layout.model.set('bundles', new Backbone.Collection([groupToDelete.model, {id: 'testId2'}]));
            layout.groupIds = [groupId, 'testId2'];

            sinon.collection.stub(app.alert, 'dismiss', function() {});
            sinon.collection.stub(app.alert, 'show', function() {});

            layout._onDeleteQuoteGroupSuccess(groupId, groupToDelete);
        });

        afterEach(function() {

        });

        it('should call app.alert.dismiss to get rid of old alert', function() {
            expect(app.alert.dismiss).toHaveBeenCalledWith('deleting_bundle_alert');
        });

        it('should call app.alert.show to display new alert', function() {
            expect(app.alert.show).toHaveBeenCalledWith('deleted_bundle_alert');
        });

        it('should remove the group from this.records', function() {
            var bundles = layout.model.get('bundles');
            expect(bundles.length).toBe(1);
            expect(bundles.at(0).get('id')).toBe('testId2');
        });

        it('should call groupToDelete.dispose()', function() {
            expect(disposeStub).toHaveBeenCalled();
        });
    });
});
