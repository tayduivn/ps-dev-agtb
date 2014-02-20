describe('Sidebar Toggle', function() {
    var field, layout, app;

    beforeEach(function() {
        app = SugarTest.app;
        var def = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]};
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'sidebartoggle');
        SugarTest.loadComponent('base', 'layout', 'default');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        layout = SugarTest.createLayout('base', null, 'default', def, null);
        field = SugarTest.createField('base', null, 'sidebartoggle', 'record', def);
        sinon.collection.stub(app.view.layouts.BaseDefaultLayout.prototype, 'processDef');
    });
    afterEach(function() {
        sinon.collection.restore();
        field.dispose();
        layout.dispose();
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

    describe('deprecated methods', function() {
        var warnStub;

        beforeEach(function() {
            warnStub = sinon.collection.stub(app.logger, 'warn');
        });

        it('should warn that updateArrows is deprecated', function() {
            field.updateArrows();
            expect(warnStub).toHaveBeenCalled();
        });

        it('should warn that sidebarArrowsOpen is deprecated', function() {
            field.sidebarArrowsOpen();
            expect(warnStub).toHaveBeenCalled();
        });
    });

    describe('toggleState', function() {
        var updateArrowsWithDirectionStub, hasClassStub,
            isOpen;

        beforeEach(function() {
            updateArrowsWithDirectionStub = sinon.collection.stub(field, 'updateArrowsWithDirection');
            hasClassStub = sinon.collection.stub($.fn, 'hasClass', function() {
                return isOpen;
            });
        });
        it('should call updateArrowsWithDirection with open', function() {
            field.toggleState('open');
            expect(updateArrowsWithDirectionStub).toHaveBeenCalledWith('open');
        });

        it('should call updateArrowsWithDirection with close', function() {
            field.toggleState('close');
            expect(updateArrowsWithDirectionStub).toHaveBeenCalledWith('close');
        });

        it('should call updateArrowsWithDirection with open if currently close', function() {
            isOpen = false;
            field.toggleState();
            expect(updateArrowsWithDirectionStub).toHaveBeenCalledWith('open');
        });

        it('should call updateArrowsWithDirection with close if currently open', function() {
            isOpen = true;
            field.toggleState();
            expect(updateArrowsWithDirectionStub).toHaveBeenCalledWith('close');
        });
    });

    describe('updateArrowsWithDirection', function() {
        var addClassStub, removeClassStub;

        beforeEach(function() {
            // Stub the addClass/removeClass jQuery methods on $'s prototype
            addClassStub = sinon.collection.stub($.fn, 'addClass', function() {
                return $.fn;
            });
            removeClassStub = sinon.collection.stub($.fn, 'removeClass', function() {
                return $.fn;
            });
        });

        it('should update arrows with direction (open)', function() {
            field.updateArrowsWithDirection('open');
            expect(removeClassStub).toHaveBeenCalledWith('icon-double-angle-left');
            expect(addClassStub).toHaveBeenCalledWith('icon-double-angle-right');
        });

        it('should update arrows with direction (close)', function() {
            field.updateArrowsWithDirection('close');
            expect(removeClassStub).toHaveBeenCalledWith('icon-double-angle-right');
            expect(addClassStub).toHaveBeenCalledWith('icon-double-angle-left');
        });
    });

});
