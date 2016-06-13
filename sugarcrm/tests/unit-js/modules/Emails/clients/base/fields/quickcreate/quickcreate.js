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
describe("Emails.Fields.Quickcreate", function() {
    var app;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField({
            client: 'base',
            name: 'quickcreate',
            type: 'quickcreate',
            viewName: 'detail',
            module: 'Emails',
            loadFromModule: true
        });
        sinon.collection.stub(field, '_super', function() {});
    });

    afterEach(function() {
        sinon.collection.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        field = null;
    });

    describe('initialize', function() {
        var ctxModel;
        var onSpy;
        var offSpy;

        beforeEach(function() {
            onSpy = sinon.collection.spy();
            offSpy = sinon.collection.spy();
            sinon.collection.spy(app.routing, 'before');
            sinon.collection.spy(app.router, 'on');

            ctxModel = {
                on: onSpy,
                off: offSpy
            };

            field.context.set('model', ctxModel);
        });

        it('should set an on change listener on the context model', function() {
            field.initialize({});

            expect(onSpy).toHaveBeenCalled();
        });

        it('should set a listener on before route changes', function() {
            field.initialize({});

            expect(app.routing.before).toHaveBeenCalled();
        });

        it('should set a listener on after route changed', function() {
            field.initialize({});

            expect(app.router.on).toHaveBeenCalled();
        });
    });

    describe('_beforeRouteChanged', function() {
        var ctxModel;
        var offSpy;

        beforeEach(function() {
            offSpy = sinon.collection.spy();
            ctxModel = {
                off: offSpy
            };
        });

        it('should do nothing if model does not exist on context', function() {
            field.context.unset('model');
            field._beforeRouteChanged();

            expect(offSpy).not.toHaveBeenCalled();
        });

        it('should remove change event listener if model exists on context', function() {
            field.context.set('model', ctxModel);
            field._beforeRouteChanged();

            expect(offSpy).toHaveBeenCalled();
        });
    });

    describe('_routeChanged', function() {
        var ctxModel;
        var onSpy;
        var offSpy;

        beforeEach(function() {
            onSpy = sinon.collection.spy();
            offSpy = sinon.collection.spy();
            sinon.collection.stub(field, 'updateEmailLinks', function() {});
            ctxModel = {
                on: onSpy,
                off: offSpy
            };
        });

        it('should do nothing if model does not exist on context', function() {
            field.context.unset('model');
            field._routeChanged();

            expect(onSpy).not.toHaveBeenCalled();
        });

        it('should remove change event listener if model exists on context', function() {
            field.context.set('model', ctxModel);
            field._routeChanged();

            expect(onSpy).toHaveBeenCalled();
        });

        it('should call updateEmailLinks any time the route changes', function() {
            field._routeChanged();

            expect(field.updateEmailLinks).toHaveBeenCalled();
        });
    });

    describe('_retrieveEmailOptionsFromLink', function() {
        var should;

        should = 'should return email options to prepopulate on email compose if existing parent model exists on ' +
            'context';
        it(should, function() {
            var bean = app.data.createBean('Contacts');
            var result;

            bean.set({
                id: '123',
                name: 'Foo'
            });
            field.context.set('model', bean);
            result = field._retrieveEmailOptionsFromLink();

            expect(result).toEqual({
                to: [{bean: bean}],
                related: bean
            });
        });

        should = 'should return email options to prepopulate on email compose if existing parent model exists on ' +
            'parent context';
        it(should, function() {
            var bean = app.data.createBean('Contacts');
            var parentContext = app.context.getContext();
            var result;

            bean.set({
                id: '123',
                name: 'Foo'
            });
            parentContext.prepare();
            field.context.parent = parentContext;
            field.context.parent.set('model', bean);
            result = field._retrieveEmailOptionsFromLink();

            expect(result).toEqual({
                to: [{bean: bean}],
                related: bean
            });
        });

        it('should return empty object if parent model does not exist', function() {
            var result;
            field.context.unset('model');
            result = field._retrieveEmailOptionsFromLink();

            expect(result).toEqual({});
        });

        it('should return empty object if parent model has no id, meaning it is not an existing record', function() {
            var bean = app.data.createBean('Contacts');
            var result;

            bean.set({
                name: 'Foo'
            });
            field.context.set('model', bean);
            result = field._retrieveEmailOptionsFromLink();

            expect(result).toEqual({});
        });
    });

    describe('_dispose', function() {
        var ctxModel;
        var offSpy;

        beforeEach(function() {
            offSpy = sinon.collection.spy();
            sinon.collection.spy(app.routing, 'offBefore');
            sinon.collection.spy(app.router, 'off');
            ctxModel = {
                off: offSpy
            };

            field.context.set('model', ctxModel);
        });

        it('should remove the change listener on the context model', function() {
            field._dispose();

            expect(offSpy).toHaveBeenCalled();
        });

        it('should remove the listener on before route changes', function() {
            field._dispose();

            expect(app.routing.offBefore).toHaveBeenCalled();
        });

        it('should remove the listener on after route changed', function() {
            field._dispose();

            expect(app.router.off).toHaveBeenCalled();
        });
    });
});
