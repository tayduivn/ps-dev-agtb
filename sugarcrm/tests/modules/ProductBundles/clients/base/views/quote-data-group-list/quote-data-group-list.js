describe('ProductBundles.Base.Views.QuoteDataGroupList', function() {
    var app;
    var view;
    var viewMeta;
    var viewContext;
    var viewContextOnSpy;
    var viewLayoutModel;
    var layout;
    var layoutDefs;
    var pbnMetadata;
    var prodMetadata;

    beforeEach(function() {
        app = SugarTest.app;
        viewLayoutModel = new Backbone.Model({
            product_bundle_items: new Backbone.Collection([
                {id: 'test1', _module: 'Products', position: 0},
                {id: 'test2', _module: 'Products', position: 1},
                {id: 'test3', _module: 'Products', position: 2}
            ])
        });
        layoutDefs = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]
        };
        layout = SugarTest.createLayout('base', 'ProductBundles', 'default', layoutDefs);
        layout.model = viewLayoutModel;
        layout.listColSpan = 3;
        viewMeta = {
            selection: {
                type: 'multi',
                actions: [
                    {
                        name: 'edit_row_button',
                        type: 'button'
                    },
                    {
                        name: 'delete_row_button',
                        type: 'button'
                    }
                ]
            }
        };

        pbnMetadata = {
            panels: [{
                fields: [{
                    name: 'description',
                    rows: 3
                }]
            }]
        };

        prodMetadata = {
            panels: [{
                fields: [
                    'field1', 'field2', 'field3', 'field4'
                ]
            }]
        };

        viewContext = app.context.getContext();
        viewContext.set({
            module: 'Quotes'
        });
        viewContext.prepare();

        viewContextOnSpy = sinon.collection.stub(viewContext, 'on', function() {});

        sinon.collection.stub(app.metadata, 'getView')
            .withArgs('ProductBundleNotes').returns(pbnMetadata)
            .withArgs('Products').returns(prodMetadata);

        view = SugarTest.createView('base', 'ProductBundles', 'quote-data-group-list',
            viewMeta, viewContext, true, layout);
        sinon.collection.stub(view, 'setElement');
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        it('should have the same model as the layout', function() {
            expect(view.model).toBe(viewLayoutModel);
        });

        it('should set listColSpan to be the layout listColSpan', function() {
            expect(view.listColSpan).toBe(layout.listColSpan);
        });

        it('should set el to be the layout el', function() {
            expect(view.el).toBe(layout.el);
        });

        it('should set collection based on product_bundle_items from the model', function() {
            expect(view.collection.length).toBe(3);
        });

        describe('setting fields', function() {
            var viewModel;
            beforeEach(function() {
                viewContextOnSpy.reset();
                viewModel = new Backbone.Model({
                    id: 'viewId1'
                });
                view.initialize({
                    context: viewContext,
                    meta: viewMeta,
                    model: viewModel,
                    layout: {
                        listColSpan: 2
                    }
                });
            });

            it('should add listener on context for quotes:group:create:qli:<modelId>', function() {
                expect(view.context.on.args[0][0]).toBe('quotes:group:create:qli:viewId1');
            });

            it('should add listener on context for quotes:group:create:note:<modelId>', function() {
                expect(view.context.on.args[1][0]).toBe('quotes:group:create:note:viewId1');
            });

            it('should add listener on context for editablelist:cancel:<modelId>', function() {
                expect(view.context.on.args[2][0]).toBe('editablelist:cancel:viewId1');
            });

            it('should add listener on context for editablelist:save:<modelId>', function() {
                expect(view.context.on.args[3][0]).toBe('editablelist:save:viewId1');
            });

            it('should call setElement', function() {
                expect(view.setElement).toHaveBeenCalled();
            });

            it('should set this.pbnListMetadata to be the ProductBundleNotes metadata', function() {
                expect(view.pbnListMetadata).toBe(pbnMetadata);
            });

            it('should set this.qliListMetadata to be the Products metadata', function() {
                expect(view.qliListMetadata).toBe(prodMetadata);
            });

            it('should initialize newIdsToSave', function() {
                expect(view.newIdsToSave).toEqual([]);
            });

            it('should initialize pbnDescriptionMetadata', function() {
                expect(view.pbnDescriptionMetadata).toEqual({
                    name: 'description',
                    rows: 3
                });
            });

            it('should set this._fields to be the Products metadata fields', function() {
                expect(view._fields).toEqual(view.qliListMetadata.panels[0].fields);
            });

            describe('setting leftColumns', function() {
                it('should be type fieldset', function() {
                    expect(view.leftColumns[0].type).toBe('fieldset');
                });

                it('should have one item in fields', function() {
                    expect(view.leftColumns[0].fields.length).toBe(1);
                });

                it('should have two buttons in fields', function() {
                    expect(view.leftColumns[0].fields[0].buttons.length).toBe(2);
                });

                it('should have buttons of type quote-data-actionmenu', function() {
                    expect(view.leftColumns[0].fields[0].type).toBe('quote-data-actionmenu');
                });

                it('should have first button in fields is edit', function() {
                    expect(view.leftColumns[0].fields[0].buttons[0].name).toBe('edit_row_button');
                    expect(view.leftColumns[0].fields[0].buttons[0].type).toBe('button');
                });

                it('should have second button in fields is edit', function() {
                    expect(view.leftColumns[0].fields[0].buttons[1].name).toBe('delete_row_button');
                    expect(view.leftColumns[0].fields[0].buttons[1].type).toBe('button');
                });
            });

            describe('setting leftSaveCancelColumn', function() {
                it('should be type fieldset', function() {
                    expect(view.leftSaveCancelColumn[0].type).toBe('fieldset');
                });

                it('should have two items in fields', function() {
                    expect(view.leftSaveCancelColumn[0].fields.length).toBe(2);
                });

                it('should have first button in fields is cancel', function() {
                    expect(view.leftSaveCancelColumn[0].fields[0].name).toBe('inline-cancel');
                    expect(view.leftSaveCancelColumn[0].fields[0].type).toBe('quote-data-editablelistbutton');
                    expect(view.leftSaveCancelColumn[0].fields[0].icon).toBe('fa-close');
                });

                it('should have second button in fields is save', function() {
                    expect(view.leftSaveCancelColumn[0].fields[1].name).toBe('inline-save');
                    expect(view.leftSaveCancelColumn[0].fields[1].type).toBe('quote-data-editablelistbutton');
                    expect(view.leftSaveCancelColumn[0].fields[1].icon).toBe('fa-check-circle');
                });
            });
        });
    });

    describe('bindDataChange()', function() {
        it('will add a listener to the collection for add', function() {
            sinon.collection.stub(view.collection, 'on');
            view.bindDataChange();
            expect(view.collection.on).toHaveBeenCalledWith('add', view.setupSugarLogicForModel, view);
        });
    });

    describe('_getSugarLogicDependenciesForModel()', function() {
        var model;
        beforeEach(function() {
            model = new Backbone.Model();
            model.module = 'Products';
        });

        afterEach(function() {
            model = null;
        });

        it('will find the module in metadata and return the dependencies', function() {
            sinon.collection.stub(app.metadata, 'getModule', function() {
                return {
                    dependencies: [{dependency: true}]
                };
            });
            var dep = view._getSugarLogicDependenciesForModel(model);
            expect(app.metadata.getModule).toHaveBeenCalledWith('Products');

            expect(dep.length).toEqual(1);
        });

        it('will load any dependencies from the record view', function() {
            sinon.collection.stub(app.metadata, 'getModule', function() {
                return {
                    dependencies: [{dependency: true}],
                    views: {
                        record: {
                            meta: {
                                dependencies: [{dependency1: true}]
                            }
                        }
                    }
                };
            });

            var dep = view._getSugarLogicDependenciesForModel(model);
            expect(app.metadata.getModule).toHaveBeenCalledWith('Products');

            expect(dep.length).toEqual(2);
        });

        it('will load dependencies from cache', function() {
            sinon.collection.spy(app.metadata, 'getModule');
            view.moduleDependencies.Products = [{dependency: true}];
            expect(app.metadata.getModule).not.toHaveBeenCalled();
        });
    });

    describe('setupSugarLogicForModel', function() {
        var model;
        beforeEach(function() {
            // this is needed since we can't load the SugarLogic plugin in tests
            view.initSugarLogicForModelWithDeps = function() {};
            model = new Backbone.Model({id: 'asdf'});
        });
        it('will not setup dependencies when size is 0', function() {
            sinon.collection.stub(view, '_getSugarLogicDependenciesForModel', function() {
                return [];
            });
            sinon.collection.spy(view, 'initSugarLogicForModelWithDeps');
            view.setupSugarLogicForModel(model);
            expect(view.initSugarLogicForModelWithDeps).not.toHaveBeenCalled();
        });

        it('it will init dependencies for the model', function() {
            var deps = [{'dependency': true}];
            sinon.collection.stub(view, '_getSugarLogicDependenciesForModel', function() {
                return deps;
            });
            var ret = {
                dispose: function() {}
            };
            sinon.collection.stub(view, 'initSugarLogicForModelWithDeps', function() {
                return ret;
            });
            view.setupSugarLogicForModel(model);
            expect(view.initSugarLogicForModelWithDeps).toHaveBeenCalled(model, deps, true);
        });
    });

    describe('onCancelRowEdit()', function() {
        var rowModel1;
        var rowModel2;

        beforeEach(function() {
            rowModel1 = new Backbone.Model({
                id: 'rowModel1'
            });

            rowModel2 = new Backbone.Model({
                id: 'rowModel2'
            });
            rowModel2.set('_notSaved', true);
            view.newIdsToSave.push('rowModel2');
            view.collection.add(rowModel1);
            view.collection.add(rowModel2);
        });

        afterEach(function() {
            rowModel1 = null;
            rowModel2 = null;
        });

        it('should only remove the rowModel from the collection when _notSaved = true', function() {
            view.onCancelRowEdit(rowModel1);
            expect(view.newIdsToSave.length).toBe(1);
            expect(view.collection.length).toBe(5);
        });

        it('should remove the rowModel from the collection since _notSaved = true', function() {
            view.onCancelRowEdit(rowModel2);
            expect(view.newIdsToSave.length).toBe(0);
            expect(view.collection.length).toBe(4);
        });
    });

    describe('onSaveRowEdit()', function() {
        var rowModel;
        var rowModelId;
        var oldModelId;
        var attrStub;

        beforeEach(function() {
            attrStub = sinon.collection.stub();
            sinon.collection.stub(view, '$', function() {
                return {
                    length: 1,
                    attr: attrStub
                };
            });
            sinon.collection.stub(view, '_setRowFields', function() {});
            sinon.collection.stub(view, 'toggleRow', function() {});

            oldModelId = 'oldRowModel1';
            rowModelId = 'rowModel1';
            rowModel = new Backbone.Model({
                id: rowModelId
            });
            rowModel.module = 'Products';
        });

        afterEach(function() {
            rowModel = null;
            oldModelId = null;
        });

        describe('model has _notSaved = true', function() {
            it('should remove _notSaved from the model if it exists', function() {
                rowModel.set('_notSaved', true);
                view.onSaveRowEdit(rowModel, rowModelId);
                expect(rowModel._notSaved).toBeUndefined();
            });

            it('should remove the model from toggledModels', function() {
                rowModel.set('_notSaved', true);
                view.toggledModels[rowModel.id] = rowModel;
                view.onSaveRowEdit(rowModel, rowModelId);
                expect(view.toggledModels[rowModel.id]).toBeUndefined();
            });
        });

        describe('model id == oldModelId', function() {
            it('should do nothing if model id is the same as oldModelId', function() {
                view.onSaveRowEdit(rowModel, rowModelId);
                expect(view.$).not.toHaveBeenCalled();
            });
        });

        describe('model id != oldModelId', function() {
            beforeEach(function() {
                view.onSaveRowEdit(rowModel, oldModelId);
            });

            it('should get the table row if model id is not oldModelId', function() {
                expect(view.$).toHaveBeenCalledWith('tr[name=Products_oldRowModel1]');
            });
            it('should change the table row name attr', function() {
                expect(attrStub).toHaveBeenCalledWith('name', 'Products_rowModel1');
            });
            it('should call _setRowFields', function() {
                expect(view._setRowFields).toHaveBeenCalled();
            });
            it('should call toggleRow', function() {
                expect(view.toggleRow).toHaveBeenCalledWith('Products', 'rowModel1', false);
            });
        });
    });

    describe('onAddNewItemToGroup()', function() {
        var linkName;
        var groupModel;
        var relatedModel;
        var relatedModelId;

        beforeEach(function() {
            linkName = 'products';
            groupModel = new Backbone.Model();
            relatedModelId = 'newModelUUID1';
            relatedModel = new Backbone.Model();

            sinon.collection.stub(view, 'createLinkModel', function() {
                return relatedModel;
            });
            sinon.collection.stub(app.utils, 'generateUUID', function() {
                return relatedModelId;
            });

            view.onAddNewItemToGroup(groupModel, linkName);
        });

        afterEach(function() {
            view.newIdsToSave = [];
        });

        it('should add the new model id to newIdsToSave', function() {
            expect(view.newIdsToSave).toContain(relatedModelId);
        });

        it('should set the new related model id to the new guid', function() {
            expect(relatedModel.get('id')).toBe(relatedModelId);
        });

        it('should set the new relatedModel position to be the max of the collection models positions', function() {
            expect(relatedModel.get('position')).toBe(3);
        });

        it('should set the new relatedModel modelView to be edit', function() {
            expect(relatedModel.modelView).toBe('edit');
        });

        it('should set the new relatedModel _notSaved to be true', function() {
            expect(relatedModel.get('_notSaved')).toBeTruthy();
        });

        it('should add the new relatedModel to toggledModels', function() {
            expect(view.toggledModels[relatedModelId]).toEqual(relatedModel);
        });

        it('should add the new relatedModel to collection', function() {
            expect(view.collection.contains(relatedModel)).toBeTruthy();
        });
    });

    describe('_render()', function() {
        beforeEach(function() {
            sinon.collection.stub(view, '_super', function() {});
            sinon.collection.stub(view, '_setRowFields', function() {});
            sinon.collection.stub(view, 'toggleRow', function() {});
        });

        it('should call _setRowFields', function() {
            view._render();
            expect(view._setRowFields).toHaveBeenCalled();
        });

        it('should call toggleRow if toggledModels has data', function() {
            view.toggledModels = {
                id1: new Backbone.Model({module: 'Products'})
            };
            view._render();
            expect(view.toggleRow).toHaveBeenCalled();
        });

        it('should not call toggleRow if toggledModels is empty', function() {
            view.toggledModels = {};
            view._render();
            expect(view.toggleRow).not.toHaveBeenCalled();
        });
    });

    describe('_onEditRowBtnClicked()', function() {
        var dataStub;
        var evt;

        beforeEach(function() {
            evt = {
                target: '<div></div>'
            };

            dataStub = sinon.collection.stub();
            dataStub.withArgs('row-module').returns('rowModule');
            dataStub.withArgs('row-model-id').returns('rowModelId');

            sinon.collection.stub($.fn, 'closest', function() {
                return {
                    length: 1,
                    data: dataStub
                };
            });

            sinon.collection.stub(view, 'toggleRow', function() {});
        });

        it('should call toggleRow', function() {
            view._onEditRowBtnClicked(evt);
            expect(view.toggleRow).toHaveBeenCalled();
        });

        it('should call toggleRow with first param row module', function() {
            view._onEditRowBtnClicked(evt);
            expect(view.toggleRow.lastCall.args[0]).toBe('rowModule');
        });

        it('should call toggleRow with second param row model id', function() {
            view._onEditRowBtnClicked(evt);
            expect(view.toggleRow.lastCall.args[1]).toBe('rowModelId');
        });

        it('should call toggleRow with third param true to toggle row', function() {
            view._onEditRowBtnClicked(evt);
            expect(view.toggleRow.lastCall.args[2]).toBeTruthy();
        });
    });

    describe('_onDeleteRowBtnClicked()', function() {
        var dataStub;
        var evt;

        beforeEach(function() {
            evt = {
                target: '<div></div>'
            };

            dataStub = sinon.collection.stub();
            dataStub.withArgs('row-module').returns('rowModule');
            dataStub.withArgs('row-model-id').returns('rowModelId');

            sinon.collection.stub($.fn, 'closest', function() {
                return {
                    length: 1,
                    data: dataStub
                };
            });

            sinon.collection.stub(app.alert, 'show');
        });

        it('should call alert show', function() {
            view._onDeleteRowBtnClicked(evt);
            expect(app.alert.show).toHaveBeenCalled();
        });
    });

    describe('isolateRowParams()', function() {
        var dataStub;
        var evt;

        beforeEach(function() {
            evt = {
                target: '<div></div>'
            };

            dataStub = sinon.collection.stub();
            dataStub.withArgs('row-module').returns('rowModule');
            dataStub.withArgs('row-model-id').returns('rowModelId');

            sinon.collection.stub($.fn, 'closest', function() {
                return {
                    length: 1,
                    data: dataStub
                };
            });
        });

        it('should return the correct params', function() {
            var result = view.isolateRowParams(evt);

            expect(result.module).toEqual('rowModule');
            expect(result.id).toEqual('rowModelId');
        });
    });

    describe('toggleRow()', function() {
        var toggleClassStub;
        var rowModel;
        var rowModule;
        var rowModelId;

        beforeEach(function() {
            view.toggledModels = {};
            rowModule = 'Products';
            rowModelId = 'testId1';
            rowModel = new Backbone.Model({
                id: rowModelId,
                module: rowModule
            });
            view.collection.add(rowModel);

            view.rowFields[rowModelId] = rowModel;

            toggleClassStub = sinon.collection.stub();

            sinon.collection.stub(view, '$', function() {
                return {
                    toggleClass: toggleClassStub,
                    hasClass: function() {
                        return false;
                    }
                };
            });
            sinon.collection.stub(view, 'toggleFields', function() {});

            view.toggleRow(rowModule, rowModelId, true);
        });

        afterEach(function() {
            toggleClassStub = null;
            rowModel = null;
            rowModule = null;
            rowModelId = null;
        });

        describe('when isEdit is true', function() {
            it('should add the toggled model to toggledModels', function() {
                expect(view.toggledModels[rowModelId]).toEqual(rowModel);
            });

            it('should set modelView to edit on the toggledModel', function() {
                expect(view.toggledModels[rowModelId].modelView).toEqual('edit');
            });
        });

        describe('when isEdit is false', function() {
            it('should delete the toggled model from toggledModels', function() {
                // set the model first then remove it
                expect(view.toggledModels[rowModelId]).toEqual(rowModel);

                // then remove it
                view.toggleRow(rowModule, rowModelId, false);
                expect(view.toggledModels[rowModelId]).toBeUndefined();

            });

            it('should set modelView on the toggled model when removing', function() {
                expect(rowModel.modelView).toBe('edit');

                view.toggleRow(rowModule, rowModelId, false);
                expect(rowModel.modelView).toBe('list');
            });

            describe('with jquery not stubbed', function() {
                var testRow;
                beforeEach(function() {
                    view.$.restore();
                    testRow = $('<tr name="Products_productId1" class="not-sortable"></tr>');
                    sinon.collection.stub(view, '$', function() {
                        return testRow;
                    });
                });

                it('should call removeClass not-sortable if the row hasClass not-sortable', function() {
                    view.toggleRow(rowModule, rowModelId, false);
                    expect(testRow.hasClass('not-sortable')).toBeFalsy();
                });

                it('should add class "sortable" if the row hasClass not-sortable', function() {
                    view.toggleRow(rowModule, rowModelId, false);
                    expect(testRow.hasClass('sortable')).toBeTruthy();
                });

                it('should add class "ui-sortable" if the row hasClass not-sortable', function() {
                    view.toggleRow(rowModule, rowModelId, false);
                    expect(testRow.hasClass('ui-sortable')).toBeTruthy();
                });
            });
        });

        it('should call this.$ with module and id', function() {
            expect(view.$).toHaveBeenCalledWith('tr[name=' + rowModule + '_' + rowModelId + ']');
        });

        it('should call toggleClass with first param being correct class', function() {
            expect(toggleClassStub.lastCall.args[0]).toBe('tr-inline-edit');
        });

        it('should call toggleClass with second param being isEdit = true', function() {
            expect(toggleClassStub.lastCall.args[1]).toBeTruthy();
        });

        it('should call toggleClass with second param being isEdit = false', function() {
            view.toggleRow(rowModule, rowModelId, false);
            expect(toggleClassStub.lastCall.args[1]).toBeFalsy();
        });

        it('should call toggleFields with first param being correct class', function() {
            expect(view.toggleFields.lastCall.args[0]).toEqual(rowModel);
        });

        it('should call toggleFields with second param being isEdit = true', function() {
            expect(view.toggleFields.lastCall.args[1]).toBeTruthy();
        });

        it('should call toggleFields with second param being isEdit = false', function() {
            view.toggleRow(rowModule, rowModelId, false);
            expect(view.toggleFields.lastCall.args[1]).toBeFalsy();
        });
    });

    describe('_setRowFields()', function() {
        var field1;
        var field2;
        var field3;
        var field4;

        beforeEach(function() {
            field1 = {
                model: new Backbone.Model({id: 'testId1'})
            };

            field2 = {
                model: new Backbone.Model({id: 'testId2'})
            };

            field3 = {
                model: new Backbone.Model({id: 'testId3'}),
                parent: true
            };

            field4 = {
                model: new Backbone.Model(),
            };

            view.fields = [
                field1,
                field2,
                field3,
                field4
            ];

            view._setRowFields();
        });

        afterEach(function() {
            view.rowFields = null;
            view.fields = null;
        });

        it('should set rowFields from fields', function() {
            expect(view.rowFields.testId1[0]).toEqual(field1);
            expect(view.rowFields.testId2[0]).toEqual(field2);
        });

        it('should set not set rowFields for fields with no id', function() {
            expect(view.rowFields.testId3).toBeUndefined();
        });

        it('should set not set rowFields for fields with a parent', function() {
            expect(view.rowFields.testId4).toBeUndefined();
        });
    });

    describe('getFieldNames()', function() {
        var prodMeta;
        var prodFieldsMeta;
        var pbnMeta;
        var pbnFieldsMeta;

        beforeEach(function() {
            prodMeta = {
                panels: [{
                    fields: [{
                        name: 'field1'
                    }, {
                        name: 'field2'
                    }]
                }]
            };

            prodFieldsMeta = {
                field1: {
                    name: 'field1'
                },
                field2: {
                    name: 'field2'
                }
            };

            pbnMeta = {
                panels: [{
                    fields: [{
                        name: 'field3'
                    }, {
                        name: 'field4'
                    }]
                }]
            };

            pbnFieldsMeta = {
                field3: {
                    name: 'field3'
                },
                field4: {
                    name: 'field4'
                }
            };

            sinon.collection.stub(app.metadata, 'getModule')
                .withArgs('Products').returns(prodFieldsMeta)
                .withArgs('ProductBundleNotes').returns(pbnFieldsMeta);

            view.pbnListMetadata = pbnMeta;
            view.qliListMetadata = prodMeta;
        });

        afterEach(function() {
            prodMeta = null;
            prodFieldsMeta = null;
            pbnMeta = null;
            pbnFieldsMeta = null;
        });

        it('should return the Products metadata fieldnames', function() {
            expect(view.getFieldNames('Products')).toEqual(['field1', 'field2']);
        });

        it('should return the ProductBundleNotes metadata fieldnames', function() {
            expect(view.getFieldNames('ProductBundleNotes')).toEqual(['field3', 'field4']);
        });
    });
});
