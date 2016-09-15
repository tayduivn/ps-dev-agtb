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
        sinon.collection.stub(layout, 'before', function() {});
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

        it('should have sortableTag', function() {
            expect(layout.sortableTag).toBe('tbody');
        });

        it('should initialize groupIds to an empty array', function() {
            expect(layout.groupIds).toEqual([]);
        });

        it('should initialize quoteDataGroupMeta to ProductBundlesQuoteDataGroupLayout metadata', function() {
            expect(layout.quoteDataGroupMeta).toEqual({
                name: 'ProductBundlesQuoteDataGroupMetadata'
            });
        });

        it('should call .before render', function() {
            layout.initialize({});
            expect(layout.before).toHaveBeenCalledWith('render');
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

    describe('_render()', function() {
        var disableSelectionStub;

        beforeEach(function() {
            disableSelectionStub = sinon.collection.stub();
            sinon.collection.stub($.fn, 'sortable', function() {
                return {
                    disableSelection: disableSelectionStub
                };
            });
        });

        afterEach(function() {
            disableSelectionStub = null;
        });

        describe('when there are no sortable items', function() {
            beforeEach(function() {
                sinon.collection.stub(layout, '$', function() {
                    return [];
                });
            });

            it('should not call sortable', function() {
                layout._render();
                expect($.fn.sortable).not.toHaveBeenCalled();
            });
        });

        describe('when there are sortable items', function() {
            beforeEach(function() {
                sinon.collection.stub(layout, '$', function() {
                    return ['<div></div>'];
                });

                layout._render();
            });

            it('should call sortable', function() {
                expect($.fn.sortable).toHaveBeenCalled();
            });

            describe('with the correct sortable params', function() {
                var callObj;
                beforeEach(function() {
                    callObj = $.fn.sortable.args[0][0];
                });

                afterEach(function() {
                    callObj = null;
                });

                it('should call with axis = "y"', function() {
                    expect(callObj.axis).toBe('y');
                });

                it('should call with connectWith = "tbody"', function() {
                    expect(callObj.connectWith).toBe('tbody');
                });

                it('should call with cursor = "move"', function() {
                    expect(callObj.cursor).toBe('move');
                });

                it('should call with helper = "clone"', function() {
                    expect(callObj.helper).toBe('clone');
                });

                it('should call with items = "tr.sortable"', function() {
                    expect(callObj.items).toBe('tr.sortable');
                });

                it('should call with out handler defined', function() {
                    expect(callObj.out).toBeDefined();
                });

                it('should call with over handler defined', function() {
                    expect(callObj.over).toBeDefined();
                });

                it('should call with placeholder = "ui-state-highlight"', function() {
                    expect(callObj.placeholder).toBe('ui-state-highlight');
                });

                it('should call with revert = true', function() {
                    expect(callObj.revert).toBeTruthy();
                });

                it('should call with start handler defined', function() {
                    expect(callObj.start).toBeDefined();
                });

                it('should call with stop handler defined', function() {
                    expect(callObj.stop).toBeDefined();
                });
            });

            it('should call disableSelection', function() {
                expect(disableSelectionStub).toHaveBeenCalled();
            });
        });
    });

    describe('_onDragStart()', function() {
        beforeEach(function() {
            sinon.collection.stub(app.tooltip, 'clear', function() {});
            sinon.collection.stub(app.tooltip, '_disable', function() {});

            layout._onDragStart();
        });

        it('should call tooltip clear', function() {
            expect(app.tooltip.clear).toHaveBeenCalled();
        });

        it('should call tooltip _disable', function() {
            expect(app.tooltip._disable).toHaveBeenCalled();
        });
    });

    describe('_onDragStop()', function() {
        var evtParam;
        var uiParam;
        var oldGroup;
        var oldGroupId;
        var oldGroupModel;
        var oldGroupTriggerSpy;
        var newGroup;
        var newGroupId;
        var newGroupModel;
        var newGroupTriggerSpy;
        var rowModelId;
        var rowModelModule;
        var rowModel;

        beforeEach(function() {
            evtParam = {};

            oldGroupTriggerSpy = sinon.collection.spy();
            newGroupTriggerSpy = sinon.collection.spy();

            oldGroupId = 'oldGroupId1';
            oldGroupModel = app.data.createBean('ProductBundles', {
                id: oldGroupId,
                name: 'oldGroupModelName_original'
            });
            oldGroup = {
                groupId: oldGroupId,
                collection: app.data.createBeanCollection('ProductBundles'),
                trigger: oldGroupTriggerSpy,
                model: oldGroupModel,
                addRowModel: function(model, isEdit) {
                    this.collection.add(model);
                },
                removeRowModel: function(model, isEdit) {
                    this.collection.remove(model);
                }
            };

            newGroupId = 'newGroupId1';
            newGroupModel = app.data.createBean('ProductBundles', {
                id: newGroupId,
                name: 'newGroupModelName_original'
            });
            newGroup = {
                groupId: newGroupId,
                collection: app.data.createBeanCollection('ProductBundles'),
                trigger: newGroupTriggerSpy,
                model: newGroupModel,
                addRowModel: function(model, isEdit) {
                    this.collection.add(model);
                },
                removeRowModel: function(model, isEdit) {
                    this.collection.remove(model);
                }
            };

            rowModelId = 'rowModelId1';
            rowModelModule = 'Products';
            rowModel = new Backbone.Model({
                id: rowModelId,
                module: rowModelModule,
                position: 2
            });
            rowModel.module = rowModelModule;

            sinon.collection.stub(app.tooltip, '_enable', function() {});
            sinon.collection.stub(layout, '_saveDefaultGroupThenCallBulk', function() {});
            sinon.collection.stub(layout, '_callBulkRequests', function() {});

            sinon.collection.stub(layout, '_getComponentByGroupId', function(id) {
                if (id === oldGroupId) {
                    return oldGroup;
                } else {
                    return newGroup;
                }
            });
            sinon.collection.stub(layout, '_updateRowPositions', function(group) {
                return {
                    data: {
                        position: 0
                    },
                    method: 'PUT',
                    url: '/v10/ProductBundles/' + group.groupId + '/link/products/rowModelId1'
                };
            });
        });

        afterEach(function() {
            evtParam = null;
            uiParam = null;
        });

        describe('when old group is different than new group', function() {
            beforeEach(function() {
                uiParam = {
                    item: {
                        get: function() {
                            return '<div data-group-id="' + oldGroupId + '"' +
                                'name="' + rowModelModule + '_' + rowModelId + '"></div>';
                        }
                    }
                };
                sinon.collection.stub($.fn, 'parent', function() {
                    return '<div data-group-id="' + newGroupId + '"></div>';
                });

                oldGroup.collection.add(rowModel);

                layout._onDragStop(evtParam, uiParam);
            });

            it('should remove the rowModel from oldGroup', function() {
                expect(oldGroup.collection.length).toBe(0);
            });

            it('should add the rowModel to newGroup', function() {
                expect(newGroup.collection.length).toBe(1);
            });

            it('should call _getComponentByGroupId with oldGroup ID', function() {
                expect(layout._getComponentByGroupId).toHaveBeenCalledWith(oldGroupId);
            });

            it('should call _getComponentByGroupId with newGroup ID', function() {
                expect(layout._getComponentByGroupId).toHaveBeenCalledWith(newGroupId);
            });

            describe('triggering group events', function() {
                describe('on oldGroup', function() {
                    it('should trigger quotes:group:changed', function() {
                        expect(oldGroupTriggerSpy.args[0][0]).toBe('quotes:group:changed');
                    });

                    it('should trigger quotes:group:save:start', function() {
                        expect(oldGroupTriggerSpy.args[1][0]).toBe('quotes:group:save:start');
                    });
                });

                describe('on newGroup', function() {
                    it('should trigger quotes:group:changed', function() {
                        expect(newGroupTriggerSpy.args[0][0]).toBe('quotes:group:changed');
                    });

                    it('should trigger quotes:group:save:start', function() {
                        expect(newGroupTriggerSpy.args[1][0]).toBe('quotes:group:save:start');
                    });
                });
            });

            describe('on save default group', function() {
                it('should call _saveDefaultGroupThenCallBulk with an unsaved group', function() {
                    newGroupModel.set('_notSaved', true);
                    // reset everything back
                    layout._saveDefaultGroupThenCallBulk.restore();
                    layout._callBulkRequests.restore();
                    sinon.collection.stub(layout, '_saveDefaultGroupThenCallBulk', function() {});
                    sinon.collection.stub(layout, '_callBulkRequests', function() {});
                    newGroup.collection.remove(rowModel);
                    oldGroup.collection.add(rowModel);

                    layout._onDragStop(evtParam, uiParam);

                    expect(layout._saveDefaultGroupThenCallBulk).toHaveBeenCalled();
                    expect(layout._callBulkRequests).not.toHaveBeenCalled();
                });

                it('should call _callBulkRequests with an regular saved group', function() {
                    expect(layout._saveDefaultGroupThenCallBulk).not.toHaveBeenCalled();
                    expect(layout._callBulkRequests).toHaveBeenCalled();
                });
            });
        });

        describe('when old group is the same as new group', function() {
            beforeEach(function() {
                uiParam = {
                    item: {
                        get: function() {
                            return '<div data-group-id="' + newGroupId + '"' +
                                'name="' + rowModelModule + '_' + rowModelId + '"></div>';
                        }
                    }
                };
                sinon.collection.stub($.fn, 'parent', function() {
                    return '<div data-group-id="' + newGroupId + '"></div>';
                });

                newGroup.collection.add(rowModel);

                layout._onDragStop(evtParam, uiParam);
            });

            it('should have the rowModel still in newGroup', function() {
                expect(newGroup.collection.length).toBe(1);
            });

            it('should not call _getComponentByGroupId with oldGroup ID', function() {
                expect(layout._getComponentByGroupId).not.toHaveBeenCalledWith(oldGroupId);
            });

            it('should call _getComponentByGroupId with newGroup ID', function() {
                expect(layout._getComponentByGroupId).toHaveBeenCalledWith(newGroupId);
            });

            describe('triggering group events', function() {
                describe('on oldGroup', function() {
                    it('should not trigger any events on the oldGroup', function() {
                        expect(oldGroupTriggerSpy).not.toHaveBeenCalled();
                    });
                });

                describe('on newGroup', function() {
                    it('should trigger quotes:group:changed', function() {
                        expect(newGroupTriggerSpy.args[0][0]).toBe('quotes:group:changed');
                    });

                    it('should trigger quotes:group:save:start', function() {
                        expect(newGroupTriggerSpy.args[1][0]).toBe('quotes:group:save:start');
                    });
                });
            });

            it('should call _callBulkRequests with an regular saved group', function() {
                expect(layout._saveDefaultGroupThenCallBulk).not.toHaveBeenCalled();
                expect(layout._callBulkRequests).toHaveBeenCalled();
            });
        });

        describe('always re-enable tooltips', function() {
            beforeEach(function() {
                uiParam = {
                    item: {
                        get: function() {
                            return '<div data-group-id="' + newGroupId + '"' +
                                'name="' + rowModelModule + '_' + rowModelId + '"></div>';
                        }
                    }
                };
                sinon.collection.stub($.fn, 'parent', function() {
                    return '<div data-group-id="' + newGroupId + '"></div>';
                });

                layout._onDragStop(evtParam, uiParam);
            });

            it('should call tooltip _enable', function() {
                expect(app.tooltip._enable).toHaveBeenCalled();
            });
        });
    });

    describe('_updateDefaultGroupWithNewData()', function() {
        var group;
        var recordData;
        var elAttrSpy;
        var trAttrSpy;

        beforeEach(function() {
            elAttrSpy = sinon.collection.spy();
            trAttrSpy = sinon.collection.spy();
            group = {
                model: app.data.createBean('ProductBundle'),
                $el: {
                    attr: elAttrSpy
                },
                $: function() {
                    return {
                        attr: trAttrSpy
                    };
                }
            };
            recordData = {
                id: 'newId1',
                test: 'abc'
            };
            layout.groupIds = ['oldGroupId1'];
            layout.defaultGroupId = 'oldGroupId1';
            layout._updateDefaultGroupWithNewData(group, recordData);
        });

        afterEach(function() {
            group = null;
            recordData = null;
            elAttrSpy = null;
            trAttrSpy = null;
        });

        it('should update groupIds with the new group ID', function() {
            expect(layout.groupIds).toEqual(['newId1']);
        });

        it('should set the defaultGroupId to be the new group ID', function() {
            expect(layout.defaultGroupId).toBe('newId1');
        });

        it('should set recordData on the model', function() {
            expect(group.model.get('id')).toBe('newId1');
            expect(group.model.get('test')).toBe('abc');
        });

        it('should set the group groupId to be the new group ID', function() {
            expect(group.groupId).toBe('newId1');
        });

        it('should call attr function on $el to update the group ID', function() {
            expect(elAttrSpy).toHaveBeenCalled();
        });

        it('should call attr function on any tr elements to update the group ID', function() {
            expect(trAttrSpy).toHaveBeenCalled();
        });
    });

    describe('_saveDefaultGroupThenCallBulk', function() {
        var oldGroup;
        var newGroup;
        var newGroupId;
        var newGroupModel;
        var bulkSaveRequests;
        var layoutModelId;
        var appApiCallArgs;

        beforeEach(function() {
            oldGroup = {};
            newGroupId = 'testGroupId1';
            newGroupModel = app.data.createBean('ProductBundles', {
                id: newGroupId,
                _notSaved: true,
                name: 'New Test Group'
            });
            newGroup = {
                model: newGroupModel
            };
            bulkSaveRequests = [];
            layoutModelId = 'layoutModelId1';

            sinon.collection.stub(app.api, 'relationships', function() {});

            layout.model.set('id', layoutModelId);
            layout._saveDefaultGroupThenCallBulk(oldGroup, newGroup, bulkSaveRequests);
            appApiCallArgs = app.api.relationships.args[0];
        });

        afterEach(function() {
            oldGroup = null;
            newGroup = null;
            newGroupModel = null;
            bulkSaveRequests = null;
            layoutModelId = null;
            appApiCallArgs = null;
        });

        it('should unset the group id', function() {
            expect(newGroupModel.has('id')).toBeFalsy();
        });

        it('should unset the _notSaved flag', function() {
            expect(newGroupModel.has('_notSaved')).toBeFalsy();
        });

        it('should call app.api.relationships with method "create"', function() {
            expect(appApiCallArgs[0]).toBe('create');
        });

        it('should call app.api.relationships with module "Quotes"', function() {
            expect(appApiCallArgs[1]).toBe('Quotes');
        });

        describe('with proper payload', function() {
            it('should use layout model id', function() {
                expect(appApiCallArgs[2].id).toBe(layoutModelId);
            });

            it('should use product_bundles as link name', function() {
                expect(appApiCallArgs[2].link).toBe('product_bundles');
            });

            it('should have related object', function() {
                expect(appApiCallArgs[2].related.position).toBe(0);
                expect(appApiCallArgs[2].related.name).toBe('New Test Group');
            });
        });
    });

    describe('_onDefaultGroupSaveSuccess', function() {
        var oldGroup;
        var newGroup;
        var newGroupModel;
        var bulkSaveRequests;
        var newGroupOldId;
        var serverData;
        var newGroupSavedId;

        beforeEach(function() {
            oldGroup = {};
            newGroupModel = app.data.createBean('ProductBundles', {
                id: ''
            });
            newGroup = {
                model: newGroupModel,
                groupId: newGroupOldId
            };
            newGroupOldId = 'oldGroupId1';
            newGroupSavedId = 'savedGroupId2';
            serverData = {
                related_record: {
                    id: newGroupSavedId,
                    test: true
                }
            };
            bulkSaveRequests = [{
                url: 'v10/ProductBundles/' + newGroupOldId + '/stuff'
            }];
            sinon.collection.stub(layout, '_callBulkRequests', function() {});
            sinon.collection.stub(layout, '_updateDefaultGroupWithNewData', function() {});

            layout._onDefaultGroupSaveSuccess(oldGroup, newGroup, bulkSaveRequests, newGroupOldId, serverData);
        });

        afterEach(function() {
            oldGroup = null;
            newGroup = null;
            bulkSaveRequests = null;
            newGroupOldId = null;
            newGroupSavedId = null;
            serverData = null;
        });

        it('should update old group id in requests to new group id', function() {
            expect(bulkSaveRequests[0].url).toBe('v10/ProductBundles/' + newGroupSavedId + '/stuff');
        });

        it('should call _updateDefaultGroupWithNewData to update the group', function() {
            expect(layout._updateDefaultGroupWithNewData).toHaveBeenCalled();
        });

        it('should call _callBulkRequests', function() {
            expect(layout._callBulkRequests).toHaveBeenCalled();
        });
    });

    describe('_callBulkRequests', function() {
        var bulkSaveRequests;
        beforeEach(function() {
            bulkSaveRequests = [{
                url: 'testUrl'
            }];
            sinon.collection.stub(app.api, 'call', function() {});

            layout._callBulkRequests({}, {}, bulkSaveRequests);
        });

        afterEach(function() {
            bulkSaveRequests = null;
        });

        it('should call with method create', function() {
            expect(app.api.call.args[0][0]).toBe('create');
        });

        it('should call with bulk url', function() {
            var url = app.api.call.args[0][1].split('/');
            expect(_.last(url)).toBe('bulk');
        });

        it('should call with requests array', function() {
            expect(app.api.call.args[0][2].requests).toBeDefined();
        });

        it('should have correct requests length', function() {
            expect(app.api.call.args[0][2].requests.length).toBe(1);
        });
    });

    describe('_onSaveUpdatedGroupSuccess()', function() {
        var oldGroup;
        var oldGroupId;
        var oldGroupModel;
        var oldGroupTriggerSpy;
        var newGroup;
        var newGroupId;
        var newGroupModel;
        var newGroupTriggerSpy;
        var bulkResponses;
        var bulkOldModelUpdate;
        var bulkNewModelUpdate;
        var oldProductGroupModel;
        beforeEach(function() {
            oldGroupTriggerSpy = sinon.collection.spy();
            newGroupTriggerSpy = sinon.collection.spy();

            oldProductGroupModel = app.data.createBean('ProductBundles', {
                id: 'oldProductGroupModelId'
            });
            sinon.collection.spy(oldProductGroupModel, 'setSyncedAttributes');

            oldGroupId = 'oldGroupId1';
            oldGroupModel = app.data.createBean('Products', {
                id: 'oldGroupModelId1',
                name: 'oldGroupModelName_original'
            });
            sinon.collection.spy(oldGroupModel, 'setSyncedAttributes');

            oldGroup = {
                groupId: oldGroupId,
                model: oldProductGroupModel,
                collection: app.data.createMixedBeanCollection(),
                trigger: oldGroupTriggerSpy
            };

            newGroupId = 'newGroupId1';
            newGroupModel = app.data.createBean('ProductBundles', {
                id: 'newGroupModelId1',
                name: 'newGroupModelName_original'
            });
            sinon.collection.spy(newGroupModel, 'setSyncedAttributes');
            newGroup = {
                groupId: newGroupId,
                collection: app.data.createMixedBeanCollection(),
                trigger: newGroupTriggerSpy
            };

            oldGroup.collection.add(oldGroupModel);
            newGroup.collection.add(newGroupModel);

            bulkOldModelUpdate = {
                contents: {
                    record: {
                        id: 'oldProductGroupModelId'
                    },
                    related_record: {
                        id: 'oldGroupModelId1',
                        name: 'oldGroupModelName_new'
                    }
                }
            };
            bulkNewModelUpdate = {
                contents: {
                    record: {},
                    related_record: {
                        id: 'newGroupModelId1',
                        name: 'newGroupModelName_new'
                    }
                }
            };
            bulkResponses = [
                bulkOldModelUpdate,
                bulkNewModelUpdate
            ];
        });

        describe('when oldGroup is not sent', function() {
            beforeEach(function() {
                layout._onSaveUpdatedGroupSuccess(undefined, newGroup, bulkResponses);
            });

            it('should not trigger quotes:group:save:stop if oldGroup is not passed in', function() {
                expect(oldGroupTriggerSpy).not.toHaveBeenCalled();
            });

            it('should always trigger quotes:group:save:stop on newGroup', function() {
                expect(newGroupTriggerSpy).toHaveBeenCalled();
            });

            it('should not update old group', function() {
                expect(oldGroupModel.get('name')).toBe('oldGroupModelName_original');
                expect(oldGroupModel.setSyncedAttributes).not.toHaveBeenCalled();
                expect(oldProductGroupModel.setSyncedAttributes).not.toHaveBeenCalled();
            });

            it('should update the new group records', function() {
                expect(newGroupModel.get('name')).toBe('newGroupModelName_new');
                expect(newGroupModel.setSyncedAttributes).toHaveBeenCalled();
            });
        });

        describe('when oldGroup is sent', function() {
            beforeEach(function() {
                layout._onSaveUpdatedGroupSuccess(oldGroup, newGroup, bulkResponses);
            });

            it('should trigger quotes:group:save:stop on oldGroup', function() {
                expect(oldGroupTriggerSpy).toHaveBeenCalled();
            });

            it('should always trigger quotes:group:save:stop on newGroup', function() {
                expect(newGroupTriggerSpy).toHaveBeenCalled();
            });

            it('should update the old group', function() {
                expect(oldGroupModel.get('name')).toBe('oldGroupModelName_new');
                expect(oldGroupModel.setSyncedAttributes).toHaveBeenCalled();
                expect(oldProductGroupModel.setSyncedAttributes).toHaveBeenCalled();
            });

            it('should update the new group records', function() {
                expect(newGroupModel.get('name')).toBe('newGroupModelName_new');
                expect(newGroupModel.setSyncedAttributes).toHaveBeenCalled();
            });
        });
    });

    describe('_updateRowPositions()', function() {
        var dataGroup;
        var rowModel1;
        var rowModel2;
        var rowModel3;
        var rowModel4;
        var results;
        var callUrl;

        beforeEach(function() {
            dataGroup = {
                groupId: 'dataGroupId1',
                collection: new Backbone.Collection(),
                $: function() {
                    return [
                        '<tr name="Products_qliId1"></tr>',
                        '<tr name="ProductBundleNotes_pbnId2"></tr>',
                        '<tr name="Products_qliId3"></tr>',
                        '<tr name="ProductBundleNotes_pbnId4"></tr>'
                    ];
                }
            };
            rowModel1 = new Backbone.Model({
                id: 'qliId1',
                module: 'Products',
                position: -1
            });
            rowModel2 = new Backbone.Model({
                id: 'pbnId2',
                module: 'ProductBundleNotes',
                position: -1
            });
            rowModel3 = new Backbone.Model({
                id: 'qliId3',
                module: 'Products',
                position: -1
            });
            rowModel4 = new Backbone.Model({
                id: 'pbnId4',
                module: 'ProductBundleNotes',
                position: -1
            });
        });

        afterEach(function() {
            dataGroup = null;
            rowModel1 = null;
            rowModel2 = null;
            rowModel3 = null;
            rowModel4 = null;
            results = null;
            callUrl = null;
        });

        describe('full row position updates', function() {
            beforeEach(function() {
                dataGroup.collection.add(rowModel1);
                dataGroup.collection.add(rowModel2);
                dataGroup.collection.add(rowModel3);
                dataGroup.collection.add(rowModel4);

                results = layout._updateRowPositions(dataGroup);
            });

            describe('first call', function() {
                it('should be called with method PUT', function() {
                    expect(results[0].method).toBe('PUT');
                });

                it('should be called with data position 0', function() {
                    expect(results[0].data.position).toBe(0);
                });

                it('should have properly formatted endpoint url', function() {
                    callUrl = results[0].url.split('/');
                    callUrl = _.without(callUrl, '.', '..', 'rest', 'v10');

                    expect(callUrl[0]).toBe('ProductBundles');
                    expect(callUrl[1]).toBe('dataGroupId1');
                    expect(callUrl[2]).toBe('link');
                    expect(callUrl[3]).toBe('products');
                    expect(callUrl[4]).toBe('qliId1');
                });
            });

            describe('second call', function() {
                it('should be called with method PUT', function() {
                    expect(results[1].method).toBe('PUT');
                });

                it('should be called with data position 1', function() {
                    expect(results[1].data.position).toBe(1);
                });

                it('should have properly formatted endpoint url', function() {
                    callUrl = results[1].url.split('/');
                    callUrl = _.without(callUrl, '.', '..', 'rest', 'v10');

                    expect(callUrl[0]).toBe('ProductBundles');
                    expect(callUrl[1]).toBe('dataGroupId1');
                    expect(callUrl[2]).toBe('link');
                    expect(callUrl[3]).toBe('product_bundle_notes');
                    expect(callUrl[4]).toBe('pbnId2');
                });
            });

            describe('third call', function() {
                it('should be called with method PUT', function() {
                    expect(results[2].method).toBe('PUT');
                });

                it('should be called with data position 0', function() {
                    expect(results[2].data.position).toBe(2);
                });

                it('should have properly formatted endpoint url', function() {
                    callUrl = results[2].url.split('/');
                    callUrl = _.without(callUrl, '.', '..', 'rest', 'v10');

                    expect(callUrl[0]).toBe('ProductBundles');
                    expect(callUrl[1]).toBe('dataGroupId1');
                    expect(callUrl[2]).toBe('link');
                    expect(callUrl[3]).toBe('products');
                    expect(callUrl[4]).toBe('qliId3');
                });
            });

            describe('fourth call', function() {
                it('should be called with method PUT', function() {
                    expect(results[3].method).toBe('PUT');
                });

                it('should be called with data position 3', function() {
                    expect(results[3].data.position).toBe(3);
                });

                it('should have properly formatted endpoint url', function() {
                    callUrl = results[3].url.split('/');
                    callUrl = _.without(callUrl, '.', '..', 'rest', 'v10');

                    expect(callUrl[0]).toBe('ProductBundles');
                    expect(callUrl[1]).toBe('dataGroupId1');
                    expect(callUrl[2]).toBe('link');
                    expect(callUrl[3]).toBe('product_bundle_notes');
                    expect(callUrl[4]).toBe('pbnId4');
                });
            });
        });

        describe('swap two row positions', function() {
            beforeEach(function() {
                rowModel1.set('position', 0);
                // switched rowModel2 and rowModel3's position
                rowModel2.set('position', 2);
                rowModel3.set('position', 1);
                rowModel4.set('position', 3);

                dataGroup.collection.add(rowModel1);
                dataGroup.collection.add(rowModel2);
                dataGroup.collection.add(rowModel3);
                dataGroup.collection.add(rowModel4);

                results = layout._updateRowPositions(dataGroup);
            });

            describe('first call', function() {
                it('should be called with method PUT', function() {
                    expect(results[0].method).toBe('PUT');
                });

                it('should be called with data position 0', function() {
                    expect(results[0].data.position).toBe(1);
                });

                it('should have properly formatted endpoint url', function() {
                    callUrl = results[0].url.split('/');
                    callUrl = _.without(callUrl, '.', '..', 'rest', 'v10');

                    expect(callUrl[0]).toBe('ProductBundles');
                    expect(callUrl[1]).toBe('dataGroupId1');
                    expect(callUrl[2]).toBe('link');
                    expect(callUrl[3]).toBe('product_bundle_notes');
                    expect(callUrl[4]).toBe('pbnId2');
                });
            });

            describe('second call', function() {
                it('should be called with method PUT', function() {
                    expect(results[1].method).toBe('PUT');
                });

                it('should be called with data position 2', function() {
                    expect(results[1].data.position).toBe(2);
                });

                it('should have properly formatted endpoint url', function() {
                    callUrl = results[1].url.split('/');
                    callUrl = _.without(callUrl, '.', '..', 'rest', 'v10');

                    expect(callUrl[0]).toBe('ProductBundles');
                    expect(callUrl[1]).toBe('dataGroupId1');
                    expect(callUrl[2]).toBe('link');
                    expect(callUrl[3]).toBe('products');
                    expect(callUrl[4]).toBe('qliId3');
                });
            });
        });
    });

    describe('_getComponentByGroupId()', function() {
        var group;
        var result;
        beforeEach(function() {
            group = {
                name: 'quote-data-group',
                groupId: 'groupId1'
            };
            layout._components = [group];
        });

        afterEach(function() {
            group = null;
            result = null;
            layout._components = [];
        });

        it('should return the group if given a correct id', function() {
            result = layout._getComponentByGroupId('groupId1');
            expect(result).toBe(group);
        });

        it('should return undefined if group is not found', function() {
            result = layout._getComponentByGroupId('groupId2');
            expect(result).toBe(undefined);
        });
    });

    describe('_onGroupDragTriggerOver()', function() {
        var evtParam;
        var uiParam;
        var group;
        var groupTriggerSpy;
        beforeEach(function() {
            evtParam = {
                target: '<div data-group-id="groupId1"></div>'
            };
            uiParam = {};
            groupTriggerSpy = sinon.collection.spy();
            group = {
                trigger: groupTriggerSpy
            };
        });

        afterEach(function() {
            evtParam = null;
            uiParam = null;
            groupTriggerSpy = null;
            group = null;
        });

        it('should trigger quotes:sortable:over on the group', function() {
            sinon.collection.stub(layout, '_getComponentByGroupId', function() {
                return group;
            });
            layout._onGroupDragTriggerOver(evtParam, uiParam);

            expect(groupTriggerSpy).toHaveBeenCalledWith('quotes:sortable:over');
        });

        it('should not trigger quotes:sortable:over if no group is found', function() {
            sinon.collection.stub(layout, '_getComponentByGroupId', function() {
                return undefined;
            });
            layout._onGroupDragTriggerOver(evtParam, uiParam);

            expect(groupTriggerSpy).not.toHaveBeenCalled();
        });
    });

    describe('_onGroupDragTriggerOut()', function() {
        var evtParam;
        var uiParam;
        var group;
        var groupTriggerSpy;
        beforeEach(function() {
            evtParam = {
                target: '<div data-group-id="groupId1"></div>'
            };
            uiParam = {};
            groupTriggerSpy = sinon.collection.spy();
            group = {
                trigger: groupTriggerSpy
            };
        });

        afterEach(function() {
            evtParam = null;
            uiParam = null;
            groupTriggerSpy = null;
            group = null;
        });

        it('should trigger quotes:sortable:over on the group', function() {
            sinon.collection.stub(layout, '_getComponentByGroupId', function() {
                return group;
            });
            layout._onGroupDragTriggerOut(evtParam, uiParam);

            expect(groupTriggerSpy).toHaveBeenCalledWith('quotes:sortable:out');
        });

        it('should not trigger quotes:sortable:over if no group is found', function() {
            sinon.collection.stub(layout, '_getComponentByGroupId', function() {
                return undefined;
            });
            layout._onGroupDragTriggerOut(evtParam, uiParam);

            expect(groupTriggerSpy).not.toHaveBeenCalled();
        });
    });

    describe('beforeRender()', function() {
        beforeEach(function() {
            sinon.collection.stub(layout, '$', function() {
                return [
                    '<div class="ui-sortable"></div>'
                ];
            });
            sinon.collection.stub($.fn, 'sortable', function() {});
            layout.beforeRender();
        });

        it('should call sortable("destoy") on groups with sortable', function() {
            expect($.fn.sortable).toHaveBeenCalledWith('destroy');
        });
    });

    describe('_onProductBundleChange()', function() {
        var quoteData;
        var defaultBundle;
        var bundle1;
        var bundle2;
        var bundle3;
        beforeEach(function() {
            defaultBundle = app.data.createBean('ProductBundles', {
                id: 'defaultId1',
                name: 'defaultName1',
                default_group: true
            });
            bundle1 = app.data.createBean('ProductBundles', {
                id: 'testId1',
                name: 'testName1'
            });
            bundle2 = app.data.createBean('ProductBundles', {
                id: 'testId2',
                name: 'testName2'
            });
            bundle3 = app.data.createBean('ProductBundles', {
                id: 'testId3',
                name: 'testName3'
            });
            quoteData = app.data.createBeanCollection('ProductBundles', [
                defaultBundle,
                bundle1,
                bundle2
            ]);

            sinon.collection.spy(layout, '_addQuoteGroupToLayout');
            sinon.collection.spy(layout, 'render');
            layout.model.set('bundles', quoteData);
        });

        it('should set this.groupIds with quoteData record IDs', function() {
            expect(layout.groupIds).toEqual(['defaultId1', 'testId1', 'testId2']);
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

            quoteData = app.data.createBeanCollection('ProductBundles', [
                defaultBundle,
                bundle1,
                bundle2,
                bundle3
            ]);

            layout.model.set('bundles', quoteData);
            expect(layout._addQuoteGroupToLayout).not.toHaveBeenCalledWith(defaultBundle);
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
            layout.model.set({
                currency_id: 'currency_id_1',
                base_rate: '50.37'
            });

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

        it('should call app.api.relationships with proper link payload currency', function() {
            expect(callArgs.args[2].related.currency_id).toBe('currency_id_1');
            expect(callArgs.args[2].related.base_rate).toBe('50.37');
        });

        it('should call app.api.relationships with payload position of 1 when no bundles exist', function() {
            // because default group is 0, any new create could get a position of 1
            layout.model.set('bundles', new Backbone.Collection());
            layout._onCreateQuoteGroup();

            callArgs = app.api.relationships.lastCall;

            expect(callArgs.args[2].related.position).toBe(1);
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
            expect(layout.model.get('bundles').at(3).get('id')).toBe('testId3');
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

            layout.model.off('change:bundles', null, layout);
            layout.model.set('bundles', new Backbone.Collection([
                groupToDelete.model,
                {id: 'testId2'}
            ]));
            layout.groupIds = [groupId, 'testId2'];

            sinon.collection.stub(app.alert, 'dismiss', function() {});
            sinon.collection.stub(app.alert, 'show', function() {});

            layout._onDeleteQuoteGroupSuccess(groupId, groupToDelete);
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

    describe('_dispose()', function() {
        beforeEach(function() {
            sinon.collection.stub(layout, 'beforeRender', function() {});
        });

        it('should call beforeRender', function() {
            layout._dispose();

            expect(layout.beforeRender).toHaveBeenCalled();
        });
    });
});
