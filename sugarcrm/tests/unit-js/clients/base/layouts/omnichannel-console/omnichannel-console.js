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
describe('Base.Layout.OmnichannelConsole', function() {
    var console;
    var app;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'layout', 'omnichannel-console');
        console = SugarTest.createLayout('base', 'layout', 'omnichannel-console', {});
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.collection.restore();
        console.dispose();
    });

    describe('open', function() {
        it('should show the console if not yet open', function() {
            sinon.collection.stub(app.router, 'on');
            sinon.collection.stub(console, '_setSize');
            console.currentState = '';
            var elShowStub = sinon.collection.stub(console.$el, 'show');
            console.open();
            expect(elShowStub).toHaveBeenCalled();
            expect(console.currentState).toEqual('idle');
        });

        it('should not try to show the console again if already open', function() {
            var elShowStub = sinon.collection.stub(console.$el, 'show');
            console.currentState = 'idle';
            console.open();
            expect(elShowStub).not.toHaveBeenCalled();
            expect(console.currentState).toEqual('idle');
        });
    });

    describe('isOpen', function() {
        it('should return false if not yet open', function() {
            console.currentState = '';
            expect(console.isOpen()).toBeFalsy();
        });

        it('should return true if already open', function() {
            console.currentState = 'idle';
            expect(console.isOpen()).toBeTruthy();
        });
    });

    describe('closeImmediately', function() {
        it('should close console', function() {
            var elHideStub = sinon.collection.stub(console.$el, 'hide');
            sinon.collection.stub(console, '_offEvents');
            console.currentState = 'idle';
            console.closeImmediately();
            expect(elHideStub).toHaveBeenCalled();
            expect(console.currentState).toBe('');
        });
    });

    describe('toggleSession', function() {
        var elToggleStub;

        beforeEach(function() {
            elToggleStub = sinon.collection.stub(console.$el, 'animate');
        });

        it('should toggle if console is open', function() {
            sinon.collection.stub(console, '$').returns({
                toggle: $.noop
            });
            console.currentState = 'idle';
            console.toggleSession();
            expect(elToggleStub).toHaveBeenCalled();
        });

        it('should not toggle if console is not open', function() {
            console.currentState = '';
            console.toggleSession();
            expect(elToggleStub).not.toHaveBeenCalled();
        });
    });

    describe('close', function() {
        var elHideStub;

        beforeEach(function() {
            elHideStub = sinon.collection.stub(console.$el, 'hide');
        });

        it('should close the console', function() {
            sinon.collection.stub(console, '_offEvents');
            console.currentState = 'idle';
            console.close();
            expect(elHideStub).toHaveBeenCalled();
            expect(console.currentState).toEqual('');
        });

        it('should not close if teh console is already closed', function() {
            console.currentState = '';
            console.close();
            expect(elHideStub).not.toHaveBeenCalled();
        });
    });
});
