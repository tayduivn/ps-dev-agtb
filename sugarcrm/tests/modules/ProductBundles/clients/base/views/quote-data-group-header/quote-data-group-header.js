describe('ProductBundles.Base.Views.QuoteDataGroupHeader', function() {
    var app;
    var view;
    var viewMeta;
    var viewLayoutModel;
    var layout;
    var layoutDefs;

    beforeEach(function() {
        app = SugarTest.app;
        viewLayoutModel = new Backbone.Model();
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
        view = SugarTest.createView('base', 'ProductBundles', 'quote-data-group-header',
            viewMeta, null, true, layout);
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

        it('should have the correct saveIconCssClass', function() {
            expect(view.saveIconCssClass).toBe('.group-loading-icon');
        });

        it('should set listColSpan to be the layout listColSpan', function() {
            expect(view.listColSpan).toBe(layout.listColSpan);
        });

        it('should set el to be the layout el', function() {
            expect(view.el).toBe(layout.el);
        });

        describe('when calling initialize', function() {
            var initOptions;
            beforeEach(function() {
                initOptions = {
                    meta: {
                        panels: [{
                            fields: ['field1', 'field2']
                        }]
                    },
                    model: new Backbone.Model(),
                    layout: {
                        listColSpan: 2
                    }
                };

                sinon.collection.stub(view.layout, 'on', function() {});
            });

            afterEach(function() {
                initOptions = null;
            });

            it('should call setElement', function() {
                view.initialize(initOptions);
                expect(view.setElement).toHaveBeenCalled();
            });

            it('should set groupSaveCt = 0', function() {
                view.initialize(initOptions);
                expect(view.groupSaveCt).toBe(0);
            });

            it('should call layout.on with quotes:group:save:start', function() {
                view.initialize(initOptions);
                expect(view.layout.on.args[0][0]).toBe('quotes:group:save:start');
            });

            it('should call layout.on with quotes:group:save:stop', function() {
                view.initialize(initOptions);
                expect(view.layout.on.args[1][0]).toBe('quotes:group:save:stop');
            });
        });
    });

    describe('_onGroupSaveStart()', function() {
        var showStub;
        beforeEach(function() {
            showStub = sinon.collection.stub();

            sinon.collection.stub(view, '$', function() {
                return {
                    show: showStub
                };
            });

            view._onGroupSaveStart();
        });

        it('should increment the groupSaveCt counter', function() {
            expect(view.groupSaveCt).toBe(1);
        });

        it('should call this.$(this.saveIconCssClass)', function() {
            expect(view.$.args[0][0]).toBe(view.saveIconCssClass);
        });

        it('should call show on the saveIconCssClass element', function() {
            expect(showStub).toHaveBeenCalled();
        });
    });

    describe('_onGroupSaveStop()', function() {
        var hideStub;
        beforeEach(function() {
            hideStub = sinon.collection.stub();

            sinon.collection.stub(view, '$', function() {
                return {
                    hide: hideStub
                };
            });
        });

        it('should decrement the groupSaveCt counter', function() {
            view.groupSaveCt = 3;
            view._onGroupSaveStop();

            expect(view.groupSaveCt).toBe(2);
        });

        describe('when groupSaveCt = 0', function() {
            beforeEach(function() {
                view.groupSaveCt = 1;
                view._onGroupSaveStop();
            });

            it('should call this.$(this.saveIconCssClass)', function() {
                expect(view.$.args[0][0]).toBe(view.saveIconCssClass);
            });

            it('should call show on the saveIconCssClass element', function() {
                expect(hideStub).toHaveBeenCalled();
            });
        });

        describe('when groupSaveCt goes below 0 in some freak async accident', function() {
            beforeEach(function() {
                view.groupSaveCt = -10;
                view._onGroupSaveStop();
            });

            it('should reset groupSaveCt to 0', function() {
                expect(view.groupSaveCt).toBe(0);
            });
        });
    });

    describe('_onDeleteBundleBtnClicked()', function() {
        it('should trigger quotes:group:delete event', function() {
            view.context.parent = SugarTest.app.context.getContext();
            sinon.collection.spy(view.context.parent, 'trigger');
            view._onDeleteBundleBtnClicked();

            expect(view.context.parent.trigger).toHaveBeenCalledWith('quotes:group:delete');
        });
    });

    describe('_onCreateQLIBtnClicked()', function() {
        it('should trigger quotes:group:delete event', function() {
            sinon.collection.spy(view.context, 'trigger');
            view.model.set('id', 'viewModel1');
            view._onCreateQLIBtnClicked();

            expect(view.context.trigger).toHaveBeenCalledWith('quotes:group:create:qli:viewModel1');
        });
    });

    describe('_onCreateCommentBtnClicked()', function() {
        it('should trigger quotes:group:delete event', function() {
            sinon.collection.spy(view.context, 'trigger');
            view.model.set('id', 'viewModel1');
            view._onCreateCommentBtnClicked();

            expect(view.context.trigger).toHaveBeenCalledWith('quotes:group:create:note:viewModel1');
        });
    });
});
