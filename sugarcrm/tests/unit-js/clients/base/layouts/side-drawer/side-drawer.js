/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
describe('Base.Layout.SideDrawer', function() {
    var drawer;
    var app;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'layout', 'side-drawer');
        drawer = SugarTest.createLayout('base', 'layout', 'side-drawer', {});
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.collection.restore();
        drawer.dispose();
    });

    describe('open', function() {
        it('should show the drawer if not yet open', function() {
            var elShowStub = sinon.collection.stub(drawer.$el, 'show');
            drawer.open();
            expect(elShowStub).toHaveBeenCalled();
            expect(drawer.currentState).toEqual('idle');
        });

        it('should not try to show the drawer again if already open', function() {
            var elShowStub = sinon.collection.stub(drawer.$el, 'show');
            var showCompStub = sinon.collection.stub(drawer, 'showComponent');
            drawer.currentState = 'idle';
            drawer.open();
            expect(elShowStub).not.toHaveBeenCalled();
            expect(drawer.currentState).toEqual('idle');
            expect(showCompStub).toHaveBeenCalled();
        });
    });

    describe('showComponent', function() {
        it('should remove old component', function() {
            var comp = {
                dispose: sinon.collection.stub()
            };
            var initCompStub = sinon.collection.stub(drawer, '_initializeComponentsFromDefinition');

            drawer._components = [comp];
            drawer.showComponent();
            expect(comp.dispose).toHaveBeenCalled();
            expect(drawer._components.length).toBe(0);
        });

        it('should add new component', function() {
            var comp = {
                loadData: sinon.collection.stub(),
                render: sinon.collection.stub(),
                dispose: sinon.collection.stub()
            };
            var initCompStub = sinon.collection.stub(drawer, '_initializeComponentsFromDefinition', function() {
                drawer._components = [comp];
            });

            drawer.showComponent();
            expect(comp.loadData).toHaveBeenCalled();
            expect(comp.render).toHaveBeenCalled();
        });
    });

    describe('toggle', function() {
        var elToggleStub;

        beforeEach(function() {
            elToggleStub = sinon.collection.stub(drawer.$el, 'toggle');
        });

        it('should toggle if drawer is open', function() {
            drawer.currentState = 'idle';
            drawer.toggle();
            expect(elToggleStub).toHaveBeenCalled();
        });

        it('should not toggle if drawer is not open', function() {
            drawer.currentState = '';
            drawer.toggle();
            expect(elToggleStub).not.toHaveBeenCalled();
        });
    });

    describe('close', function() {
        it('should close the drawer, remove component and callback', function() {
            var comp = {
                dispose: sinon.collection.stub()
            };
            var onCloseCallback = {
                apply: sinon.collection.stub()
            };
            var elHideStub = sinon.collection.stub(drawer.$el, 'hide');
            drawer._components = [comp];
            drawer.onCloseCallback = onCloseCallback;
            drawer.close();
            expect(elHideStub).toHaveBeenCalled();
            expect(comp.dispose).toHaveBeenCalled();
            expect(drawer._components.length).toBe(0);
            expect(onCloseCallback.apply).toHaveBeenCalled();
            expect(drawer.currentState).toEqual('');
        });

        it('should add shortcuts to close drawer', function() {
            expect(drawer.shortcuts).toContain('SideDrawer:Close');
        });
    });

    describe('_resizeDrawer', function() {
        var cssStub;
        var heightStub;

        beforeEach(function() {
            cssStub = sinon.collection.stub(drawer.$el, 'css');
            heightStub = sinon.collection.stub(drawer, '_determineDrawerHeight');
        });

        it('should resize', function() {
            drawer.currentState = 'idle';
            drawer._resizeDrawer();
            expect(heightStub).toHaveBeenCalled();
            expect(cssStub).toHaveBeenCalled();
        });

        it('should not resize when opening', function() {
            drawer.currentState = 'opening';
            drawer._resizeDrawer();
            expect(heightStub).not.toHaveBeenCalled();
            expect(cssStub).not.toHaveBeenCalled();
        });

        it('should not resize when closing', function() {
            drawer.currentState = 'closing';
            drawer._resizeDrawer();
            expect(heightStub).not.toHaveBeenCalled();
            expect(cssStub).not.toHaveBeenCalled();
        });
    });
});
