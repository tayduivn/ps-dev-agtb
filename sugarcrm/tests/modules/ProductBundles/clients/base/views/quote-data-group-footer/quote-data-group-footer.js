describe('ProductBundles.Base.Views.QuoteDataGroupFooter', function() {
    var app;
    var view;
    var viewMeta;
    var viewContext;
    var viewLayoutModel;
    var layout;
    var layoutDefs;
    var layoutGroupId;

    beforeEach(function() {
        app = SugarTest.app;

        viewContext = app.context.getContext();
        viewContext.set({
            module: 'ProductBundles'
        });
        viewContext.prepare();

        layoutGroupId = 'layoutGroupId1';
        viewLayoutModel = new Backbone.Model({
            id: layoutGroupId
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
            panels: [{
                fields: ['field1', 'field2']
            }]
        };

        view = SugarTest.createView('base', 'ProductBundles', 'quote-data-group-footer',
            viewMeta, viewContext, true, layout);
        sinon.collection.stub(view, 'setElement');
        sinon.collection.stub(view, '_super');
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        var initOptions;

        it('should have the same model as the layout', function() {
            expect(view.model).toBe(viewLayoutModel);
        });

        it('should set listColSpan to be the layout listColSpan + 1', function() {
            expect(view.listColSpan).toBe(layout.listColSpan + 1);
        });

        it('should set el to be the layout el', function() {
            expect(view.el).toBe(layout.el);
        });

        describe('setting isEmptyGroup', function() {
            var layoutCollection;
            beforeEach(function() {
                layoutCollection = new Backbone.Collection();
                initOptions = {
                    context: viewContext,
                    meta: {
                        panels: [{
                            fields: ['field1', 'field2']
                        }]
                    },
                    layout: {
                        listColSpan: 2,
                        model: viewLayoutModel,

                    }
                };
            });

            afterEach(function() {
                layoutCollection = null;
            });

            it('should set isEmptyGroup true if layout collection has no records', function() {
                view.layout.collection = layoutCollection;
                view.initialize(initOptions);
                expect(view.isEmptyGroup).toBeTruthy();
            });

            it('should set isEmptyGroup false if layout collection has records', function() {
                layoutCollection.add(new Backbone.Model({
                    id: 'test1'
                }));
                view.layout.collection = layoutCollection;
                view.initialize(initOptions);
                expect(view.isEmptyGroup).toBeFalsy();
            });
        });

        describe('when initializing', function() {
            var callArgs;

            beforeEach(function() {
                initOptions = {
                    context: viewContext,
                    meta: {
                        panels: [{
                            fields: ['field1', 'field2']
                        }]
                    },
                    layout: {
                        listColSpan: 2,
                        model: viewLayoutModel
                    }
                };

                sinon.collection.stub(view.context, 'on', function() {});
                sinon.collection.stub(view.layout, 'on', function() {});
                sinon.collection.stub(view.layout.collection, 'on', function() {});

                view.initialize(initOptions);
            });

            afterEach(function() {
                initOptions = null;
                callArgs = null;
            });

            it('should call setElement', function() {
                expect(view.setElement).toHaveBeenCalled();
            });

            it('should call view.context.on should be called with "quotes:group:create:qli:<groupID>"', function() {
                callArgs = view.context.on.args[0];
                expect(callArgs[0]).toBe('quotes:group:create:qli:layoutGroupId1');
            });

            it('should call view.context.on should be called with "quotes:group:create:note:<groupID>"', function() {
                callArgs = view.context.on.args[1];
                expect(callArgs[0]).toBe('quotes:group:create:note:layoutGroupId1');
            });

            it('should call view.context.on should be called with "editablelist:cancel:<groupID>"', function() {
                callArgs = view.context.on.args[2];
                expect(callArgs[0]).toBe('editablelist:cancel:layoutGroupId1');
            });

            it('should call view.context.on should be called with "quotes:group:changed:<groupID>"', function() {
                callArgs = view.context.on.args[3];
                expect(callArgs[0]).toBe('quotes:group:changed:layoutGroupId1');
            });

            it('should call view.layout.on should be called with "quotes:sortable:over"', function() {
                callArgs = view.layout.on.args[0];
                expect(callArgs[0]).toBe('quotes:sortable:over');
            });

            it('should call view.layout.on should be called with "quotes:sortable:out"', function() {
                callArgs = view.layout.on.args[1];
                expect(callArgs[0]).toBe('quotes:sortable:out');
            });

            it('should call view.layout.collection.on should be called with "add remove"', function() {
                callArgs = view.layout.collection.on.args[0];
                expect(callArgs[0]).toBe('add remove');
            });
        });
    });

    describe('_onNewItemChanged()', function() {
        var layoutCollection;
        beforeEach(function() {
            layoutCollection = new Backbone.Collection();
            view.layout.collection = layoutCollection;
            sinon.collection.stub(view, 'toggleEmptyRow', function() {});
        });

        afterEach(function() {
            layoutCollection = null;
        });

        describe('when layout collection has records', function() {
            beforeEach(function() {
                layoutCollection.add(new Backbone.Model({
                    id: 'test1'
                }));
                view._onNewItemChanged();
            });

            it('should set isEmptyGroup false', function() {
                expect(view.isEmptyGroup).toBeFalsy();
            });

            it('should call toggleEmptyRow with false', function() {
                expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
            });
        });

        describe('when layout collection has no records', function() {
            beforeEach(function() {
                view._onNewItemChanged();
            });

            it('should set isEmptyGroup true', function() {
                expect(view.isEmptyGroup).toBeTruthy();
            });

            it('should call toggleEmptyRow with true', function() {
                expect(view.toggleEmptyRow).toHaveBeenCalledWith(true);
            });
        });
    });

    describe('_onSortableGroupOver()', function() {
        it('should always call toggleEmptyRow with false', function() {
            sinon.collection.stub(view, 'toggleEmptyRow');
            view._onSortableGroupOver();

            expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
        });
    });

    describe('_onSortableGroupOut()', function() {
        var evtParam;
        var uiParam;
        beforeEach(function() {
            sinon.collection.stub(view, 'toggleEmptyRow');
            uiParam = {};
            evtParam = {};
        });

        afterEach(function() {
            evtParam = null;
            uiParam = null;
        });

        describe('when isEmptyGroup is true', function() {
            beforeEach(function() {
                view.isEmptyGroup = true;
                uiParam = {
                    sender: null
                };
            });

            it('should always call toggleEmptyRow with true because the collection is empty', function() {
                view._onSortableGroupOut(evtParam, uiParam);

                expect(view.toggleEmptyRow).toHaveBeenCalledWith(true);
            });
        });

        describe('when isEmptyGroup is false', function() {
            beforeEach(function() {
                view.isEmptyGroup = false;
            });

            describe('when ui.sender is null', function() {
                beforeEach(function() {
                    uiParam = {
                        sender: null
                    };
                });

                describe('when layout.collection.length = 1', function() {
                    beforeEach(function() {
                        view.layout.collection.add(new Backbone.Model({
                            id: 1
                        }));
                    });

                    describe('when the current item 0 is hidden', function() {
                        beforeEach(function() {
                            uiParam.item = {
                                get: function() {
                                    return '<div style="display: none"></div>';
                                }
                            };
                        });

                        it('should call toggleEmptyRow with true', function() {
                            view._onSortableGroupOut(evtParam, uiParam);

                            expect(view.toggleEmptyRow).toHaveBeenCalledWith(true);
                        });
                    });

                    describe('when the current item 0 is not hidden', function() {
                        beforeEach(function() {
                            uiParam.item = {
                                get: function() {
                                    return '<div style="display: block"></div>';
                                }
                            };
                        });

                        it('should call toggleEmptyRow with true', function() {
                            view._onSortableGroupOut(evtParam, uiParam);

                            expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
                        });
                    });
                });

                describe('when layout.collection.length != 1', function() {
                    beforeEach(function() {
                        view.layout.collection.reset();
                    });

                    it('should call toggleEmptyRow with false', function() {
                        view._onSortableGroupOut(evtParam, uiParam);

                        expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
                    });
                });
            });

            describe('when ui.sender is not null', function() {
                describe('when ui.sender el is the same as the view.el', function() {
                    beforeEach(function() {
                        view.el = '<div id="viewEl" style="display: block"></div>';
                        uiParam.sender = {
                            length: 1,
                            get: function() {
                                return view.el;
                            }
                        };
                    });

                    describe('when layout.collection.length = 1', function() {
                        beforeEach(function() {
                            view.layout.collection.add(new Backbone.Model({
                                id: 1
                            }));
                        });

                        describe('when the current item 0 is hidden', function() {
                            beforeEach(function() {
                                uiParam.item = {
                                    get: function() {
                                        return '<div style="display: none"></div>';
                                    }
                                };
                            });

                            it('should call toggleEmptyRow with true', function() {
                                view._onSortableGroupOut(evtParam, uiParam);

                                expect(view.toggleEmptyRow).toHaveBeenCalledWith(true);
                            });
                        });

                        describe('when the current item 0 is not hidden', function() {
                            beforeEach(function() {
                                uiParam.item = {
                                    get: function() {
                                        return '<div style="display: block"></div>';
                                    }
                                };
                            });

                            it('should call toggleEmptyRow with true', function() {
                                view._onSortableGroupOut(evtParam, uiParam);

                                expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
                            });
                        });
                    });

                    describe('when layout.collection.length != 1', function() {
                        beforeEach(function() {
                            view.layout.collection.reset();
                        });

                        it('should call toggleEmptyRow with false', function() {
                            view._onSortableGroupOut(evtParam, uiParam);

                            expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
                        });
                    });
                });

                describe('when ui.sender el is different from the view.el', function() {
                    beforeEach(function() {
                        view.el = '<div id="viewEl" style="display: block"></div>';
                        uiParam.sender = {
                            length: 1,
                            get: function() {
                                return '<div id="diffEl" style="display: block"></div>';
                            }
                        };
                    });

                    it('should call toggleEmptyRow with false because sender is not in the same group', function() {
                        view._onSortableGroupOut(evtParam, uiParam);

                        expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
                    });
                });
            });
        });
    });

    describe('toggleEmptyRow()', function() {
        var addClassSpy;
        var removeClassSpy;
        beforeEach(function() {
            addClassSpy = sinon.collection.stub();
            removeClassSpy = sinon.collection.stub();
            sinon.collection.stub(view, '$', function() {
                return {
                    addClass: addClassSpy,
                    removeClass: removeClassSpy
                };
            });
        });

        it('should call remove class hidden when showEmptyRow is true', function() {
            view.toggleEmptyRow(true);

            expect(removeClassSpy).toHaveBeenCalled();
            expect(addClassSpy).not.toHaveBeenCalled();
        });

        it('should call add class hidden when showEmptyRow is false', function() {
            view.toggleEmptyRow(false);

            expect(removeClassSpy).not.toHaveBeenCalled();
            expect(addClassSpy).toHaveBeenCalled();
        });
    });

    describe('_renderHtml', function() {
        beforeEach(function() {
            sinon.collection.stub(view, 'toggleEmptyRow');
            sinon.collection.stub(view, '$', function() {
                return {
                    length: 0
                };
            });
        });

        it('should call toggleEmptyRow with true when isEmptyGroup = true', function() {
            view.isEmptyGroup = true;
            view._renderHtml();

            expect(view.toggleEmptyRow).toHaveBeenCalledWith(true);
        });

        it('should call toggleEmptyRow with false when isEmptyGroup = false', function() {
            view.isEmptyGroup = false;
            view._renderHtml();

            expect(view.toggleEmptyRow).toHaveBeenCalledWith(false);
        });
    });
});
