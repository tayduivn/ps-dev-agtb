/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

describe('Sugar7 shortcuts', function() {
    var app,
        view,
        mousetrapBindStub,
        mousetrapUnbindStub,
        mousetrapResetStub,
        shortcutsMainPaneStub,
        View = Backbone.View.extend({
            before: $.noop,
            dispose: function() {
                this._dispose();
            },
            _dispose: $.noop
        });

    beforeEach(function() {
        app = SugarTest.app;

        view = new View();

        mousetrapBindStub = sinon.stub();
        mousetrapUnbindStub = sinon.stub();
        mousetrapResetStub = sinon.stub();

        Mousetrap = {
            bind: mousetrapBindStub,
            unbind: mousetrapUnbindStub,
            reset: mousetrapResetStub
        };

        shortcutsMainPaneStub = sinon.stub(
            app.shortcuts,
            '_isComponentInMainPane'
        ).returns(true);
    });

    afterEach(function() {
        shortcutsMainPaneStub.restore();
        app.shortcuts._shortcuts = {};
        app.shortcuts._savedShortCuts = [];
        app.shortcuts.clear();
        Mousetrap = undefined;
    });

    describe('register', function() {
        it('should bind shortcut keys when global scope is specified', function() {
            app.shortcuts.register(app.shortcuts.SCOPE.GLOBAL, 'a', $.noop, view);
            expect(mousetrapBindStub.calledOnce).toBe(true);
        });

        it('should bind shortcut keys when global scope is specified and another scope is active', function() {
            app.shortcuts.activate(app.shortcuts.SCOPE.RECORD);
            app.shortcuts.register(app.shortcuts.SCOPE.GLOBAL, 'a', $.noop, view);
            expect(mousetrapBindStub.calledOnce).toBe(true);
        });

        using('shortcut keys for active scope', ['a', ['b', 'c']], function (keys) {
            it('should bind shortcut keys when that scope is active', function() {
                var keyCount = _.isArray(keys) ? keys.length : 1;
                app.shortcuts.activate(app.shortcuts.SCOPE.RECORD);
                app.shortcuts.register(app.shortcuts.SCOPE.RECORD, keys, $.noop, view);
                expect(mousetrapBindStub.callCount).toBe(keyCount);
            });
        });

        it('should not bind shortcut keys when that scope is not active', function() {
            app.shortcuts.activate(app.shortcuts.SCOPE.LIST);
            app.shortcuts.register(app.shortcuts.SCOPE.RECORD, 'a', $.noop, view);
            expect(mousetrapBindStub.called).toBe(false);
        });

        it('should not bind non-global shortcut keys when the component is not in main pane', function() {
            shortcutsMainPaneStub.restore();
            shortcutsMainPaneStub = sinon.stub(app.shortcuts, '_isComponentInMainPane').returns(false);
            app.shortcuts.activate(app.shortcuts.SCOPE.RECORD);
            app.shortcuts.register(app.shortcuts.SCOPE.RECORD, 'a', $.noop, view);
            expect(mousetrapBindStub.called).toBe(false);
        });

        using('duplicate shortcut keys', ['a', ['a', 'b']], function (keys) {
            it('should not allow shortcut keys to be bound to more than one event', function() {
                var keyCount = _.isArray(keys) ? keys.length : 1;
                app.shortcuts.activate(app.shortcuts.SCOPE.RECORD);
                app.shortcuts.register(app.shortcuts.SCOPE.GLOBAL, 'a', $.noop, view);
                app.shortcuts.register(app.shortcuts.SCOPE.RECORD, keys, $.noop, view);
                expect(mousetrapBindStub.callCount).toBe(keyCount + 1);
                expect(mousetrapUnbindStub.callCount).toBe(1);
                expect(_.isEmpty(app.shortcuts._shortcuts[app.shortcuts.SCOPE.GLOBAL]['a'])).toBe(true);
            });
        });

        it('should bind multiple shortcut keys when the keys are an array', function() {
            app.shortcuts.activate(app.shortcuts.SCOPE.RECORD);
            app.shortcuts.register(app.shortcuts.SCOPE.RECORD, ['a', 'b'], $.noop, view);
            expect(mousetrapBindStub.callCount).toBe(2);
        });

        it("should unregister a component's shortcut keys when the component is disposed", function() {
            var shortcutsUnregisterSpy = sinon.spy(app.shortcuts, 'unregister');
            app.shortcuts.activate(app.shortcuts.SCOPE.RECORD);
            app.shortcuts.register(app.shortcuts.SCOPE.RECORD, 'a', $.noop, view);
            view.dispose();
            expect(shortcutsUnregisterSpy.called).toBe(true);
            shortcutsUnregisterSpy.restore();
        });
    });

    describe('unregister', function() {
        using('bound shortcut keys', ['a', ['a', 'b']], function(keys) {
            it('should unbind shortcut keys when the key has already been bound.', function() {
                var keyCount = _.isArray(keys) ? keys.length : 1;
                app.shortcuts.activate(app.shortcuts.SCOPE.RECORD);
                app.shortcuts.register(app.shortcuts.SCOPE.RECORD, keys, $.noop, view);
                app.shortcuts.unregister(app.shortcuts.SCOPE.RECORD, 'a', view);
                expect(mousetrapUnbindStub.callCount).toBe(keyCount);
            });
        });

        it('should not unbind shortcut keys when the key has not been bound.', function() {
            app.shortcuts.activate(app.shortcuts.SCOPE.RECORD);
            app.shortcuts.unregister(app.shortcuts.SCOPE.RECORD, 'a', view);
            expect(mousetrapUnbindStub.called).toBe(false);
        });

        it('should not unbind shortcut keys when a different component wants to unregister the same key', function() {
            app.shortcuts.activate(app.shortcuts.SCOPE.RECORD);
            app.shortcuts.register(app.shortcuts.SCOPE.RECORD, 'a', $.noop, view);
            app.shortcuts.unregister(app.shortcuts.SCOPE.RECORD, 'a', new View());
            expect(mousetrapUnbindStub.called).toBe(false);
        });
    });

    describe('activate', function() {
        it('should call clear', function() {
            var shortcutsClearSpy = sinon.spy(app.shortcuts, 'clear');
            app.shortcuts.activate(app.shortcuts.SCOPE.RECORD);
            expect(shortcutsClearSpy.called).toBe(true);
            shortcutsClearSpy.restore();
        });

        it('should set scope as active.', function() {
            app.shortcuts.activate(app.shortcuts.SCOPE.RECORD);
            expect(app.shortcuts.currentScope.get()).toBe(app.shortcuts.SCOPE.RECORD);
        });
    });

    describe('clear', function() {
        it('should remove all key bindings.', function() {
            app.shortcuts.clear();
            expect(mousetrapResetStub.calledOnce).toBe(true);
        });

        it('should clear current scope.', function() {
            app.shortcuts.clear();
            expect(app.shortcuts.currentScope.get()).toBeUndefined();
        });

        it('should rebind global shortcuts.', function() {
            app.shortcuts.register(app.shortcuts.SCOPE.GLOBAL, 'a', $.noop, view);
            expect(mousetrapBindStub.calledOnce).toBe(true);

            app.shortcuts.clear();
            expect(mousetrapBindStub.calledTwice).toBe(true);
        });
    });

    describe('save and restore', function() {
        it('should save all bindings and then rebind when restore is called.', function() {
            app.shortcuts.activate(app.shortcuts.SCOPE.RECORD);
            app.shortcuts.register(app.shortcuts.SCOPE.RECORD, 'a', $.noop, view);

            expect(mousetrapBindStub.calledOnce).toBe(true);
            app.shortcuts.save();
            app.shortcuts.activate(app.shortcuts.SCOPE.LIST);

            app.shortcuts.activate(app.shortcuts.SCOPE.RECORD);
            app.shortcuts.restore();
            expect(mousetrapBindStub.calledTwice).toBe(true);
        });
    });
});
