describe('KBSContents.Base.Views.KBSDashletTopics', function() {

    var app, view, sandbox, context, meta, layout, moduleName = 'KBSContents';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        context = app.context.getContext({
            module: moduleName
        });
        context.set('model', new Backbone.Model());
        context.parent = new Backbone.Model();
        context.parent.set('module', moduleName);
        meta = {
            config: false
        };
        layout = SugarTest.createLayout('base', moduleName, 'list', null, context.parent);
        SugarTest.loadPlugin('Dashlet');
        SugarTest.loadComponent(
            'base',
            'view',
            'kbs-dashlet-topics',
            moduleName
        );
        SugarTest.loadHandlebarsTemplate(
            'kbs-dashlet-topics',
            'view',
            'base',
            null,
            moduleName
        );
        view = SugarTest.createView(
            'base',
            moduleName,
            'kbs-dashlet-topics',
            meta,
            context,
            moduleName,
            layout
        );
    });

    afterEach(function() {
        sandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        layout.dispose();
        Handlebars.templates = {};
        delete app.plugins.plugins['view']['Dashlet'];
        view = null;
        layout = null;
    });

    describe('initDashlet()', function() {
        var templateGetViewStub, handlebarsRegisterPartialStub;

        beforeEach(function() {
            templateGetViewStub = sandbox.stub(app.template, 'getView');
            handlebarsRegisterPartialStub = sandbox.stub(Handlebars, 'registerPartial');
        });

        it('should load node-list template and register as handlebars partial when init dashlet',
            function() {
                view.initDashlet();
                expect(templateGetViewStub).toHaveBeenCalled();
                expect(handlebarsRegisterPartialStub).toHaveBeenCalled();
            }
        );
    });

    describe('getLastStateKey()', function() {
        var lastStateKeyStub, lastStateKey;

        beforeEach(function() {
           lastStateKeyStub = sandbox.stub(app.user.lastState, 'key', function() {
               return 'test-key';
           });
           lastStateKey = view._lastStateKey;
        });

        afterEach(function() {
            view._lastStateKey = lastStateKey;
        });

        it('should generate last state key when called getLastStateKey()', function() {
            var key = view.getLastStateKey();
            expect(key).toEqual('test-key');
            expect(lastStateKeyStub).toHaveBeenCalled();
        });

    });

    describe('bindDataChange()', function() {
        var model, modelOnStub;

        beforeEach(function() {
            model = view.model;
            modelOnStub = sandbox.stub(view.model, 'on');
        });

        afterEach(function() {
            view.model = model;
        });

        it('should attach event listener when model defined', function() {
            view.bindDataChange();
            expect(modelOnStub).toHaveBeenCalled();
        });

        it('should not attach event listener when model not defined', function() {
            view.model = null;
            view.bindDataChange();
            expect(modelOnStub).not.toHaveBeenCalled();
        });

    });

    describe('loadData()', function() {
        var settingsGetStub, apiCallStub, contextGetStub, disposed;

        beforeEach(function() {
            settingsGetStub = sandbox.stub(view.settings, 'get', function(name) {
                return name;
            });
            contextGetStub = sandbox.stub(app.controller.context, 'get', function() {
                var model = new Backbone.Model();
                model.set('topic_id', 200000);
                return model;
            });
            apiCallStub = sandbox.stub(app.api, 'call');
            disposed = view.disposed;
        });

        afterEach(function() {
            view.disposed = disposed;
        });

        it('should build url when called loadData()', function() {
            view.loadData({});
            expect(apiCallStub).toHaveBeenCalled();
            expect(apiCallStub.getCall(0).args[1]).toEqual(app.api.buildURL(
                'module_name',
                'tree/link_name',
                null,
                {
                    'order_by' : 'name:asc'
                }
            ));

        });

        it('should not api call when disposed', function() {
            view.disposed = true;
            view.loadData({});

            expect(apiCallStub).not.toHaveBeenCalled();
        });

        it('should not api call when link not defined', function() {
            settingsGetStub.restore();
            settingsGetStub = sandbox.stub(view.settings, 'get',
                function(name) {
                    if ('link_name' == name) {
                        return undefined;
                    }
                    return name;
                }
            );
            view.loadData({});

            expect(apiCallStub).not.toHaveBeenCalled();
        });

        it('should not api call when module not defined', function() {
            settingsGetStub.restore();
            settingsGetStub = sandbox.stub(view.settings, 'get',
                function(name) {
                    if ('module_name' == name) {
                        return undefined;
                    }
                    return name;
                }
            );
            view.loadData({});

            expect(apiCallStub).not.toHaveBeenCalled();
        });
    });

    describe('_traverseNodes()', function() {
        it('should not traverse when callback not defined', function() {
            var result = view._traverseNodes({
                records: [
                    {
                        name: 'test1',
                        subnodes: {
                            records: [
                                {
                                    name: 'abc'
                                },
                                {
                                    name: 'desss'
                                }
                            ]
                        }
                    }
                ]
            }, null);

            expect(result).toBe(false);
        });

        it('should traverse when callback defined', function() {
            var spy = sinon.spy(function(record) {
                return !_.isUndefined(record.subnodes);
            });
            var result = view._traverseNodes({
                records: [
                    {
                        name: 'test1',
                        subnodes: {
                            records: [
                                {
                                    name: 'abc'
                                },
                                {
                                    name: 'desss'
                                }
                            ]
                        }
                    }
                ]
            }, spy);

            expect(result).not.toBe(false);
            expect(spy).toHaveBeenCalled();
            expect(spy.calledThrice).toBeTruthy();
        });
    });

    describe('_render()', function() {
        var activateItemStub, active, nodes;

        beforeEach(function() {
            activateItemStub = sandbox.stub(view, '_activateItem');
            active = view.active;
            nodes = view.nodes;
        });

        afterEach(function() {
            view.active = active;
            view.nodes = nodes;
        });

        it('should activate item when active defined', function() {
            view.active = true;
            view.nodes = {};
            view._render();

            expect(activateItemStub).toHaveBeenCalled();
        });
    });
});
