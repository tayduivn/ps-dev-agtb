describe('KBSContents.Base.Views.ConfigView', function() {

    var app, view, sandbox, context, apiCallStub, moduleName = 'KBSContents';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        context = app.context.getContext({
            module: moduleName
        });
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition(
            'config',
            {
                configModule: moduleName,
                panels: [
                    {
                        label: 'LBL_ADMIN_LABEL_LANGUAGES',
                        fields: {}
                    }
                ]
            },
            moduleName
        );
        SugarTest.testMetadata.set();
        app.data.declareModels();
        SugarTest.loadHandlebarsTemplate(
            'config',
            'view',
            'base',
            null,
            moduleName
        );
        apiCallStub = sandbox.stub(app.api, 'call');
        view = SugarTest.createView(
            'base',
            moduleName,
            'config',
            null,
            context,
            moduleName
        );
    });

    afterEach(function() {
        sandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        Handlebars.templates = {};
        view = null;
    });

    describe('_initModel()', function() {
        var model, modelSetStub, createBeanStub, baseModel;

        beforeEach(function() {
            baseModel = view.model;
            model = new Backbone.Model();
            modelSetStub = sandbox.stub(model, 'set');
            createBeanStub = sandbox.stub(app.data, 'createBean', function() {
                return model;
            });
        });

        afterEach(function() {
            view.model = baseModel;
        });

        it('should create model for config when called _initModel', function() {
            view._initModel({});
            expect(createBeanStub).toHaveBeenCalledWith('config');
            expect(createBeanStub.calledOnce).toBeTruthy();
        });

        it('should create model for config and set attributes when called _initModel',
            function() {
                view._initModel({
                    test1: 'TEST 1',
                    test2: {
                        name: 'test3'
                    }
                });
                expect(createBeanStub).toHaveBeenCalledWith('config');
                expect(modelSetStub).toHaveBeenCalled();
                expect(modelSetStub.calledTwice).toBeTruthy();
                expect(modelSetStub.getCall(0).args[0]).toEqual({test1: ''});
                expect(modelSetStub.getCall(1).args[0]).toEqual({test3: ''});
            }
        );
    });

    describe('saveClicked()', function() {
        var changedAttrsStub, disposeStub, contextStub;

        beforeEach(function() {
            changedAttrsStub = sandbox.stub(
                view.model,
                'changedAttributes',
                function() {
                    return {
                        test1: 'test-1'
                    };
                }
            );
            apiCallStub.restore();
            apiCallStub = sandbox.stub(app.api, 'call',
                function(action, url, attrs, options) {
                    options.success();
                }
            );
            disposeStub = sandbox.stub(view, 'dispose');
            contextStub = sandbox.stub(view.context, 'trigger');
        });

        it('should api call when save clicked', function() {
            view.saveClicked();

            expect(apiCallStub).toHaveBeenCalled();
            expect(apiCallStub.getCall(0).args[2]).toEqual({test1: 'test-1'});
        });

        it('should api call and dispose when success', function() {
            view.saveClicked();

            expect(disposeStub).toHaveBeenCalled();
        });

        it('should api call and context event trigger when success', function() {
            view.saveClicked();

            expect(contextStub).toHaveBeenCalled();
        });

        it('should api call and not dispose if disposed when save clicked', function() {
            view.disposed = true;
            view.saveClicked();

            expect(disposeStub).not.toHaveBeenCalled();
        });
    });

    describe('_dispose()', function() {
        var superStub, contextOffStub;

        beforeEach(function() {
            superStub = sandbox.stub(view, '_super', function() {});
            contextOffStub = sandbox.stub(view.context, 'off');
        });

        it('should call parent _dispose() when _dispose()', function() {
            view._dispose();
            expect(superStub).toHaveBeenCalledWith('_dispose');
        });

        it('should detach event listener when _dispose()', function() {
            view._dispose();
            expect(contextOffStub).toHaveBeenCalled();
        });
    });
});
