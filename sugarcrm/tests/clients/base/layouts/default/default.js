describe('BaseDefaultLayout', function() {
    var layout, app, def;

    beforeEach(function() {
        app = SugarTest.app;
        def = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]
        };
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'default');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        layout = SugarTest.createLayout('base', null, 'default', def, null);
    });
    afterEach(function() {
        sinon.collection.restore();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('listeners', function() {
        var toggleSidePaneStub;

        beforeEach(function() {
            sinon.collection.stub(layout, 'processDef');
            toggleSidePaneStub = sinon.collection.stub(layout, 'toggleSidePane');
            layout.initialize({ meta: def });
        });

        it('should toggle side pane when "sidebar:toggle" is triggered', function() {
            app.controller.context.trigger('sidebar:toggle');
            expect(toggleSidePaneStub).toHaveBeenCalled();
        });

        it('should respond when "sidebar:state:ask" is triggered', function() {
            var triggerSpy = sinon.collection.spy(app.controller.context, 'trigger');
            app.controller.context.trigger('sidebar:state:ask');
            expect(triggerSpy).toHaveBeenCalledWith('sidebar:state:respond');
        });
    });

    describe('isSidePaneVisible', function() {
        var lastStateStub, lastState;

        beforeEach(function() {
            lastStateStub = sinon.collection.stub(app.user.lastState, 'get', function() {
                return lastState;
            });
        });

        it('should return true', function() {
            lastState = undefined;
            expect(layout.isSidePaneVisible()).toBeTruthy();
        });

        it('should return false', function() {
            lastState = 'hide';
            expect(layout.isSidePaneVisible()).toBeFalsy();
        });
    });

    describe('toggleSidePane', function() {
        var isSidePaneVisibleStub, isSidePaneVisible;
        var lastStateSetStub, lastStateRemoveStub;
        var _toggleVisibilityStub;

        beforeEach(function() {
            isSidePaneVisibleStub = sinon.collection.stub(layout, 'isSidePaneVisible', function() {
                return isSidePaneVisible;
            });
            lastStateSetStub = sinon.collection.stub(app.user.lastState, 'set');
            lastStateRemoveStub = sinon.collection.stub(app.user.lastState, 'remove');
            _toggleVisibilityStub = sinon.collection.stub(layout, '_toggleVisibility');
        });

        describe('when "true" is passed', function() {
            it('should remove key and call _toggleVisibility with "true"', function() {
                isSidePaneVisible = false;
                layout.toggleSidePane(true);
                expect(lastStateSetStub).not.toHaveBeenCalled();
                expect(lastStateRemoveStub).toHaveBeenCalled();
                expect(_toggleVisibilityStub).toHaveBeenCalled();
            });

            it('should ignore because side pane is already visible', function() {
                isSidePaneVisible = true;
                layout.toggleSidePane(true);
                expect(lastStateSetStub).not.toHaveBeenCalled();
                expect(lastStateRemoveStub).not.toHaveBeenCalled();
                expect(_toggleVisibilityStub).not.toHaveBeenCalled();
            });
        });

        describe('when "false" is passed', function() {
            it('should set key and call _toggleVisibility with "false"', function() {
                isSidePaneVisible = true;
                layout.toggleSidePane(false);
                expect(lastStateSetStub).toHaveBeenCalled();
                expect(lastStateRemoveStub).not.toHaveBeenCalled();
                expect(_toggleVisibilityStub).toHaveBeenCalled();
            });

            it('should ignore because side pane is already hidden', function() {
                isSidePaneVisible = false;
                layout.toggleSidePane(false);
                expect(lastStateSetStub).not.toHaveBeenCalled();
                expect(lastStateRemoveStub).not.toHaveBeenCalled();
                expect(_toggleVisibilityStub).not.toHaveBeenCalled();
            });
        });

        describe('when nothing is passed', function() {
            it('should set key and call _toggleVisibility with "false"', function() {
                isSidePaneVisible = true;
                layout.toggleSidePane();
                expect(lastStateSetStub).toHaveBeenCalled();
                expect(lastStateRemoveStub).not.toHaveBeenCalled();
                expect(_toggleVisibilityStub).toHaveBeenCalled();
            });

            it('should remove key and call _toggleVisibility with "true"', function() {
                isSidePaneVisible = false;
                layout.toggleSidePane();
                expect(lastStateSetStub).not.toHaveBeenCalled();
                expect(lastStateRemoveStub).toHaveBeenCalled();
                expect(_toggleVisibilityStub).toHaveBeenCalled();
            });
        });
    });


    describe('_toggleVisibility', function() {
        var resizeStub, contextStub;

        beforeEach(function() {
            resizeStub = sinon.collection.stub($.fn, 'trigger');
            contextStub = sinon.collection.stub(app.controller.context, 'trigger');
        });

        it('should call window "resize"', function() {
            layout._toggleVisibility(true);
            expect(resizeStub).toHaveBeenCalledWith('resize');
            expect(contextStub).toHaveBeenCalledWith('sidebar:state:changed');
        });
    });

    describe('deprecated methods', function() {
        var warnStub, toggleSidePaneStub;

        beforeEach(function() {
            warnStub = sinon.collection.stub(app.logger, 'warn');
            toggleSidePaneStub = sinon.collection.stub(layout, 'toggleSidePane');
        });

        it('should warn that toggleSide is deprecated', function() {
            layout.toggleSide();
            expect(toggleSidePaneStub).toHaveBeenCalled();
            expect(warnStub).toHaveBeenCalled();
        });

        it('should warn that openSide is deprecated', function() {
            layout.openSide();
            expect(toggleSidePaneStub).toHaveBeenCalledWith(true);
            expect(warnStub).toHaveBeenCalled();
        });
    });
});
