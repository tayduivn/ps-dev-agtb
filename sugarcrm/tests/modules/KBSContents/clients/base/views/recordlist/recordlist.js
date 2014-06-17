describe('KBSContents.Base.Views.RecordList', function() {

    var app, view, sandbox, layout, context, moduleName = 'KBSContents';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        context = app.context.getContext({
            module: moduleName
        });
        context.set('model', new Backbone.Model());
        context.parent = new Backbone.Model();
        context.parent.set('module', moduleName);
        SugarTest.loadPlugin('KBSContent');
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'recordlist', moduleName);
        SugarTest.loadHandlebarsTemplate('flex-list', 'view', 'base');
        layout = SugarTest.createLayout(
            'base',
            moduleName,
            'list',
            null,
            context.parent
        );
        view = SugarTest.createView(
            'base',
            moduleName,
            'recordlist',
            null,
            context,
            moduleName,
            layout
        );
    });

    afterEach(function() {
        sandbox.restore();
        layout.dispose();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        delete app.plugins.plugins['view']['KBSContent'];
        view = null;
        layout = null;
    });

    describe('initialize()', function() {
        var superStub, hasAccessToModelStub, contextSetStub;

        beforeEach(function() {
            superStub = sandbox.stub(view, '_super');
            contextSetStub = sandbox.stub(view.context, 'set');
        });

        it('should call parent method when initialize', function() {
            view.initialize({});
            expect(superStub).toHaveBeenCalled();
        });

        it('should check acl edit and when not allowed set context noedit', function() {
            hasAccessToModelStub = sandbox.stub(app.acl, 'hasAccess', function() {
                return false;
            });
            view.initialize({});
            expect(contextSetStub).toHaveBeenCalledWith('requiredFilter', 'records-noedit');
        });

        it('should check acl edit and when allowed then not set context', function() {
            hasAccessToModelStub = sandbox.stub(app.acl, 'hasAccess', function() {
                return true;
            });
            view.initialize({});
            expect(contextSetStub).not.toHaveBeenCalled();
        });
    });

    describe('parseFieldMetadata()', function() {
        var superStub, hasAccessToModelStub, contextSetStub, data;

        beforeEach(function() {
            data = {
                module: moduleName,
                meta: {
                    panels: [
                        {
                            fields: [
                                {
                                    name: 'test1'
                                },
                                {
                                    name: 'test2'
                                },
                                {
                                    name: 'status'
                                }
                            ]
                        }
                    ]
                }
            };
            superStub = sandbox.stub(view, '_super', function(func, args) {
                return _.clone(args[0]);
            });
            contextSetStub = sandbox.stub(view.context, 'set');
        });

        it('should check acl edit and do not filter data when allowed', function() {
            hasAccessToModelStub = sandbox.stub(app.acl, 'hasAccess', function() {
                return true;
            });
            var result = view.parseFieldMetadata(data);

            expect(superStub).toHaveBeenCalled();
            expect(hasAccessToModelStub).toHaveBeenCalled();
            expect(result.meta.panels[0].fields).toContain({
                name: 'status'
            });
        });

        it('should check acl edit and remove secure fields when not allowed', function() {
            hasAccessToModelStub = sandbox.stub(app.acl, 'hasAccess', function() {
                return false;
            });
            var result = view.parseFieldMetadata(data);

            expect(superStub).toHaveBeenCalled();
            expect(hasAccessToModelStub).toHaveBeenCalled();
            expect(result.meta.panels[0].fields).not.toContain({
                name: 'status'
            });
        });
    });
});
