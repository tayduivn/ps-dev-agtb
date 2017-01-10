describe('Quotes.Base.Views.PanelTop', function() {
    var app;
    var view;
    var viewMeta;
    var viewLayoutModel;
    var layout;
    var layoutDefs;
    var context;
    beforeEach(function() {
        app = SugarTest.app;
        viewLayoutModel = new Backbone.Model();
        layoutDefs = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]
        };
        layout = SugarTest.createLayout('base', 'Quotes', 'default', layoutDefs);

        SugarTest.loadComponent('base', 'view', 'panel-top');

        var parentContext = app.context.getContext();

        parentContext.set('module', 'Accounts');
        context = app.context.getContext();
        context.parent = parentContext;

        viewMeta = {
            panels: [{
                fields: ['field1', 'field2']
            }]
        };
        view = SugarTest.createView('base', 'Quotes', 'panel-top', viewMeta, context, true, layout);
        sinon.collection.stub(view, '_super', function() {});
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        it('should add MassQuote to this.plugins', function() {
            expect(view.plugins).toContain('MassQuote');
        });
    });

    describe('createRelatedClicked()', function() {
        var contextCollection;

        beforeEach(function() {
            contextCollection = new Backbone.Collection();
            view.context.set('collection', contextCollection);
            sinon.collection.stub(view.layout, 'trigger', function() {});

            view.createRelatedClicked({});
        });

        afterEach(function() {
            contextCollection = null;
        });

        it('should add MassQuote to this.plugins', function() {
            expect(view.context.get('mass_collection')).toEqual(contextCollection);
        });

        it('should trigger list:massquote:fire on view layout', function() {
            expect(view.layout.trigger).toHaveBeenCalledWith('list:massquote:fire');
        });
    });
});
