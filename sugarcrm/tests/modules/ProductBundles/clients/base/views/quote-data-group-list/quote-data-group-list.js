describe('ProductBundles.Base.Views.QuoteDataGroupList', function() {
    var app;
    var view;
    var viewMeta;
    var viewLayoutModel;
    var layout;
    var layoutDefs;
    var pbnMetadata;
    var prodMetadata;

    beforeEach(function() {
        app = SugarTest.app;
        viewLayoutModel = new Backbone.Model({
            related_records: [
                new Backbone.Model({id: 'test1'}),
                new Backbone.Model({id: 'test2'}),
                new Backbone.Model({id: 'test3'})
            ]
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
                fields: ['field1']
            }]
        };

        prodMetadata = {
            panels: [{
                fields: [
                    'field1', 'field2', 'field3', 'field4'
                ]
            }]
        };

        sinon.collection.stub(app.metadata, 'getView')
            .withArgs('ProductBundleNotes').returns(pbnMetadata)
            .withArgs('Products').returns(prodMetadata);

        view = SugarTest.createView('base', 'ProductBundles', 'quote-data-group-list', viewMeta, null, true, layout);
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

        it('should set rowCollection based on related_records', function() {
            expect(view.rowCollection.length).toBe(3);
        });

        describe('setting fields', function() {
            beforeEach(function() {
                view.initialize({
                    meta: viewMeta,
                    model: new Backbone.Model(),
                    layout: {
                        listColSpan: 2
                    }
                });
            });

            it('should call setElement', function() {
                expect(view.setElement).toHaveBeenCalled();
            });

            it('should set this.pbnListMetadata to be the ProductBundleNotes metadata', function() {
                expect(view.pbnListMetadata).toBe(pbnMetadata);
            });

            it('should set this.pbnListMetadata to be the Products metadata', function() {
                expect(view.qliListMetadata).toBe(prodMetadata);
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
                }
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
            view.rowCollection.add(rowModel);

            view.rowFields[rowModelId] = rowModel;

            toggleClassStub = sinon.collection.stub();
            sinon.collection.stub(view, '$', function() {
                return {
                    toggleClass: toggleClassStub
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

        it('should add the toggled model to toggledModels on isEdit = true', function() {
            expect(view.toggledModels[rowModelId]).toEqual(rowModel);
        });

        it('should delete the toggled model from toggledModels on isEdit = false', function() {
            // set the model first then remove it
            expect(view.toggledModels[rowModelId]).toEqual(rowModel);

            // then remove it
            view.toggleRow(rowModule, rowModelId, false);
            expect(view.toggledModels[rowModelId]).toBeUndefined();
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
