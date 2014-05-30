describe('KBSContents.Base.Layouts.RecordsSearchTags', function() {

    var app, layout, sandbox, context, moduleName = 'KBSContents';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        context = app.context.getContext({
            module: moduleName
        });
        context.set('model', new Backbone.Model());
        context.tag = 'abc';
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addLayoutDefinition('records-search-tags', {
            type: 'records-search-tags',
            name: 'base',
            components: []
        });
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        layout = SugarTest.createLayout(
            'base',
            moduleName,
            'records-search-tags',
            null,
            context,
            moduleName,
            {
                def: {
                    context: context
                }
            }
        );
    });

    afterEach(function() {
        sandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        layout.dispose();
        SugarTest.testMetadata.dispose();
        layout = null;
    });

    describe('_initializeCollectionFilterDef()', function() {
        var getComponentStub, collection;

        beforeEach(function() {
            collection = app.data.createBeanCollection(moduleName);
            collection.filterDef = undefined;
            getComponentStub = sandbox.stub(layout, 'getComponent', function() {
                var component = {
                    collection: collection,
                    getComponent: function() {
                        return component;
                    }
                };
                return component;
            });
        });

        it('should set filterDef if component defined when initialize collection filter def',
            function() {
                layout._initializeCollectionFilterDef({
                    def: {
                        context: context
                    }
                });
                expect(getComponentStub).toHaveBeenCalled();
                expect(collection.filterDef).toBeDefined();
            }
        );
    });
});
