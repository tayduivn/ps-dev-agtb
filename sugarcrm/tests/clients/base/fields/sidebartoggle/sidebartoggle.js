describe('Sidebar Toggle', function() {
    var field, app;

    beforeEach(function() {
        app = SugarTest.app;
        var def = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]};
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'sidebartoggle');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        field = SugarTest.createField('base', null, 'sidebartoggle', 'record', def);
    });
    afterEach(function() {
        sinon.collection.restore();
        field.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    it('should trigger "sidebar:state:ask" to get the current open/close state', function() {
        var contextStub = sinon.collection.stub(app.controller.context, 'trigger');
        field.initialize({});
        expect(contextStub).toHaveBeenCalledWith('sidebar:state:ask');
    });

    describe('listeners', function() {
        var toggleStateStub;

        beforeEach(function() {
            toggleStateStub = sinon.collection.stub(field, 'toggleState');
            app.controller.context.off();
            field.initialize({});
        });

        it('should listen for "sidebar:state:respond" event', function() {
            app.controller.context.trigger('sidebar:state:respond');
            expect(toggleStateStub).toHaveBeenCalled();
        });

        it('should listen for "sidebar:state:changed" event', function() {
            app.controller.context.trigger('sidebar:state:changed');
            expect(toggleStateStub).toHaveBeenCalled();
        });
    });

    describe('toggle', function() {
        it('should trigger "sidebar:toggle" event', function() {
            var contextStub = sinon.collection.stub(app.controller.context, 'trigger');
            field.toggle();
            expect(contextStub).toHaveBeenCalledWith('sidebar:toggle');
        });
    });

    describe('toggleState', function() {
        it('should call stay open if called with open', function() {
            field._state = 'open';
            field.toggleState('open');
            expect(field._state).toEqual('open');
        });

        it('should stay close if called with close', function() {
            field._state = 'close';
            field.toggleState('close');
            expect(field._state).toEqual('close');
        });

        it('should become open if currently close', function() {
            field._state = 'close';
            field.toggleState();
            expect(field._state).toEqual('open');
        });

        it('should become close if currently open', function() {
            field._state = 'open';
            field.toggleState();
            expect(field._state).toEqual('close');
        });
    });
});
